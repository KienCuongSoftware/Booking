<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Amenity;
use App\Models\Hotel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestCatalogAdvancedFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_filter_hotels_by_price_range(): void
    {
        $host = User::factory()->create(['role' => UserRole::Host]);

        $cheapHotel = Hotel::query()->create([
            'host_id' => $host->id,
            'name' => 'Hotel Gia Mem',
            'slug' => 'hotel-gia-mem',
            'city' => 'Ha Noi',
            'address' => '1 Price Road',
            'base_price' => 700000,
            'new_price' => 700000,
            'is_active' => true,
        ]);
        $midHotel = Hotel::query()->create([
            'host_id' => $host->id,
            'name' => 'Hotel Tam Trung',
            'slug' => 'hotel-tam-trung',
            'city' => 'Ha Noi',
            'address' => '2 Price Road',
            'base_price' => 1500000,
            'new_price' => 1500000,
            'is_active' => true,
        ]);
        $expensiveHotel = Hotel::query()->create([
            'host_id' => $host->id,
            'name' => 'Hotel Gia Cao',
            'slug' => 'hotel-gia-cao',
            'city' => 'Ha Noi',
            'address' => '3 Price Road',
            'base_price' => 3200000,
            'new_price' => 3200000,
            'is_active' => true,
        ]);

        $this->get(route('home', ['min_price' => 900000, 'max_price' => 2500000]))
            ->assertOk()
            ->assertDontSee($cheapHotel->name)
            ->assertSee($midHotel->name)
            ->assertDontSee($expensiveHotel->name);
    }

    public function test_guest_can_filter_hotels_by_multiple_amenities(): void
    {
        $host = User::factory()->create(['role' => UserRole::Host]);
        $wifi = Amenity::query()->create(['name' => 'Wifi', 'icon_key' => 'wifi', 'sort_order' => 1]);
        $pool = Amenity::query()->create(['name' => 'Pool', 'icon_key' => 'pool', 'sort_order' => 2]);
        $spa = Amenity::query()->create(['name' => 'Spa', 'icon_key' => 'spa', 'sort_order' => 3]);

        $hotelAll = Hotel::query()->create([
            'host_id' => $host->id,
            'name' => 'Hotel Day Du Tien Ich',
            'slug' => 'hotel-day-du-tien-ich',
            'city' => 'Da Nang',
            'address' => '1 Amenity St',
            'base_price' => 1200000,
            'new_price' => 1200000,
            'is_active' => true,
        ]);
        $hotelWifiOnly = Hotel::query()->create([
            'host_id' => $host->id,
            'name' => 'Hotel Chi Co Wifi',
            'slug' => 'hotel-chi-co-wifi',
            'city' => 'Da Nang',
            'address' => '2 Amenity St',
            'base_price' => 1100000,
            'new_price' => 1100000,
            'is_active' => true,
        ]);

        $hotelAll->amenities()->attach([$wifi->id, $pool->id, $spa->id]);
        $hotelWifiOnly->amenities()->attach([$wifi->id]);

        $this->get(route('home', ['amenity_ids' => [$wifi->id, $pool->id]]))
            ->assertOk()
            ->assertSee($hotelAll->name)
            ->assertDontSee($hotelWifiOnly->name);
    }
}
