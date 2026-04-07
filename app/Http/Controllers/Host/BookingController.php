<?php

namespace App\Http\Controllers\Host;

use App\Enums\BookingPaymentStatus;
use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(Request $request): View
    {
        $query = Booking::query()
            ->whereHas('hotel', function ($builder) use ($request): void {
                $builder->where('host_id', $request->user()->id);
            })
            ->with(['customer:id,name,email', 'hotel:id,name', 'roomType:id,name']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->value());
        }

        $bookings = $query->latest('id')->paginate(12)->withQueryString();

        return view('host.bookings.index', compact('bookings'));
    }

    public function updateStatus(Request $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->hotel->host_id === $request->user()->id, 403);

        $validated = $request->validate([
            'status' => ['required', 'in:confirmed,cancelled,completed'],
            'host_note' => ['nullable', 'string', 'max:1000'],
            'mark_paid' => ['nullable', 'boolean'],
        ]);

        $booking->status = BookingStatus::from($validated['status']);
        $booking->host_note = $validated['host_note'] ?: $booking->host_note;

        if (($validated['mark_paid'] ?? false) && $booking->payment_status !== BookingPaymentStatus::Paid) {
            $booking->payment_status = BookingPaymentStatus::Paid;
        }

        $booking->save();

        return back()->with('status', __('Đã cập nhật trạng thái đơn đặt.'));
    }
}

