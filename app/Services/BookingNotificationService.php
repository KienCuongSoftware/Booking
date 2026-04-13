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
        $bookings = Booking::query()
            ->where('status', BookingStatus::Confirmed->value)
            ->whereDate('check_in_date', now()->addDay()->toDateString())
            ->whereNull('reminder_sent_at')
            ->with(['customer:id,name,email', 'hotel:id,name', 'roomType:id,name'])
            ->get();

        foreach ($bookings as $booking) {
            Mail::to($booking->customer->email)->queue(new BookingReminderMail($booking));
            $booking->forceFill([
                'reminder_sent_at' => now(),
            ])->save();
        }

        return $bookings->count();
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
}
