<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;

class BookingNoShowService
{
    public function __construct(
        private readonly BookingLifecycleService $bookingLifecycleService,
        private readonly CancellationFeeService $cancellationFeeService,
        private readonly BookingNotificationService $bookingNotificationService,
        private readonly BookingLedgerService $bookingLedgerService,
    ) {}

    public function markOverdueAsNoShow(): int
    {
        $bookings = Booking::query()
            ->where('status', BookingStatus::Confirmed->value)
            ->whereDate('check_in_date', '<', now()->toDateString())
            ->with(['hotel.cancellationPolicy.tiers'])
            ->get();

        foreach ($bookings as $booking) {
            $originalStatus = $booking->status;
            $fee = $this->cancellationFeeService->calculate($booking);

            $booking = $this->bookingLifecycleService->transition(
                $booking,
                BookingStatus::NoShow,
                null,
                [
                    'cancel_reason' => __('Khách không đến nhận phòng đúng giờ.'),
                    'cancellation_fee_amount' => $fee['fee_amount'],
                    'refund_amount' => $fee['refund_amount'],
                    'cancellation_policy_snapshot' => $fee['policy_snapshot'],
                    'event_note' => __('Hệ thống tự động chuyển đơn sang không đến (no-show).'),
                ],
            );

            $this->bookingLedgerService->recordCancellationFees($booking, null, 'no_show');
            $this->bookingNotificationService->sendStatusChanged($booking, $originalStatus);
        }

        return $bookings->count();
    }
}
