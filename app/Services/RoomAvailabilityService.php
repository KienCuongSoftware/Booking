<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\RoomType;
use Illuminate\Support\Carbon;

class RoomAvailabilityService
{
    /**
     * @return array<int, string>
     */
    public function blockedDatesForRoomType(RoomType $roomType, Carbon $windowStart, Carbon $windowEnd, ?int $excludeBookingId = null): array
    {
        $start = $windowStart->copy()->startOfDay();
        $end = $windowEnd->copy()->startOfDay();

        $bookings = Booking::query()
            ->where('room_type_id', $roomType->id)
            ->whereIn('status', [BookingStatus::Pending->value, BookingStatus::Confirmed->value])
            ->when($excludeBookingId !== null, fn ($q) => $q->where('id', '!=', $excludeBookingId))
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

    /**
     * @param  array<int, string>  $blockedDates
     */
    public function firstBlockedDateInRange(array $blockedDates, Carbon $checkIn, Carbon $checkOut): ?string
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
