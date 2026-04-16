<?php

namespace App\Http\Controllers\Host;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class BookingCheckInController extends Controller
{
    public function entry(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'payload' => ['required', 'string', 'max:5000'],
        ]);

        $parsed = $this->parseCheckInPayload($validated['payload']);
        $booking = $this->resolveBookingByPayload($parsed['booking_code'], $parsed['token']);
        if (! $booking) {
            return redirect()
                ->route($request->user()->role->dashboardRouteName())
                ->with('status', __('Mã QR không hợp lệ hoặc đã hết hiệu lực.'));
        }

        if ($request->user()->role->value !== 'host') {
            return redirect()
                ->route($request->user()->role->dashboardRouteName())
                ->with('status', __('Mã QR này dùng để host xác nhận check-in. Vui lòng đăng nhập tài khoản chủ khách sạn.'));
        }

        if ((int) $booking->hotel->host_id !== (int) $request->user()->id) {
            return redirect()
                ->route('host.bookings.index')
                ->with('status', __('Bạn không có quyền xác nhận check-in cho đơn này.'));
        }

        return redirect()->route('host.bookings.check-in.preview', ['payload' => $validated['payload']]);
    }

    public function preview(Request $request): View|RedirectResponse
    {
        $validated = $request->validate([
            'payload' => ['required', 'string', 'max:5000'],
        ]);

        $parsed = $this->parseCheckInPayload($validated['payload']);
        $booking = $this->resolveBookingForHost($parsed['booking_code'], $parsed['token'], (int) $request->user()->id);
        if (! $booking) {
            return redirect()
                ->route('host.bookings.index')
                ->withErrors(['token' => __('Không tìm thấy đơn phù hợp với mã QR.')]);
        }

        $booking->loadMissing(['hotel:id,name,host_id,address,city', 'roomType:id,name', 'customer:id,name,email']);
        $eligibilityError = $this->checkEligibilityError($booking);

        return view('host.bookings.check-in-preview', [
            'booking' => $booking,
            'payload' => $validated['payload'],
            'eligibilityError' => $eligibilityError,
        ]);
    }

    public function confirm(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'payload' => ['required', 'string', 'max:5000'],
        ]);

        $parsed = $this->parseCheckInPayload($validated['payload']);
        $booking = $this->resolveBookingForHost($parsed['booking_code'], $parsed['token'], (int) $request->user()->id);
        if (! $booking) {
            return redirect()
                ->route('host.bookings.index')
                ->withErrors(['token' => __('Không tìm thấy đơn phù hợp với mã QR.')]);
        }

        $eligibilityError = $this->checkEligibilityError($booking);
        if ($eligibilityError !== null) {
            return redirect()
                ->route('host.bookings.check-in.preview', ['payload' => $validated['payload']])
                ->withErrors(['token' => $eligibilityError]);
        }

        $booking->forceFill(['checked_in_at' => now()])->save();

        return redirect()
            ->route('host.bookings.index')
            ->with('status', __('Đã xác nhận khách đến cho đơn :code.', ['code' => $booking->booking_code]));
    }

    public function store(Request $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->hotel->host_id === $request->user()->id, 403);

        $validated = $request->validate([
            'token' => ['required', 'string', 'max:2000'],
        ]);

        $parsed = $this->parseCheckInPayload($validated['token']);
        if ($parsed['booking_code'] !== null && ! hash_equals((string) $booking->booking_code, $parsed['booking_code'])) {
            return back()->withErrors(['token' => __('Mã check-in không khớp với đơn đang xử lý.')]);
        }

        if (! hash_equals((string) $booking->check_in_token, $parsed['token'])) {
            return back()->withErrors(['token' => __('Mã xác nhận không đúng.')]);
        }

        $eligibilityError = $this->checkEligibilityError($booking);
        if ($eligibilityError !== null) {
            return back()->withErrors(['token' => $eligibilityError]);
        }

        $booking->forceFill(['checked_in_at' => now()])->save();

        return back()->with('status', __('Đã xác nhận khách đến.'));
    }

    /**
     * @return array{token: string, booking_code: string|null}
     */
    private function parseCheckInPayload(string $input): array
    {
        $raw = trim($input);
        $bookingCode = null;
        $token = $raw;

        if ($this->looksLikeBase64Url($raw)) {
            $decoded = $this->decodeBase64Url($raw);
            if (is_string($decoded) && $decoded !== '') {
                $raw = $decoded;
                $token = $raw;
            }
        }

        $decodedJson = json_decode($raw, true);
        if (is_array($decodedJson)) {
            $bookingCode = isset($decodedJson['booking_code']) ? trim((string) $decodedJson['booking_code']) : null;
            $token = trim((string) ($decodedJson['token'] ?? $decodedJson['check_in_token'] ?? ''));
            if ($token !== '') {
                return ['token' => $token, 'booking_code' => $bookingCode ?: null];
            }
        }

        if (str_contains($raw, '|')) {
            [$first, $second] = explode('|', $raw, 2);
            $candidateCode = trim($first);
            $candidateToken = trim($second);
            if ($candidateToken !== '') {
                return [
                    'booking_code' => $candidateCode !== '' ? $candidateCode : null,
                    'token' => $candidateToken,
                ];
            }
        }

        if (str_starts_with($raw, 'http://') || str_starts_with($raw, 'https://')) {
            $query = parse_url($raw, PHP_URL_QUERY);
            if (is_string($query) && $query !== '') {
                parse_str($query, $params);

                if (! empty($params['payload'])) {
                    $payloadRaw = is_string($params['payload']) ? trim($params['payload']) : '';
                    if ($payloadRaw !== '') {
                        $payloadDecoded = $this->decodeBase64Url($payloadRaw) ?? $payloadRaw;
                        $nested = $this->parseCheckInPayload($payloadDecoded);
                        if ($nested['token'] !== '') {
                            return $nested;
                        }
                    }
                }

                $candidateToken = trim((string) ($params['token'] ?? $params['check_in_token'] ?? ''));
                if ($candidateToken !== '') {
                    $candidateCode = trim((string) ($params['booking_code'] ?? $params['booking'] ?? ''));

                    return [
                        'booking_code' => $candidateCode !== '' ? $candidateCode : null,
                        'token' => $candidateToken,
                    ];
                }
            }
        }

        return [
            'booking_code' => null,
            'token' => $token,
        ];
    }

    private function resolveBookingForHost(?string $bookingCode, string $token, int $hostId): ?Booking
    {
        if ($token === '') {
            return null;
        }

        $query = Booking::query()
            ->whereHas('hotel', fn ($q) => $q->where('host_id', $hostId))
            ->where('check_in_token', $token);

        if ($bookingCode !== null && $bookingCode !== '') {
            $query->where('booking_code', $bookingCode);
        }

        return $query->first();
    }

    private function resolveBookingByPayload(?string $bookingCode, string $token): ?Booking
    {
        if ($token === '') {
            return null;
        }

        $query = Booking::query()
            ->with('hotel:id,host_id')
            ->where('check_in_token', $token);

        if ($bookingCode !== null && $bookingCode !== '') {
            $query->where('booking_code', $bookingCode);
        }

        return $query->first();
    }

    private function checkEligibilityError(Booking $booking): ?string
    {
        if ($booking->checked_in_at) {
            return __('Đơn đã được check-in trước đó.');
        }

        if ($booking->status !== BookingStatus::Confirmed) {
            return __('Chỉ có thể check-in đơn ở trạng thái đã xác nhận.');
        }

        $today = Carbon::today();
        if ($today->lt($booking->check_in_date) || $today->gt($booking->check_out_date)) {
            return __('Hiện chưa nằm trong khoảng ngày lưu trú để check-in.');
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
