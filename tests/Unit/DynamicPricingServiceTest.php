<?php

namespace Tests\Unit;

use App\Enums\UserRole;
use App\Models\Hotel;
use App\Models\PublicHoliday;
use App\Models\RoomType;
use App\Models\User;
use App\Services\DynamicPricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DynamicPricingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_weekend_multiplier_increases_subtotal(): void
    {
        config(['booking.dynamic_pricing.enabled' => true]);

        $host = User::factory()->create(['role' => UserRole::Host]);
        $hotel = Hotel::query()->create([
            'host_id' => $host->id,
            'name' => 'Test Hotel',
            'slug' => 'test-hotel-abc',
            'city' => 'HCMC',
            'province_code' => null,
            'address' => '1 Test',
            'star_rating' => 4,
            'base_price' => 1_000_000,
            'new_price' => 1_000_000,
            'weekend_multiplier' => 2,
            'holiday_multiplier' => 1,
            'last_minute_hours' => 0,
            'last_minute_discount_percent' => 0,
            'is_active' => true,
        ]);

        $room = RoomType::query()->create([
            'hotel_id' => $hotel->id,
            'name' => 'Deluxe',
            'slug' => 'deluxe-abc',
            'max_occupancy' => 2,
            'quantity' => 5,
            'base_price' => 100,
            'new_price' => 100,
            'is_active' => true,
        ]);

        $checkIn = Carbon::parse('2026-04-17'); // Friday
        $checkOut = Carbon::parse('2026-04-19'); // Sunday checkout (2 nights Fri+Sat)

        $quote = app(DynamicPricingService::class)->quote($hotel, $room, $checkIn, $checkOut, Carbon::parse('2026-04-01'));

        $this->assertSame(2, (int) $quote['nights']);
        $this->assertGreaterThan(200.0, $quote['subtotal']);
    }

    public function test_holiday_multiplier_applies_when_holiday_row_exists(): void
    {
        config(['booking.dynamic_pricing.enabled' => true]);

        PublicHoliday::query()->create([
            'holiday_date' => '2026-04-20',
            'name' => 'Test holiday',
            'country' => 'VN',
        ]);

        $host = User::factory()->create(['role' => UserRole::Host]);
        $hotel = Hotel::query()->create([
            'host_id' => $host->id,
            'name' => 'Test Hotel 2',
            'slug' => 'test-hotel-def',
            'city' => 'HCMC',
            'province_code' => null,
            'address' => '1 Test',
            'star_rating' => 4,
            'base_price' => 100,
            'new_price' => 100,
            'weekend_multiplier' => 1,
            'holiday_multiplier' => 3,
            'last_minute_hours' => 0,
            'last_minute_discount_percent' => 0,
            'is_active' => true,
        ]);

        $room = RoomType::query()->create([
            'hotel_id' => $hotel->id,
            'name' => 'Deluxe',
            'slug' => 'deluxe-def',
            'max_occupancy' => 2,
            'quantity' => 5,
            'base_price' => 100,
            'new_price' => 100,
            'is_active' => true,
        ]);

        $checkIn = Carbon::parse('2026-04-20');
        $checkOut = Carbon::parse('2026-04-21');

        $quote = app(DynamicPricingService::class)->quote($hotel, $room, $checkIn, $checkOut, Carbon::parse('2026-04-01'));

        $this->assertSame(300.0, $quote['subtotal']);
    }
}
