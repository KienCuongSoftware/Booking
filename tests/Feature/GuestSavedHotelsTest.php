<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Hotel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestSavedHotelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_save_and_unsave_hotel_in_session(): void
    {
        $host = User::factory()->create(['role' => UserRole::Host]);
        $hotel = Hotel::query()->create([
            'host_id' => $host->id,
            'name' => 'Saved Hotel',
            'slug' => 'saved-hotel-test',
            'city' => 'Ha Noi',
            'address' => '1 Example Street',
            'base_price' => 1200000,
            'is_active' => true,
        ]);

        $this->post(route('public.hotels.saved.toggle', $hotel))
            ->assertRedirect();

        $this->assertContains($hotel->id, session('guest_saved_hotel_ids', []));

        $this->post(route('public.hotels.saved.toggle', $hotel))
            ->assertRedirect();

        $this->assertNotContains($hotel->id, session('guest_saved_hotel_ids', []));
    }

    public function test_saved_hotels_page_lists_hotels_from_session(): void
    {
        $host = User::factory()->create(['role' => UserRole::Host]);
        $hotel = Hotel::query()->create([
            'host_id' => $host->id,
            'name' => 'Wishlist Candidate',
            'slug' => 'wishlist-candidate',
            'city' => 'Da Nang',
            'address' => '2 Demo Road',
            'base_price' => 900000,
            'is_active' => true,
        ]);

        $this->withSession(['guest_saved_hotel_ids' => [$hotel->id]])
            ->get(route('public.hotels.saved'))
            ->assertOk()
            ->assertSee('Wishlist Candidate');
    }
}
