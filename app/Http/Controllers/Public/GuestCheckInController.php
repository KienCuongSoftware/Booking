<?php

namespace App\Http\Controllers\Public;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Trang công khai (không cần đăng nhập) để khách đưa cho lễ tân xem thông tin đơn sau khi quét QR.
 * Chỉ hiển thị dữ liệu tối thiểu khi payload chứa token hợp lệ.
 */
class GuestCheckInController extends Controller
{
    public function show(Request $request): View
    {
        $payload = (string) $request->query('payload', '');
        $booking = $this->resolveBookingFromPayload($payload);

        return view('public.check-in-guest', [
            'valid' => $booking !== null,
            'booking' => $booking,
        ]);
    }

    private function resolveBookingFromPayload(string $payload): ?Booking
    {
        $payload = trim($payload);
        if ($payload === '') {
            return null;
        }

        $token = $this->extractToken($payload);
        if ($token === '') {
            return null;
        }

        $bookingCode = $this->extractBookingCode($payload);

        $query = Booking::query()
            ->with(['hotel:id,name,city', 'roomType:id,name'])
            ->where('check_in_token', $token);

        if ($bookingCode !== null && $bookingCode !== '') {
            $query->where('booking_code', $bookingCode);
        }

        $booking = $query->first();

        if (! $booking || ! in_array($booking->status, [BookingStatus::Confirmed, BookingStatus::Completed], true)) {
            return null;
        }

        return $booking;
    }

    /**
     * @return array{0: ?string, 1: string} [bookingCode, token]
     */
    private function parseJsonPayload(string $raw): array
    {
        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return [null, ''];
        }

        $code = isset($decoded['booking_code']) ? trim((string) $decoded['booking_code']) : null;
        $token = trim((string) ($decoded['token'] ?? $decoded['check_in_token'] ?? ''));

        return [$code !== '' ? $code : null, $token];
    }

    private function extractToken(string $payload): string
    {
        if ($this->looksLikeBase64Url($payload)) {
            $decoded = $this->decodeBase64Url($payload);
            if (is_string($decoded) && $decoded !== '') {
                $payload = $decoded;
            }
        }

        [, $token] = $this->parseJsonPayload($payload);
        if ($token !== '') {
            return $token;
        }

        if (str_contains($payload, '|')) {
            $parts = explode('|', $payload, 2);
            if (isset($parts[1]) && trim($parts[1]) !== '') {
                return trim($parts[1]);
            }
        }

        return trim($payload);
    }

    private function extractBookingCode(string $payload): ?string
    {
        if ($this->looksLikeBase64Url($payload)) {
            $decoded = $this->decodeBase64Url($payload);
            if (is_string($decoded) && $decoded !== '') {
                $payload = $decoded;
            }
        }

        [$code] = $this->parseJsonPayload($payload);
        if ($code !== null) {
            return $code;
        }

        if (str_contains($payload, '|')) {
            $parts = explode('|', $payload, 2);
            $first = trim($parts[0]);

            return $first !== '' ? $first : null;
        }

        return null;
    }

    private function looksLikeBase64Url(string $value): bool
    {
        return preg_match('/^[A-Za-z0-9\-_]+={0,2}$/', $value) === 1;
    }

    private function decodeBase64Url(string $value): ?string
    {
        $base64 = strtr($value, '-_', '+/');
        $padding = strlen($base64) % 4;
        if ($padding > 0) {
            $base64 .= str_repeat('=', 4 - $padding);
        }
        $decoded = base64_decode($base64, true);

        return $decoded === false ? null : $decoded;
    }
}
