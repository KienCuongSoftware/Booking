<?php

namespace Database\Seeders;

use App\Models\CancellationPolicy;
use App\Models\Hotel;
use Illuminate\Database\Seeder;

class CancellationPolicySeeder extends Seeder
{
    public function run(): void
    {
        $hotels = Hotel::query()->get(['id']);

        foreach ($hotels as $hotel) {
            $policy = CancellationPolicy::query()->updateOrCreate(
                ['hotel_id' => $hotel->id],
                [
                    'name' => 'Chính sách tiêu chuẩn',
                    'is_active' => true,
                ],
            );

            $tiers = [
                ['min_hours_before' => 72, 'max_hours_before' => null, 'fee_percent' => 0, 'sort_order' => 1],
                ['min_hours_before' => 24, 'max_hours_before' => 72, 'fee_percent' => 30, 'sort_order' => 2],
                ['min_hours_before' => 0, 'max_hours_before' => 24, 'fee_percent' => 50, 'sort_order' => 3],
            ];

            foreach ($tiers as $tier) {
                $policy->tiers()->updateOrCreate(
                    [
                        'min_hours_before' => $tier['min_hours_before'],
                        'max_hours_before' => $tier['max_hours_before'],
                    ],
                    [
                        'fee_percent' => $tier['fee_percent'],
                        'sort_order' => $tier['sort_order'],
                    ],
                );
            }
        }
    }
}
