<?php

namespace Tests\Feature;

use App\Enums\BookingPaymentMethod;
use App\Enums\BookingPaymentProvider;
use App\Enums\BookingPaymentStatus;
use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Hotel;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

class CustomerExperienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_bookings_index_is_accessible(): void
    {
        $customer = $this->makeCustomer();

        $this->actingAs($customer)
            ->get(route('customer.bookings.index'))
            ->assertOk()
            ->assertSee(__('Đơn đặt của tôi'), false);
    }

    public function test_customer_can_view_own_booking_detail(): void
    {
        $host = $this->makeHost();
        $customer = $this->makeCustomer();
        $hotel = $this->makeHotel($host);
        $roomType = $this->makeRoomType($hotel);
        $booking = $this->makeBooking($customer, $hotel, $roomType);

        $this->actingAs($customer)
            ->get(route('customer.bookings.show', $booking))
            ->assertOk()
            ->assertSee($booking->booking_code, false);
    }

    public function test_customer_cannot_view_other_booking_detail(): void
    {
        $host = $this->makeHost();
        $customerA = $this->makeCustomer();
        $customerB = $this->makeCustomer();
        $hotel = $this->makeHotel($host);
        $roomType = $this->makeRoomType($hotel);
        $booking = $this->makeBooking($customerA, $hotel, $roomType);

        $this->actingAs($customerB)
            ->get(route('customer.bookings.show', $booking))
            ->assertForbidden();
    }

    public function test_customer_cannot_view_pass_for_pending_booking(): void
    {
        $host = $this->makeHost();
        $customer = $this->makeCustomer();
        $hotel = $this->makeHotel($host);
        $roomType = $this->makeRoomType($hotel);
        $booking = $this->makeBooking($customer, $hotel, $roomType, [
            'status' => BookingStatus::Pending,
        ]);

        $this->actingAs($customer)
            ->get(route('customer.bookings.pass', $booking))
            ->assertRedirect(route('customer.bookings.show', $booking))
            ->assertSessionHasErrors('pass');
    }

    public function test_customer_scanning_qr_entry_is_redirected_without_403(): void
    {
        $host = $this->makeHost();
        $customer = $this->makeCustomer();
        $hotel = $this->makeHotel($host);
        $roomType = $this->makeRoomType($hotel);
        $booking = $this->makeBooking($customer, $hotel, $roomType, [
            'status' => BookingStatus::Confirmed,
            'check_in_token' => 'token-scan-entry',
        ]);

        $payload = json_encode([
            'booking_code' => $booking->booking_code,
            'token' => $booking->check_in_token,
        ], JSON_UNESCAPED_UNICODE);
        $encodedPayload = rtrim(strtr(base64_encode((string) $payload), '+/', '-_'), '=');

        $this->actingAs($customer)
            ->get(route('check-in.entry', ['payload' => $encodedPayload]))
            ->assertRedirect(route('customer.bookings.index'))
            ->assertSessionHas('status');
    }

    private function makeCustomer(): User
    {
        return User::factory()->create([
            'role' => UserRole::Customer,
            'email_verified_at' => now(),
        ]);
    }

    private function makeHost(): User
    {
        return User::factory()->create([
            'role' => UserRole::Host,
            'email_verified_at' => now(),
        ]);
    }

    private function makeHotel(User $host): Hotel
    {
        return Hotel::query()->create([
            'host_id' => $host->id,
            'name' => 'Hotel '.Str::random(5),
            'slug' => 'hotel-'.Str::lower(Str::random(8)),
            'city' => 'Ho Chi Minh',
            'address' => '1 Nguyen Hue',
            'star_rating' => 4,
            'base_price' => 1_000_000,
            'is_active' => true,
        ]);
    }

    private function makeRoomType(Hotel $hotel): RoomType
    {
        return RoomType::query()->create([
            'hotel_id' => $hotel->id,
            'name' => 'Deluxe '.Str::random(4),
            'slug' => 'deluxe-'.Str::lower(Str::random(8)),
            'max_occupancy' => 2,
            'quantity' => 3,
            'base_price' => 1_000_000,
            'new_price' => 1_000_000,
            'is_active' => true,
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function makeBooking(User $customer, Hotel $hotel, RoomType $roomType, array $overrides = []): Booking
    {
        $defaults = [
            'booking_code' => 'TST-'.Str::upper(Str::random(10)),
            'customer_id' => $customer->id,
            'hotel_id' => $hotel->id,
            'room_type_id' => $roomType->id,
            'check_in_date' => Carbon::today()->addDays(5),
            'check_out_date' => Carbon::today()->addDays(7),
            'guest_count' => 2,
            'nights' => 2,
            'unit_price' => 1_000_000,
            'total_price' => 2_000_000,
            'currency' => 'VND',
            'status' => BookingStatus::Pending,
            'payment_method' => BookingPaymentMethod::Cash,
            'payment_provider' => null,
            'payment_status' => BookingPaymentStatus::Unpaid,
            'status_changed_at' => now(),
            'status_changed_by' => $customer->id,
        ];

        return Booking::query()->create(array_merge($defaults, $overrides));
    }
}
