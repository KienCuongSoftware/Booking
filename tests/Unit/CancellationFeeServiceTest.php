<?php

namespace Tests\Unit;

use App\Enums\BookingPaymentMethod;
use App\Enums\BookingPaymentStatus;
use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\CancellationPolicy;
use App\Models\Hotel;
use App\Models\RoomType;
use App\Models\User;
use App\Services\CancellationFeeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

class CancellationFeeServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_applies_fee_percent_by_tier_window(): void
    {
        $host = User::factory()->create(['role' => UserRole::Host, 'email_verified_at' => now()]);
        $customer = User::factory()->create(['role' => UserRole::Customer, 'email_verified_at' => now()]);
        $hotel = Hotel::query()->create([
            'host_id' => $host->id,
            'name' => 'Policy Hotel',
            'slug' => 'policy-'.Str::lower(Str::random(6)),
            'city' => 'Hanoi',
            'address' => '12 Tran Hung Dao',
            'star_rating' => 4,
            'base_price' => 1_000_000,
            'is_active' => true,
        ]);
        $roomType = RoomType::query()->create([
            'hotel_id' => $hotel->id,
            'name' => 'Standard',
            'slug' => 'std-'.Str::lower(Str::random(6)),
            'max_occupancy' => 2,
            'quantity' => 3,
            'base_price' => 1_000_000,
            'new_price' => 1_000_000,
            'is_active' => true,
        ]);

        $policy = CancellationPolicy::query()->create([
            'hotel_id' => $hotel->id,
            'name' => 'Standard',
            'is_active' => true,
        ]);
        $policy->tiers()->createMany([
            ['min_hours_before' => 72, 'max_hours_before' => null, 'fee_percent' => 0, 'sort_order' => 1],
            ['min_hours_before' => 24, 'max_hours_before' => 72, 'fee_percent' => 30, 'sort_order' => 2],
            ['min_hours_before' => 0, 'max_hours_before' => 24, 'fee_percent' => 50, 'sort_order' => 3],
        ]);

        $booking = Booking::query()->create([
            'booking_code' => 'UT-'.Str::upper(Str::random(8)),
            'customer_id' => $customer->id,
            'hotel_id' => $hotel->id,
            'room_type_id' => $roomType->id,
            'check_in_date' => Carbon::today()->addDays(4),
            'check_out_date' => Carbon::today()->addDays(6),
            'guest_count' => 2,
            'nights' => 2,
            'unit_price' => 1_000_000,
            'total_price' => 2_000_000,
            'currency' => 'VND',
            'status' => BookingStatus::Pending,
            'payment_method' => BookingPaymentMethod::Cash,
            'payment_status' => BookingPaymentStatus::Unpaid,
            'status_changed_at' => now(),
            'status_changed_by' => $customer->id,
        ]);

        $service = app(CancellationFeeService::class);
        $highWindow = $service->calculate($booking, $booking->check_in_date->copy()->subHours(80));
        $midWindow = $service->calculate($booking, $booking->check_in_date->copy()->subHours(36));
        $lowWindow = $service->calculate($booking, $booking->check_in_date->copy()->subHours(8));

        $this->assertSame(0.0, $highWindow['fee_percent']);
        $this->assertSame(30.0, $midWindow['fee_percent']);
        $this->assertSame(50.0, $lowWindow['fee_percent']);
    }
}
