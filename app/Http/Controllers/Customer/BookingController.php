<?php

namespace App\Http\Controllers\Customer;

use App\Enums\BookingPaymentMethod;
use App\Enums\BookingPaymentProvider;
use App\Enums\BookingPaymentStatus;
use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Hotel;
use App\Services\BookingLedgerService;
use App\Services\BookingLifecycleService;
use App\Services\BookingNotificationService;
use App\Services\CancellationFeeService;
use App\Services\RebookSuggestionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
    ) {}

    public function index(Request $request): View
    {
        $bookings = $request->user()
            ->bookings()
            ->with(['hotel:id,name,slug', 'roomType:id,name', 'statusEvents.actor:id,name'])
            ->latest('id')
            ->paginate(10);

        return view('customer.bookings.index', compact('bookings'));
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
            'payment_method' => ['required', 'in:cash,bank_transfer'],
            'payment_provider' => ['nullable', 'in:momo,paypal'],
            'payment_reference' => ['nullable', 'string', 'max:120'],
            'customer_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $checkIn = \Illuminate\Support\Carbon::parse($validated['check_in_date']);
        $checkOut = \Illuminate\Support\Carbon::parse($validated['check_out_date']);
        $nights = $checkIn->diffInDays($checkOut);
        $booking = DB::transaction(function () use ($hotel, $validated, $request, $checkIn, $checkOut, $nights) {
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

            $reservedCount = Booking::query()
                ->where('room_type_id', $roomType->id)
                ->whereIn('status', [BookingStatus::Pending->value, BookingStatus::Confirmed->value])
                ->whereDate('check_in_date', '<', $checkOut->toDateString())
                ->whereDate('check_out_date', '>', $checkIn->toDateString())
                ->lockForUpdate()
                ->count();

            if ($reservedCount >= $roomType->quantity) {
                throw ValidationException::withMessages([
                    'room_type_id' => __('Loại phòng này đã hết chỗ trong khoảng ngày bạn chọn.'),
                ]);
            }

            $unitPrice = (float) ($roomType->new_price ?? $roomType->base_price);
            $totalPrice = $unitPrice * $nights;
            $paymentMethod = BookingPaymentMethod::from($validated['payment_method']);
            $paymentProvider = $paymentMethod === BookingPaymentMethod::BankTransfer
                ? BookingPaymentProvider::from($validated['payment_provider'] ?? 'momo')
                : null;
            $paymentStatus = $paymentMethod === BookingPaymentMethod::Cash
                ? BookingPaymentStatus::Unpaid
                : BookingPaymentStatus::Pending;

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
            ]);

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

        $this->bookingNotificationService->sendCreated($booking);

        return redirect()
            ->route('customer.bookings.index')
            ->with('status', __('Đặt phòng thành công. Chủ khách sạn sẽ xử lý đơn của bạn sớm.'));
    }
}

