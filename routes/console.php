<?php

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Services\BookingLedgerService;
use App\Services\BookingLifecycleService;
use App\Services\BookingNoShowService;
use App\Services\BookingNotificationService;
use App\Services\BookingWaitlistService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('bookings:send-reminders', function () {
    $result = app(BookingNotificationService::class)->sendAllScheduledReminders();
    $this->info('Sent booking reminders: D-3='.$result['d3'].' | D-1='.$result['d1'].' | H-6='.$result['h6']);
})->purpose('Send check-in reminder emails for confirmed bookings');

Artisan::command('bookings:send-follow-ups', function () {
    $sentCount = app(BookingNotificationService::class)->sendCompletionFollowUps();
    $this->info("Sent {$sentCount} booking follow-up emails.");
})->purpose('Send post-stay follow-up emails for completed bookings');

Artisan::command('bookings:mark-no-show', function () {
    $count = app(BookingNoShowService::class)->markOverdueAsNoShow();
    $this->info("Marked {$count} bookings as no-show.");
})->purpose('Automatically mark overdue confirmed bookings as no-show');

Schedule::command('bookings:send-reminders')->hourly();
Schedule::command('bookings:send-follow-ups')->dailyAt('18:00');
Schedule::command('bookings:mark-no-show')->dailyAt('02:00');

Artisan::command('bookings:expire-holds', function () {
    $lifecycle = app(BookingLifecycleService::class);
    $ledger = app(BookingLedgerService::class);
    $notifier = app(BookingNotificationService::class);
    $waitlist = app(BookingWaitlistService::class);

    $count = 0;
    Booking::query()
        ->where('status', BookingStatus::Pending->value)
        ->whereNotNull('hold_expires_at')
        ->where('hold_expires_at', '<', now())
        ->orderBy('id')
        ->chunkById(100, function ($bookings) use ($lifecycle, $ledger, $notifier, $waitlist, &$count): void {
            foreach ($bookings as $booking) {
                $originalStatus = $booking->status;
                $booking = $lifecycle->transition(
                    $booking,
                    BookingStatus::Cancelled,
                    null,
                    [
                        'cancel_reason' => __('Giữ chỗ/Thanh toán hết hạn.'),
                        'cancellation_fee_amount' => 0,
                        'refund_amount' => 0,
                        'cancellation_policy_snapshot' => null,
                        'event_note' => __('Tự động hủy do hết thời giữ chỗ thanh toán.'),
                    ],
                );
                $ledger->recordCancellationFees($booking, null, 'cancelled');
                $notifier->sendStatusChanged($booking, $originalStatus);
                $waitlist->notifyForFreedSlot($booking);
                $count++;
            }
        });

    $this->info("Expired holds: {$count}");
})->purpose('Auto-cancel pending bookings when payment hold expires');

Artisan::command('bookings:alert-pending-host-sla', function () {
    $sent = app(BookingNotificationService::class)->sendPendingHostSlaReminders();
    $this->info("Host SLA reminders: {$sent}");
})->purpose('Email hosts when pending bookings exceed SLA threshold');

Schedule::command('bookings:expire-holds')->everyFiveMinutes();
Schedule::command('bookings:alert-pending-host-sla')->hourly();
