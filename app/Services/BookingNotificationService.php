<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Mail\BookingCreatedMail;
use App\Mail\BookingFollowUpMail;
use App\Mail\BookingReminderMail;
use App\Mail\BookingStatusChangedMail;
use App\Models\Booking;
use Illuminate\Support\Facades\Mail;

class BookingNotificationService
{
    public function sendCreated(Booking $booking): void
    {
        $booking->loadMissing(['customer:id,name,email', 'hotel.host:id,name,email', 'roomType:id,name']);

        Mail::to($booking->customer->email)->queue(new BookingCreatedMail($booking, 'customer'));

        if ($booking->hotel?->host?->email) {
            Mail::to($booking->hotel->host->email)->queue(new BookingCreatedMail($booking, 'host'));
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

        Mail::to($booking->customer->email)->queue(new BookingStatusChangedMail($booking, $from));
    }

    public function sendUpcomingCheckInReminders(): int
    {
        return $this->sendRemindersByWindow('d1');
    }

    public function sendRemindersByWindow(string $window): int
    {
        $bookings = Booking::query()
            ->where('status', BookingStatus::Confirmed->value)
            ->whereNull($this->reminderColumn($window))
            ->with(['customer:id,name,email', 'hotel:id,name', 'hotel.cancellationPolicy:id,hotel_id,send_reminder_d3,send_reminder_d1,send_reminder_h6', 'roomType:id,name'])
            ->get()
            ->filter(fn (Booking $booking) => $this->shouldSendReminder($booking, $window))
            ->values();

        foreach ($bookings as $booking) {
            Mail::to($booking->customer->email)->queue(new BookingReminderMail($booking, $window));
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
        $bookings = Booking::query()
            ->where('status', BookingStatus::Completed->value)
            ->whereNull('follow_up_sent_at')
            ->whereDate('completed_at', '<=', now()->subDay()->toDateString())
            ->with(['customer:id,name,email', 'hotel:id,name', 'roomType:id,name'])
            ->get();

        foreach ($bookings as $booking) {
            Mail::to($booking->customer->email)->queue(new BookingFollowUpMail($booking));
            $booking->forceFill([
                'follow_up_sent_at' => now(),
            ])->save();
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

}
