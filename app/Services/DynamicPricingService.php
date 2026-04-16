<?php

namespace App\Services;

use App\Models\Hotel;
use App\Models\PublicHoliday;
use App\Models\RoomType;
use Illuminate\Support\Carbon;

class DynamicPricingService
{
    /**
     * @return array{nightly: list<array{date: string, amount: float, flags: list<string>}>, subtotal: float, nights: int, avg_unit_price: float, last_minute_applied: bool}
     */
    public function quote(Hotel $hotel, RoomType $roomType, Carbon $checkIn, Carbon $checkOut, ?Carbon $reference = null): array
    {
        if (! config('booking.dynamic_pricing.enabled', true)) {
            $nights = max(1, $checkIn->diffInDays($checkOut));
            $base = (float) ($roomType->new_price ?? $roomType->base_price);
            $subtotal = round($base * $nights, 2);

            return [
                'nightly' => [],
                'subtotal' => $subtotal,
                'nights' => $nights,
                'avg_unit_price' => $nights > 0 ? round($subtotal / $nights, 2) : $base,
                'last_minute_applied' => false,
            ];
        }

        $reference ??= Carbon::now();

        $nights = max(1, $checkIn->diffInDays($checkOut));
        $baseNightly = (float) ($roomType->new_price ?? $roomType->base_price);
        $weekendMul = $roomType->weekend_multiplier !== null
            ? (float) $roomType->weekend_multiplier
            : (float) ($hotel->weekend_multiplier ?? 1.1);
        $holidayMul = $roomType->holiday_multiplier !== null
            ? (float) $roomType->holiday_multiplier
            : (float) ($hotel->holiday_multiplier ?? 1.25);

        $nightly = [];
        $cursor = $checkIn->copy()->startOfDay();
        $end = $checkOut->copy()->startOfDay();

        while ($cursor->lt($end)) {
            $mult = 1.0;
            $flags = [];

            if ($this->isWeekendNight($cursor)) {
                $mult *= $weekendMul;
                $flags[] = 'weekend';
            }

            if (PublicHoliday::isHoliday($cursor)) {
                $mult *= $holidayMul;
                $flags[] = 'holiday';
            }

            $amount = round($baseNightly * $mult, 2);
            $nightly[] = [
                'date' => $cursor->toDateString(),
                'amount' => $amount,
                'flags' => $flags,
            ];

            $cursor->addDay();
        }

        $subtotal = round(array_sum(array_column($nightly, 'amount')), 2);

        $lastMinuteHours = (int) ($hotel->last_minute_hours ?? 72);
        $lastMinuteDiscount = (float) ($hotel->last_minute_discount_percent ?? 0);
        $lastMinuteApplied = false;

        if ($lastMinuteDiscount > 0 && $lastMinuteHours > 0) {
            $checkInAt = $checkIn->copy()->setTime(14, 0);
            $hoursToCheckIn = $reference->diffInHours($checkInAt, false);
            if ($hoursToCheckIn >= 0 && $hoursToCheckIn <= $lastMinuteHours) {
                $subtotal = round($subtotal * (1 - $lastMinuteDiscount / 100), 2);
                $lastMinuteApplied = true;
            }
        }

        $count = count($nightly) ?: $nights;

        return [
            'nightly' => $nightly,
            'subtotal' => $subtotal,
            'nights' => $nights,
            'avg_unit_price' => $count > 0 ? round($subtotal / $count, 2) : $baseNightly,
            'last_minute_applied' => $lastMinuteApplied,
        ];
    }

    private function isWeekendNight(Carbon $date): bool
    {
        return $date->isFriday() || $date->isSaturday() || $date->isSunday();
    }
}
