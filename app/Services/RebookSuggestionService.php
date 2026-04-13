<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RebookSuggestionService
{
    /**
     * @return Collection<int, array{check_in_date: string, check_out_date: string, nights: int, same_weekday: bool}>
     */
    public function suggestDates(Booking $booking, int $limit = 5): Collection
    {
        $booking->loadMissing('roomType');

        $nights = max(1, (int) $booking->nights);
        $today = Carbon::today();
        $startDate = $booking->check_in_date->copy()->greaterThan($today)
            ? $booking->check_in_date->copy()
            : $today->copy()->addDay();
        $targetWeekday = $booking->check_in_date->dayOfWeek;

        $suggestions = collect();

        for ($offset = 0; $offset <= 60 && $suggestions->count() < $limit; $offset++) {
            $checkIn = $startDate->copy()->addDays($offset)->startOfDay();
            $checkOut = $checkIn->copy()->addDays($nights);

            if (! $this->isRoomTypeAvailable($booking, $checkIn, $checkOut)) {
                continue;
            }

            $suggestions->push([
                'check_in_date' => $checkIn->toDateString(),
                'check_out_date' => $checkOut->toDateString(),
                'nights' => $nights,
                'same_weekday' => $checkIn->dayOfWeek === $targetWeekday,
            ]);
        }

        return $suggestions
            ->sortByDesc('same_weekday')
            ->values()
            ->take($limit);
    }

    private function isRoomTypeAvailable(Booking $booking, Carbon $checkIn, Carbon $checkOut): bool
    {
        $capacity = (int) $booking->roomType->quantity;
        if ($capacity <= 0) {
            return false;
        }

        $reservedCount = Booking::query()
            ->where('room_type_id', $booking->room_type_id)
            ->whereIn('status', [BookingStatus::Pending->value, BookingStatus::Confirmed->value])
            ->whereDate('check_in_date', '<', $checkOut->toDateString())
            ->whereDate('check_out_date', '>', $checkIn->toDateString())
            ->count();

        return $reservedCount < $capacity;
    }
}
