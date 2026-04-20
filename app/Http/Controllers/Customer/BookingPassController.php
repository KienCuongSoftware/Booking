<?php

namespace App\Http\Controllers\Customer;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingPassController extends Controller
{
    public function show(Request $request, Booking $booking): View|RedirectResponse
    {
        abort_unless($booking->customer_id === $request->user()->id, 403);
        if ($booking->status !== BookingStatus::Confirmed) {
            return redirect()
                ->route('customer.bookings.show', $booking)
                ->withErrors(['pass' => __('Vé QR chỉ khả dụng khi đơn đã được xác nhận.')]);
        }

        $booking->loadMissing(['hotel:id,name,slug', 'roomType:id,name']);

        $payloadData = [
            'v' => 1,
            'booking_code' => $booking->booking_code,
            'token' => $booking->check_in_token,
            'hotel' => $booking->hotel->name,
            'room_type' => $booking->roomType->name,
            'check_in_date' => $booking->check_in_date?->toDateString(),
            'check_out_date' => $booking->check_out_date?->toDateString(),
            'guest_count' => $booking->guest_count,
        ];

        $payload = json_encode($payloadData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            ?: ($booking->booking_code.'|'.$booking->check_in_token);
        $encodedPayload = rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');
        $checkInUrl = route('check-in.entry', ['payload' => $encodedPayload], true);
        $guestInfoUrl = route('check-in.guest', ['payload' => $encodedPayload], true);
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data='.rawurlencode($checkInUrl);

        return view('customer.bookings.pass', compact('booking', 'qrUrl', 'payload', 'checkInUrl', 'guestInfoUrl'));
    }
}
