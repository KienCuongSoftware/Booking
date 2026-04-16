<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\IdempotencyKey;
use App\Models\User;

class IdempotencyService
{
    public const SCOPE_BOOKING_STORE = 'booking.store';

    public function existingBookingFor(User $user, string $scope, string $key): ?Booking
    {
        if (! config('booking.idempotency.enabled', true)) {
            return null;
        }

        $record = IdempotencyKey::query()
            ->where('user_id', $user->id)
            ->where('scope', $scope)
            ->where('key', $key)
            ->first();

        if (! $record?->booking_id) {
            return null;
        }

        return Booking::query()->find($record->booking_id);
    }

    public function remember(User $user, string $scope, string $key, Booking $booking): void
    {
        if (! config('booking.idempotency.enabled', true)) {
            return;
        }

        try {
            IdempotencyKey::query()->create([
                'scope' => $scope,
                'key' => $key,
                'user_id' => $user->id,
                'booking_id' => $booking->id,
                'created_at' => now(),
            ]);
        } catch (\Throwable) {
            // duplicate key — ignore
        }
    }

    public function attachBookingIdempotencyKey(Booking $booking, ?string $headerKey): void
    {
        if (! $headerKey || ! config('booking.idempotency.enabled', true)) {
            return;
        }

        $booking->forceFill(['idempotency_key' => $headerKey])->save();
    }
}
