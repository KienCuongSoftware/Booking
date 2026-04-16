<?php

namespace App\Http\Controllers\Customer;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function create(Request $request, Booking $booking): View|RedirectResponse
    {
        abort_unless($booking->customer_id === $request->user()->id, 403);
        abort_unless($booking->status === BookingStatus::Completed, 403);

        if ($booking->review()->exists()) {
            return redirect()
                ->route('customer.bookings.index')
                ->with('status', __('Bạn đã gửi đánh giá cho đơn này.'));
        }

        $booking->loadMissing(['hotel:id,name,slug', 'roomType:id,name']);

        return view('customer.bookings.review', compact('booking'));
    }

    public function store(Request $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->customer_id === $request->user()->id, 403);
        abort_unless($booking->status === BookingStatus::Completed, 403);

        if ($booking->review()->exists()) {
            return redirect()
                ->route('customer.bookings.index')
                ->withErrors(['rating' => __('Bạn đã gửi đánh giá cho đơn này.')]);
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        Review::query()->create([
            'booking_id' => $booking->id,
            'rating' => (int) $validated['rating'],
            'comment' => ($validated['comment'] ?? null) ?: null,
        ]);

        return redirect()
            ->route('customer.bookings.index')
            ->with('status', __('Cảm ơn bạn đã đánh giá.'));
    }
}
