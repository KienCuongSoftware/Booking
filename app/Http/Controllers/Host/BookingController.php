<?php

namespace App\Http\Controllers\Host;

use App\Enums\BookingPaymentStatus;
use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingTransaction;
use App\Services\BookingLedgerService;
use App\Services\BookingLifecycleService;
use App\Services\BookingNotificationService;
use App\Services\CancellationFeeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(
        private readonly BookingLifecycleService $bookingLifecycleService,
        private readonly CancellationFeeService $cancellationFeeService,
        private readonly BookingLedgerService $bookingLedgerService,
        private readonly BookingNotificationService $bookingNotificationService,
    ) {}

    public function index(Request $request): View
    {
        $query = Booking::query()
            ->whereHas('hotel', function ($builder) use ($request): void {
                $builder->where('host_id', $request->user()->id);
            })
            ->with(['customer:id,name,email', 'hotel:id,name', 'roomType:id,name', 'transactions.actor:id,name']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->value());
        }

        $bookings = $query->latest('id')->paginate(12)->withQueryString();

        return view('host.bookings.index', compact('bookings'));
    }

    public function updateStatus(Request $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->hotel->host_id === $request->user()->id, 403);

        $validated = $request->validate([
            'status' => ['required', 'in:confirmed,cancelled,completed,no_show'],
            'host_note' => ['nullable', 'string', 'max:1000'],
            'mark_paid' => ['nullable', 'boolean'],
        ]);

        $originalStatus = $booking->status;
        $booking->host_note = ($validated['host_note'] ?? null) ?: $booking->host_note;

        $markedPaid = false;
        if (($validated['mark_paid'] ?? false) && $booking->payment_status !== BookingPaymentStatus::Paid) {
            $booking->payment_status = BookingPaymentStatus::Paid;
            $markedPaid = true;
        }

        $booking->save();
        $transitionContext = [
            'event_note' => __('Chủ khách sạn cập nhật trạng thái đơn.'),
        ];
        if (in_array($validated['status'], [BookingStatus::Cancelled->value, BookingStatus::NoShow->value], true)) {
            $fee = $this->cancellationFeeService->calculate($booking);
            $transitionContext['cancellation_fee_amount'] = $fee['fee_amount'];
            $transitionContext['refund_amount'] = $fee['refund_amount'];
            $transitionContext['cancellation_policy_snapshot'] = $fee['policy_snapshot'];
            $transitionContext['cancel_reason'] = $validated['status'] === BookingStatus::NoShow->value
                ? __('Khách không đến nhận phòng.')
                : __('Đơn được hủy bởi chủ khách sạn.');
        }

        $booking = $this->bookingLifecycleService->transition(
            $booking,
            BookingStatus::from($validated['status']),
            $request->user(),
            $transitionContext,
        );

        if ($markedPaid) {
            $this->bookingLedgerService->recordMarkedPaid($booking, $request->user());
        }
        if (in_array($booking->status->value, [BookingStatus::Cancelled->value, BookingStatus::NoShow->value], true)) {
            $this->bookingLedgerService->recordCancellationFees($booking, $request->user(), $booking->status->value);
        }
        $this->bookingNotificationService->sendStatusChanged($booking, $originalStatus);

        return back()->with('status', __('Đã cập nhật trạng thái đơn đặt.'));
    }

    public function updateRefundStatus(Request $request, Booking $booking, BookingTransaction $transaction): RedirectResponse
    {
        abort_unless($booking->hotel->host_id === $request->user()->id, 403);
        abort_unless($transaction->booking_id === $booking->id && $transaction->type === 'refund', 404);

        $validated = $request->validate([
            'status' => ['required', 'in:processing,refunded,failed'],
        ]);

        $this->bookingLedgerService->updateRefundStatus(
            $transaction,
            $validated['status'],
            $request->user(),
        );

        return back()->with('status', __('Đã cập nhật trạng thái hoàn tiền.'));
    }
}

