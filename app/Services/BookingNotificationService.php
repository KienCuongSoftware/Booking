<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Mail\BookingCreatedMail;
use App\Mail\BookingFollowUpMail;
use App\Mail\BookingReminderMail;
use App\Mail\BookingStatusChangedMail;
use App\Mail\HostPendingBookingReminderMail;
use App\Models\Booking;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class BookingNotificationService
{
    public function sendCreated(Booking $booking): void
    {
        $booking->loadMissing(['customer:id,name,email', 'hotel.host:id,name,email', 'roomType:id,name']);

        $this->queueSafely(
            $booking->customer->email,
            new BookingCreatedMail($booking, 'customer'),
            'booking_created_customer'
        );

        if ($booking->hotel?->host?->email) {
            $this->queueSafely(
                $booking->hotel->host->email,
                new BookingCreatedMail($booking, 'host'),
                'booking_created_host'
            );
        }
    }

    public function sendStatusChanged(Booking $booking, BookingStatus|string|null $fromStatus = null): void
    {
        $booking->loadMissing(['customer:id,name,email', 'hotel:id,name', 'roomType:id,name']);

        $from = null;
        if ($fromStatus instanceof BookingStatus) {
            $from = $fromStatus;
        } elseif (is_string($fromStatus) && $fromStatus !== '') {
            $from = BookingStatus::from($fromStatus);
        }

        $this->queueSafely(
            $booking->customer->email,
            new BookingStatusChangedMail($booking, $from),
            'booking_status_changed_customer'
        );
    }

    public function sendUpcomingCheckInReminders(): int
    {
        return $this->sendRemindersByWindow('d1');
    }

    public function sendRemindersByWindow(string $window): int
    {
        if (! config('booking.reminders.enabled', true)) {
            return 0;
        }

        $bookings = Booking::query()
            ->where('status', BookingStatus::Confirmed->value)
            ->whereNull($this->reminderColumn($window))
            ->with(['customer:id,name,email', 'hotel:id,name', 'hotel.cancellationPolicy:id,hotel_id,send_reminder_d3,send_reminder_d1,send_reminder_h6', 'roomType:id,name'])
            ->get()
            ->filter(fn (Booking $booking) => $this->shouldSendReminder($booking, $window))
            ->values();

        foreach ($bookings as $booking) {
            $this->queueSafely(
                $booking->customer->email,
                new BookingReminderMail($booking, $window),
                'booking_reminder_'.$window
            );
            $booking->forceFill([
                $this->reminderColumn($window) => now(),
                'reminder_sent_at' => $window === 'd1' ? now() : $booking->reminder_sent_at,
            ])->save();
        }

        return $bookings->count();
    }

    public function sendAllScheduledReminders(): array
    {
        return [
            'd3' => $this->sendRemindersByWindow('d3'),
            'd1' => $this->sendRemindersByWindow('d1'),
            'h6' => $this->sendRemindersByWindow('h6'),
        ];
    }

    public function sendCompletionFollowUps(): int
    {
        if (! config('booking.reminders.enabled', true)) {
            return 0;
        }

        $bookings = Booking::query()
            ->where('status', BookingStatus::Completed->value)
            ->whereNull('follow_up_sent_at')
            ->whereDate('completed_at', '<=', now()->subDay()->toDateString())
            ->with(['customer:id,name,email', 'hotel:id,name', 'roomType:id,name'])
            ->get();

        foreach ($bookings as $booking) {
            $this->queueSafely(
                $booking->customer->email,
                new BookingFollowUpMail($booking),
                'booking_follow_up'
            );
            $booking->forceFill([
                'follow_up_sent_at' => now(),
            ])->save();
        }

        return $bookings->count();
    }

    public function sendPendingHostSlaReminders(): int
    {
        if (! config('booking.pending_sla.enabled', true)) {
            return 0;
        }

        $hours = max(1, (int) config('booking.pending_sla.hours', 24));
        $threshold = now()->subHours($hours);

        $bookings = Booking::query()
            ->where('status', BookingStatus::Pending->value)
            ->where('created_at', '<=', $threshold)
            ->whereNull('pending_host_notified_at')
            ->with(['hotel.host:id,name,email', 'roomType:id,name'])
            ->get();

        foreach ($bookings as $booking) {
            $hostEmail = $booking->hotel?->host?->email;
            if ($hostEmail) {
                $this->queueSafely(
                    $hostEmail,
                    new HostPendingBookingReminderMail($booking),
                    'booking_pending_host_sla'
                );
            }

            $booking->forceFill(['pending_host_notified_at' => now()])->save();
        }

        return $bookings->count();
    }

    private function reminderColumn(string $window): string
    {
        return match ($window) {
            'd3' => 'reminder_d3_sent_at',
            'h6' => 'reminder_h6_sent_at',
            default => 'reminder_sent_at',
        };
    }

    private function shouldSendReminder(Booking $booking, string $window): bool
    {
        $policy = $booking->hotel?->cancellationPolicy;
        if (! $policy) {
            return false;
        }

        if ($window === 'd3' && ! $policy->send_reminder_d3) {
            return false;
        }
        if ($window === 'd1' && ! $policy->send_reminder_d1) {
            return false;
        }
        if ($window === 'h6' && ! $policy->send_reminder_h6) {
            return false;
        }

        $checkInAt = $booking->check_in_date->copy()->setTime(14, 0);
        $now = now();

        return match ($window) {
            'd3' => $now->between($checkInAt->copy()->subDays(3), $checkInAt->copy()->subDays(2)),
            'h6' => $now->between($checkInAt->copy()->subHours(6), $checkInAt),
            default => $now->between($checkInAt->copy()->subDay(), $checkInAt),
        };
    }

    private function queueSafely(string $recipient, Mailable $mailable, string $context): void
    {
        try {
            Mail::to($recipient)->queue($mailable);
        } catch (Throwable $e) {
            Log::warning('Mail queue failed', [
                'context' => $context,
                'recipient' => $recipient,
                'error' => $e->getMessage(),
            ]);
            report($e);
        }
    }
}
