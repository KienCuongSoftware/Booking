<?php

namespace App\Services;

use App\Mail\WaitlistSlotAvailableMail;
use App\Models\Booking;
use App\Models\WaitlistEntry;
use Illuminate\Support\Facades\Mail;

class BookingWaitlistService
{
    public function notifyForFreedSlot(Booking $booking): int
    {
        $booking->loadMissing(['hotel:id,name', 'roomType:id,name']);

        $entries = WaitlistEntry::query()
            ->where('room_type_id', $booking->room_type_id)
            ->whereNull('notified_at')
            ->whereDate('check_in_date', '<', $booking->check_out_date->toDateString())
            ->whereDate('check_out_date', '>', $booking->check_in_date->toDateString())
            ->with('user:id,name,email')
            ->orderBy('id')
            ->limit(15)
            ->get();

        $sent = 0;
        foreach ($entries as $entry) {
            if (! $entry->user?->email) {
                continue;
            }

            $entry->loadMissing(['hotel:id,name,slug', 'roomType:id,name', 'user:id,name,email']);

            Mail::to($entry->user->email)->queue(new WaitlistSlotAvailableMail($entry, $booking));
            $entry->forceFill(['notified_at' => now()])->save();
            $sent++;
        }

        return $sent;
    }
}
