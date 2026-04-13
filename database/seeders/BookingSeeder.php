<?php

namespace Database\Seeders;

use App\Enums\BookingPaymentMethod;
use App\Enums\BookingPaymentProvider;
use App\Enums\BookingPaymentStatus;
use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::query()
            ->where('role', UserRole::Customer->value)
            ->get(['id']);

        if ($customers->isEmpty()) {
            return;
        }

        $roomTypes = RoomType::query()
            ->with(['hotel:id,name'])
            ->where('is_active', true)
            ->get();

        if ($roomTypes->isEmpty()) {
            return;
        }

        $statusPool = [
            BookingStatus::Pending,
            BookingStatus::Confirmed,
            BookingStatus::Cancelled,
            BookingStatus::Completed,
        ];

        $createdCount = 0;

        foreach ($roomTypes as $roomType) {
            $customer = $customers->random();
            $nights = random_int(1, 4);
            $checkIn = Carbon::today()->addDays(random_int(-7, 14));
            $checkOut = $checkIn->copy()->addDays($nights);
            $unitPrice = (float) ($roomType->new_price ?? $roomType->base_price);
            $paymentMethod = random_int(0, 1) === 0
                ? BookingPaymentMethod::Cash
                : BookingPaymentMethod::BankTransfer;
            $paymentProvider = $paymentMethod === BookingPaymentMethod::BankTransfer
                ? (random_int(0, 1) === 0 ? BookingPaymentProvider::Momo : BookingPaymentProvider::Paypal)
                : null;
            $status = $statusPool[array_rand($statusPool)];

            $paymentStatus = match ($status) {
                BookingStatus::Cancelled => BookingPaymentStatus::Failed,
                BookingStatus::Completed => BookingPaymentStatus::Paid,
                default => $paymentMethod === BookingPaymentMethod::Cash
                    ? BookingPaymentStatus::Unpaid
                    : BookingPaymentStatus::Pending,
            };

            Booking::query()->updateOrCreate(
                ['booking_code' => 'SEED-'.$roomType->id.'-'.($createdCount + 1)],
                [
                    'customer_id' => $customer->id,
                    'hotel_id' => $roomType->hotel_id,
                    'room_type_id' => $roomType->id,
                    'check_in_date' => $checkIn->toDateString(),
                    'check_out_date' => $checkOut->toDateString(),
                    'guest_count' => min(max(1, random_int(1, max(1, (int) $roomType->max_occupancy))), 10),
                    'nights' => $nights,
                    'unit_price' => $unitPrice,
                    'total_price' => $unitPrice * $nights,
                    'currency' => 'VND',
                    'status' => $status->value,
                    'payment_method' => $paymentMethod->value,
                    'payment_provider' => $paymentProvider?->value,
                    'payment_status' => $paymentStatus->value,
                    'payment_reference' => $paymentProvider ? strtoupper($paymentProvider->value).'-'.Str::upper(Str::random(10)) : null,
                    'customer_note' => null,
                    'host_note' => null,
                ]
            );

            $createdCount++;

            if ($createdCount >= 30) {
                break;
            }
        }
    }
}
