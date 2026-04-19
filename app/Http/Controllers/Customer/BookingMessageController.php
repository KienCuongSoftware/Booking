<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingMessageController extends Controller
{
    public function index(Request $request, Booking $booking): View
    {
        abort_unless($booking->customer_id === $request->user()->id, 403);

        $booking->load([
            'hotel:id,name',
            'messages' => fn ($q) => $q->orderBy('id'),
            'messages.sender:id,name',
        ]);

        return view('customer.bookings.messages', compact('booking'));
    }

    public function store(Request $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->customer_id === $request->user()->id, 403);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        BookingMessage::query()->create([
            'booking_id' => $booking->id,
            'sender_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);

        return back()->with('status', __('Đã gửi tin nhắn.'));
    }
}
