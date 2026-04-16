<?php

namespace App\Http\Controllers\Customer;

use App\Enums\BookingPaymentMethod;
use App\Enums\BookingPaymentProvider;
use App\Enums\BookingPaymentStatus;
use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Hotel;
use App\Models\PromoCode;
use App\Models\RoomType;
use App\Models\WaitlistEntry;
use App\Services\AuditLogService;
use App\Services\BookingLedgerService;
use App\Services\BookingLifecycleService;
use App\Services\BookingNotificationService;
use App\Services\BookingWaitlistService;
use App\Services\CancellationFeeService;
use App\Services\DynamicPricingService;
use App\Services\IdempotencyService;
use App\Services\PayPalCheckoutService;
use App\Services\RebookSuggestionService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(
        private readonly BookingLifecycleService $bookingLifecycleService,
        private readonly BookingLedgerService $bookingLedgerService,
        private readonly CancellationFeeService $cancellationFeeService,
        private readonly RebookSuggestionService $rebookSuggestionService,
        private readonly BookingNotificationService $bookingNotificationService,
        private readonly DynamicPricingService $dynamicPricingService,
        private readonly IdempotencyService $idempotencyService,
        private readonly AuditLogService $auditLogService,
        private readonly BookingWaitlistService $bookingWaitlistService,
        private readonly PayPalCheckoutService $payPalCheckoutService,
    ) {}

    public function index(Request $request): View
    {
        $bookings = $request->user()
            ->bookings()
            ->with(['hotel:id,name,slug', 'roomType:id,name', 'statusEvents.actor:id,name', 'review:id,booking_id'])
            ->latest('id')
            ->paginate(10);

        return view('customer.bookings.index', compact('bookings'));
    }

    public function show(Request $request, Booking $booking): View
    {
        abort_unless($booking->customer_id === $request->user()->id, 403);

        $booking->load([
            'hotel:id,name,slug,address,city',
            'roomType:id,name',
            'review:id,booking_id,rating,comment',
            'promoCode:id,code',
            'statusEvents' => function ($query): void {
                $query->orderBy('changed_at')->with('actor:id,name');
            },
        ]);

        $cancellationPreview = null;
        if (in_array($booking->status, [BookingStatus::Pending, BookingStatus::Confirmed], true)
            && $booking->check_in_date->format('Y-m-d') > now()->format('Y-m-d')) {
            $cancellationPreview = $this->cancellationFeeService->calculate($booking);
        }

        return view('customer.bookings.show', compact('booking', 'cancellationPreview'));
    }

    public function availability(Request $request, Hotel $hotel): JsonResponse
    {
        $validated = $request->validate([
            'room_type_id' => ['required', 'integer'],
            'check_in_date' => ['nullable', 'date', 'after_or_equal:today'],
            'check_out_date' => ['nullable', 'date', 'after:check_in_date'],
        ]);

        $roomType = $hotel->roomTypes()
            ->where('is_active', true)
            ->where('id', $validated['room_type_id'])
            ->firstOrFail();

        $windowStart = now()->startOfDay();
        $windowEnd = now()->addDays(180)->startOfDay();
        $blockedDates = $this->blockedDatesForRoomType($roomType, $windowStart, $windowEnd);

        $response = [
            'blocked_dates' => $blockedDates,
            'min_date' => $windowStart->toDateString(),
            'max_date' => $windowEnd->toDateString(),
        ];

        if (! empty($validated['check_in_date']) && ! empty($validated['check_out_date'])) {
            $checkIn = Carbon::parse($validated['check_in_date'])->startOfDay();
            $checkOut = Carbon::parse($validated['check_out_date'])->startOfDay();
            $firstBlockedDate = $this->firstBlockedDateInRange($blockedDates, $checkIn, $checkOut);

            $response['range_available'] = $firstBlockedDate === null;
            $response['first_blocked_date'] = $firstBlockedDate;
        }

        return response()->json($response);
    }

    public function cancellable(Request $request): View
    {
        $bookings = $request->user()
            ->bookings()
            ->whereIn('status', [BookingStatus::Pending->value, BookingStatus::Confirmed->value])
            ->whereDate('check_in_date', '>', now()->toDateString())
            ->with(['hotel:id,name,slug', 'roomType:id,name'])
            ->latest('id')
            ->paginate(10);

        $cancellationPreviews = [];
        foreach ($bookings as $booking) {
            $cancellationPreviews[$booking->id] = $this->cancellationFeeService->calculate($booking);
        }

        return view('customer.bookings.cancellable', compact('bookings', 'cancellationPreviews'));
    }

    public function rebook(Request $request): View
    {
        $bookings = $request->user()
            ->bookings()
            ->whereIn('status', [BookingStatus::Cancelled->value, BookingStatus::NoShow->value, BookingStatus::Completed->value])
            ->with(['hotel:id,name,slug', 'roomType:id,name'])
            ->latest('id')
            ->paginate(10);

        $rebookSuggestions = [];
        foreach ($bookings as $booking) {
            $rebookSuggestions[$booking->id] = $this->rebookSuggestionService->suggestDates($booking)->all();
        }

        return view('customer.bookings.rebook', compact('bookings', 'rebookSuggestions'));
    }

    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->customer_id === $request->user()->id, 403);

        $validated = $request->validate([
            'cancel_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($booking->check_in_date->isToday() || $booking->check_in_date->isPast()) {
            throw ValidationException::withMessages([
                'cancel_reason' => [__('Đã quá thời hạn hủy đơn cho lịch nhận phòng này.')],
            ]);
        }

        $fee = $this->cancellationFeeService->calculate($booking);
        $originalStatus = $booking->status;
        $booking = $this->bookingLifecycleService->transition(
            $booking,
            BookingStatus::Cancelled,
            $request->user(),
            [
                'cancel_reason' => ($validated['cancel_reason'] ?? null) ?: null,
                'cancellation_fee_amount' => $fee['fee_amount'],
                'refund_amount' => $fee['refund_amount'],
                'cancellation_policy_snapshot' => $fee['policy_snapshot'],
                'event_note' => __('Khách hủy đơn trước hạn.'),
            ],
        );
        $this->bookingLedgerService->recordCancellationFees($booking, $request->user(), 'cancelled');
        $this->bookingNotificationService->sendStatusChanged($booking, $originalStatus);
        $this->bookingWaitlistService->notifyForFreedSlot($booking);
        $this->auditLogService->record($booking, 'customer_cancelled', $request->user(), [
            'fee_amount' => $fee['fee_amount'],
            'refund_amount' => $fee['refund_amount'],
        ], $request);

        return redirect()
            ->route('customer.bookings.cancellable')
            ->with('status', __('Đã hủy đơn đặt. Phí hủy áp dụng: :percent%.', ['percent' => rtrim(rtrim(number_format($fee['fee_percent'], 2, '.', ''), '0'), '.')]));
    }

    public function store(Request $request, Hotel $hotel): RedirectResponse
    {
        $validated = $request->validate([
            'room_type_id' => ['required', 'integer'],
            'check_in_date' => ['required', 'date', 'after_or_equal:today'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'guest_count' => ['required', 'integer', 'min:1', 'max:10'],
            'payment_method' => ['required', 'in:cash,bank_transfer,paypal'],
            'payment_provider' => ['nullable', 'in:momo,paypal'],
            'payment_reference' => ['nullable', 'string', 'max:120'],
            'customer_note' => ['nullable', 'string', 'max:1000'],
            'promo_code' => ['nullable', 'string', 'max:40'],
            'join_waitlist' => ['nullable', 'boolean'],
        ]);

        if ($validated['payment_method'] === BookingPaymentMethod::BankTransfer->value
            && empty($validated['payment_provider'])) {
            throw ValidationException::withMessages([
                'payment_provider' => __('Vui lòng chọn cổng thanh toán khi dùng chuyển khoản.'),
            ]);
        }

        if ($validated['payment_method'] !== BookingPaymentMethod::BankTransfer->value) {
            $validated['payment_provider'] = null;
        }

        $idemHeader = (string) $request->header('Idempotency-Key', '');
        if ($idemHeader !== '' && config('booking.idempotency.enabled', true)) {
            $existing = $this->idempotencyService->existingBookingFor(
                $request->user(),
                IdempotencyService::SCOPE_BOOKING_STORE,
                sha1($idemHeader),
            );
            if ($existing) {
                return redirect()
                    ->route('customer.bookings.index')
                    ->with('status', __('Đơn đặt đã được tạo trước đó (idempotency).'));
            }
        }

        $checkIn = Carbon::parse($validated['check_in_date']);
        $checkOut = Carbon::parse($validated['check_out_date']);
        $nights = $checkIn->diffInDays($checkOut);

        $booking = DB::transaction(function () use ($hotel, $validated, $request, $checkIn, $checkOut, $nights, $idemHeader) {
            $roomType = $hotel->roomTypes()
                ->where('is_active', true)
                ->where('id', $validated['room_type_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if ((int) $validated['guest_count'] > $roomType->max_occupancy) {
                throw ValidationException::withMessages([
                    'guest_count' => __('Số khách vượt quá sức chứa của loại phòng đã chọn.'),
                ]);
            }

            $blockedDates = $this->blockedDatesForRoomType($roomType, $checkIn, $checkOut);
            $firstBlockedDate = $this->firstBlockedDateInRange($blockedDates, $checkIn, $checkOut);
            if ($firstBlockedDate !== null) {
                if ($request->boolean('join_waitlist')) {
                    WaitlistEntry::query()->updateOrCreate(
                        [
                            'user_id' => $request->user()->id,
                            'hotel_id' => $hotel->id,
                            'room_type_id' => $roomType->id,
                            'check_in_date' => $checkIn->toDateString(),
                            'check_out_date' => $checkOut->toDateString(),
                        ],
                        [
                            'guest_count' => (int) $validated['guest_count'],
                            'notified_at' => null,
                        ],
                    );

                    throw new HttpResponseException(
                        redirect()
                            ->route('public.hotels.show', $hotel)
                            ->with('status', __('Hết chỗ — đã thêm bạn vào danh sách chờ.')),
                    );
                }

                throw ValidationException::withMessages([
                    'room_type_id' => __('Loại phòng này đã hết chỗ vào ngày :date.', ['date' => Carbon::parse($firstBlockedDate)->format('d/m/Y')]),
                ]);
            }

            $pricing = $this->dynamicPricingService->quote($hotel, $roomType, $checkIn, $checkOut);
            $subtotal = (float) $pricing['subtotal'];

            $promoModel = null;
            $discount = 0.0;
            $promoCodeRaw = trim((string) ($validated['promo_code'] ?? ''));
            if ($promoCodeRaw !== '') {
                $promoModel = PromoCode::query()
                    ->whereRaw('UPPER(code) = ?', [mb_strtoupper($promoCodeRaw)])
                    ->first();
                if (! $promoModel) {
                    throw ValidationException::withMessages([
                        'promo_code' => __('Mã giảm giá không hợp lệ.'),
                    ]);
                }
                $check = $promoModel->validateFor($hotel, $roomType, $checkIn, $checkOut);
                if (! $check['valid']) {
                    throw ValidationException::withMessages([
                        'promo_code' => $check['message'] ?? __('Mã giảm giá không hợp lệ.'),
                    ]);
                }
                $discount = $promoModel->discountAmountForSubtotal($subtotal);
            }

            $totalPrice = max(0.0, round($subtotal - $discount, 2));
            $unitPrice = $nights > 0 ? round($totalPrice / $nights, 2) : 0.0;

            $paymentMethod = BookingPaymentMethod::from($validated['payment_method']);
            if ($paymentMethod === BookingPaymentMethod::PayPal) {
                if (! config('booking.payments.paypal.enabled', false)
                    || ! (string) config('services.paypal.client_id')
                    || ! (string) config('services.paypal.client_secret')) {
                    throw ValidationException::withMessages([
                        'payment_method' => __('Thanh toán PayPal đang tắt hoặc chưa cấu hình đủ Client ID / Secret.'),
                    ]);
                }
            }

            $paymentProvider = match ($paymentMethod) {
                BookingPaymentMethod::BankTransfer => BookingPaymentProvider::from($validated['payment_provider'] ?? 'momo'),
                BookingPaymentMethod::PayPal => BookingPaymentProvider::Paypal,
                default => null,
            };

            $paymentStatus = $paymentMethod === BookingPaymentMethod::Cash
                ? BookingPaymentStatus::Unpaid
                : BookingPaymentStatus::Pending;

            $holdExpiresAt = null;
            if (config('booking.hold.enabled', true) && $paymentMethod !== BookingPaymentMethod::Cash) {
                $holdExpiresAt = now()->addMinutes((int) config('booking.hold.minutes', 20));
            }

            $booking = Booking::query()->create([
                'booking_code' => 'BK'.strtoupper(Str::random(8)),
                'customer_id' => $request->user()->id,
                'hotel_id' => $hotel->id,
                'room_type_id' => $roomType->id,
                'check_in_date' => $checkIn->toDateString(),
                'check_out_date' => $checkOut->toDateString(),
                'guest_count' => (int) $validated['guest_count'],
                'nights' => $nights,
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
                'status' => BookingStatus::Pending,
                'payment_method' => $paymentMethod,
                'payment_provider' => $paymentProvider,
                'payment_status' => $paymentStatus,
                'payment_reference' => ($validated['payment_reference'] ?? null) ?: null,
                'customer_note' => ($validated['customer_note'] ?? null) ?: null,
                'status_changed_at' => now(),
                'status_changed_by' => $request->user()->id,
                'hold_expires_at' => $holdExpiresAt,
                'promo_code_id' => $promoModel?->id,
                'discount_amount' => $discount,
                'check_in_token' => Str::random(48),
                'pricing_snapshot' => $pricing,
                'idempotency_key' => $idemHeader !== '' ? mb_substr($idemHeader, 0, 128) : null,
            ]);

            if ($promoModel) {
                $promoModel->increment('uses_count');
            }

            $this->bookingLifecycleService->recordStatusEvent(
                $booking,
                null,
                BookingStatus::Pending,
                $request->user(),
                ['event_note' => __('Tạo mới đơn đặt phòng.')],
            );
            $this->bookingLedgerService->recordChargeCreated($booking, $request->user());

            return $booking;
        });

        if ($idemHeader !== '' && config('booking.idempotency.enabled', true)) {
            $this->idempotencyService->remember(
                $request->user(),
                IdempotencyService::SCOPE_BOOKING_STORE,
                sha1($idemHeader),
                $booking,
            );
        }

        $this->auditLogService->record($booking, 'booking_created', $request->user(), [
            'hotel_id' => $hotel->id,
            'room_type_id' => $booking->room_type_id,
            'total_price' => (float) $booking->total_price,
        ], $request);

        if ($booking->payment_method === BookingPaymentMethod::PayPal) {
            $approvalUrl = $this->payPalCheckoutService->createCheckoutApprovalUrl($booking);
            if ($approvalUrl) {
                return redirect()->away($approvalUrl);
            }
        }

        if ($booking->payment_method !== BookingPaymentMethod::Cash) {
            return redirect()
                ->route('customer.bookings.show', $booking)
                ->with('status', __('Đã giữ chỗ. Vui lòng hoàn tất thanh toán trước khi xác nhận email được gửi.'));
        }

        $this->bookingNotificationService->sendCreated($booking);

        return redirect()
            ->route('customer.bookings.index')
            ->with('status', __('Đặt phòng thành công. Chủ khách sạn sẽ xử lý đơn của bạn sớm.'));
    }

    /**
     * @return array<int, string>
     */
    private function blockedDatesForRoomType(RoomType $roomType, Carbon $windowStart, Carbon $windowEnd): array
    {
        $start = $windowStart->copy()->startOfDay();
        $end = $windowEnd->copy()->startOfDay();

        $bookings = Booking::query()
            ->where('room_type_id', $roomType->id)
            ->whereIn('status', [BookingStatus::Pending->value, BookingStatus::Confirmed->value])
            ->whereDate('check_in_date', '<', $end->toDateString())
            ->whereDate('check_out_date', '>', $start->toDateString())
            ->lockForUpdate()
            ->get(['check_in_date', 'check_out_date']);

        $occupancyByDay = [];
        foreach ($bookings as $booking) {
            $cursor = Carbon::parse($booking->check_in_date)->startOfDay();
            $checkout = Carbon::parse($booking->check_out_date)->startOfDay();
            while ($cursor->lt($checkout)) {
                if ($cursor->gte($start) && $cursor->lt($end)) {
                    $key = $cursor->toDateString();
                    $occupancyByDay[$key] = ($occupancyByDay[$key] ?? 0) + 1;
                }
                $cursor->addDay();
            }
        }

        $blockedDates = [];
        foreach ($occupancyByDay as $date => $count) {
            if ($count >= $roomType->quantity) {
                $blockedDates[] = $date;
            }
        }

        sort($blockedDates);

        return $blockedDates;
    }

    private function firstBlockedDateInRange(array $blockedDates, Carbon $checkIn, Carbon $checkOut): ?string
    {
        $blockedMap = array_fill_keys($blockedDates, true);
        $cursor = $checkIn->copy()->startOfDay();
        $checkout = $checkOut->copy()->startOfDay();

        while ($cursor->lt($checkout)) {
            $key = $cursor->toDateString();
            if (isset($blockedMap[$key])) {
                return $key;
            }
            $cursor->addDay();
        }

        return null;
    }
}
