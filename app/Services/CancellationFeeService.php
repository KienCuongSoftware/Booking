<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\CancellationPolicy;
use App\Models\CancellationPolicyTier;
use Carbon\CarbonInterface;

class CancellationFeeService
{
    /**
     * @return array{fee_percent: float, fee_amount: float, refund_amount: float, hours_before_check_in: int, policy_snapshot: array<string, mixed>}
     */
    public function calculate(Booking $booking, ?CarbonInterface $cancelledAt = null): array
    {
        $booking->loadMissing('hotel.cancellationPolicy.tiers');

        $cancelledAt ??= now();
        $checkInAt = $booking->check_in_date->copy()->startOfDay();
        $hoursBeforeCheckIn = max(0, $cancelledAt->diffInHours($checkInAt, false));

        $policy = $booking->hotel->cancellationPolicy;
        $tier = $this->findMatchingTier($policy, $hoursBeforeCheckIn);
        $feePercent = (float) ($tier?->fee_percent ?? 0);
        $totalPrice = (float) $booking->total_price;
        $feeAmount = round(($totalPrice * $feePercent) / 100, 2);
        $refundAmount = round(max(0, $totalPrice - $feeAmount), 2);

        return [
            'fee_percent' => $feePercent,
            'fee_amount' => $feeAmount,
            'refund_amount' => $refundAmount,
            'hours_before_check_in' => $hoursBeforeCheckIn,
            'policy_snapshot' => [
                'policy_id' => $policy?->id,
                'policy_name' => $policy?->name,
                'tier_id' => $tier?->id,
                'min_hours_before' => $tier?->min_hours_before,
                'max_hours_before' => $tier?->max_hours_before,
                'fee_percent' => $feePercent,
                'hours_before_check_in' => $hoursBeforeCheckIn,
            ],
        ];
    }

    private function findMatchingTier(?CancellationPolicy $policy, int $hoursBeforeCheckIn): ?CancellationPolicyTier
    {
        if (! $policy) {
            return null;
        }

        return $policy->tiers->first(function (CancellationPolicyTier $tier) use ($hoursBeforeCheckIn): bool {
            if ($hoursBeforeCheckIn < $tier->min_hours_before) {
                return false;
            }

            if ($tier->max_hours_before !== null && $hoursBeforeCheckIn >= $tier->max_hours_before) {
                return false;
            }

            return true;
        });
    }
}
