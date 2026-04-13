<?php

namespace Tests\Feature;

use App\Enums\BookingPaymentMethod;
use App\Enums\BookingPaymentStatus;
use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Mail\BookingCreatedMail;
use App\Mail\BookingFollowUpMail;
use App\Mail\BookingReminderMail;
use App\Mail\BookingStatusChangedMail;
use App\Models\Booking;
use App\Models\CancellationPolicy;
use App\Models\Hotel;
use App\Models\RoomType;
use App\Models\User;
use App\Services\RebookSuggestionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class BookingLifecycleFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_host_can_transition_pending_booking_to_confirmed(): void
    {
        Mail::fake();

        $host = $this->createUser(UserRole::Host);
        $customer = $this->createUser(UserRole::Customer);
        $hotel = $this->createHotel($host);
        $roomType = $this->createRoomType($hotel);
        $this->seedCancellationPolicy($hotel);
        $booking = $this->createBooking($customer, $hotel, $roomType, [
            'status' => BookingStatus::Pending,
        ]);

        $this->actingAs($host)->patch(route('host.bookings.update-status', $booking), [
            'status' => BookingStatus::Confirmed->value,
        ])->assertSessionHas('status');

        $booking->refresh();

        $this->assertSame(BookingStatus::Confirmed, $booking->status);
        $this->assertNotNull($booking->confirmed_at);
        $this->assertNotNull($booking->status_changed_at);
        Mail::assertQueued(BookingStatusChangedMail::class, 1);
    }

    public function test_customer_cancel_applies_tiered_fee_and_marks_metadata(): void
    {
        Mail::fake();

        $host = $this->createUser(UserRole::Host);
        $customer = $this->createUser(UserRole::Customer);
        $hotel = $this->createHotel($host);
        $roomType = $this->createRoomType($hotel);
        $this->seedCancellationPolicy($hotel);

        $booking = $this->createBooking($customer, $hotel, $roomType, [
            'status' => BookingStatus::Confirmed,
            'check_in_date' => Carbon::today()->addDays(2),
            'check_out_date' => Carbon::today()->addDays(4),
            'nights' => 2,
            'unit_price' => 1_000_000,
            'total_price' => 2_000_000,
        ]);

        $this->actingAs($customer)->patch(route('customer.bookings.cancel', $booking), [
            'cancel_reason' => 'Đổi kế hoạch công tác',
        ])->assertSessionHas('status');

        $booking->refresh();

        $this->assertSame(BookingStatus::Cancelled, $booking->status);
        $this->assertSame(600000.0, (float) $booking->cancellation_fee_amount);
        $this->assertSame(1400000.0, (float) $booking->refund_amount);
        $this->assertNotNull($booking->cancelled_at);
        $this->assertSame($customer->id, $booking->cancelled_by);
        Mail::assertQueued(BookingStatusChangedMail::class, 1);
    }

    public function test_rebook_suggestions_return_up_to_five_available_dates(): void
    {
        $host = $this->createUser(UserRole::Host);
        $customer = $this->createUser(UserRole::Customer);
        $hotel = $this->createHotel($host);
        $roomType = $this->createRoomType($hotel, ['quantity' => 1]);
        $this->seedCancellationPolicy($hotel);

        $baseBooking = $this->createBooking($customer, $hotel, $roomType, [
            'status' => BookingStatus::Completed,
            'check_in_date' => Carbon::today()->subDays(7),
            'check_out_date' => Carbon::today()->subDays(5),
            'nights' => 2,
        ]);

        $this->createBooking($customer, $hotel, $roomType, [
            'status' => BookingStatus::Confirmed,
            'check_in_date' => Carbon::today()->addDay(),
            'check_out_date' => Carbon::today()->addDays(3),
            'nights' => 2,
        ]);

        $suggestions = app(RebookSuggestionService::class)->suggestDates($baseBooking);

        $this->assertLessThanOrEqual(5, $suggestions->count());
        $this->assertGreaterThan(0, $suggestions->count());
        $this->assertArrayHasKey('check_in_date', $suggestions->first());
        $this->assertArrayHasKey('same_weekday', $suggestions->first());
    }

    public function test_booking_created_sends_email_to_customer_and_host(): void
    {
        Mail::fake();

        $host = $this->createUser(UserRole::Host);
        $customer = $this->createUser(UserRole::Customer);
        $hotel = $this->createHotel($host);
        $roomType = $this->createRoomType($hotel);
        $this->seedCancellationPolicy($hotel);

        $this->actingAs($customer)->post(route('customer.bookings.store', $hotel), [
            'room_type_id' => $roomType->id,
            'check_in_date' => Carbon::today()->addDays(5)->toDateString(),
            'check_out_date' => Carbon::today()->addDays(7)->toDateString(),
            'guest_count' => 2,
            'payment_method' => BookingPaymentMethod::Cash->value,
        ])->assertRedirect(route('customer.bookings.index'));

        Mail::assertQueued(BookingCreatedMail::class, 2);
    }

    public function test_scheduler_commands_send_reminder_and_follow_up_emails(): void
    {
        Mail::fake();

        $host = $this->createUser(UserRole::Host);
        $customer = $this->createUser(UserRole::Customer);
        $hotel = $this->createHotel($host);
        $roomType = $this->createRoomType($hotel);
        $this->seedCancellationPolicy($hotel);

        $reminderBooking = $this->createBooking($customer, $hotel, $roomType, [
            'status' => BookingStatus::Confirmed,
            'check_in_date' => Carbon::today()->addDay(),
            'check_out_date' => Carbon::today()->addDays(3),
            'nights' => 2,
        ]);

        $followUpBooking = $this->createBooking($customer, $hotel, $roomType, [
            'status' => BookingStatus::Completed,
            'check_in_date' => Carbon::today()->subDays(3),
            'check_out_date' => Carbon::today()->subDay(),
            'nights' => 2,
            'completed_at' => Carbon::yesterday()->subHours(2),
        ]);

        $this->artisan('bookings:send-reminders')->assertSuccessful();
        $this->artisan('bookings:send-follow-ups')->assertSuccessful();

        $reminderBooking->refresh();
        $followUpBooking->refresh();

        Mail::assertQueued(BookingReminderMail::class, 1);
        Mail::assertQueued(BookingFollowUpMail::class, 1);
        $this->assertNotNull($reminderBooking->reminder_sent_at);
        $this->assertNotNull($followUpBooking->follow_up_sent_at);
    }

    private function createUser(UserRole $role): User
    {
        return User::factory()->create([
            'role' => $role,
            'email_verified_at' => now(),
        ]);
    }

    private function createHotel(User $host): Hotel
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

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createRoomType(Hotel $hotel, array $attributes = []): RoomType
    {
        return RoomType::query()->create(array_merge([
            'hotel_id' => $hotel->id,
            'name' => 'Deluxe '.Str::random(4),
            'slug' => 'deluxe-'.Str::lower(Str::random(8)),
            'max_occupancy' => 2,
            'quantity' => 3,
            'base_price' => 1_000_000,
            'new_price' => 1_000_000,
            'is_active' => true,
        ], $attributes));
    }

    private function seedCancellationPolicy(Hotel $hotel): void
    {
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
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createBooking(User $customer, Hotel $hotel, RoomType $roomType, array $attributes = []): Booking
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

        return Booking::query()->create(array_merge($defaults, $attributes));
    }
}
