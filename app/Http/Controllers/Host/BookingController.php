<?php

namespace App\Http\Controllers\Host;

use App\Enums\BookingPaymentStatus;
use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\BookingLifecycleService;
use App\Services\BookingNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(
        private readonly BookingLifecycleService $bookingLifecycleService,
        private readonly BookingNotificationService $bookingNotificationService,
    ) {}

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

        $originalStatus = $booking->status;
        $booking->host_note = ($validated['host_note'] ?? null) ?: $booking->host_note;

        if (($validated['mark_paid'] ?? false) && $booking->payment_status !== BookingPaymentStatus::Paid) {
            $booking->payment_status = BookingPaymentStatus::Paid;
        }

        $booking->save();
        $booking = $this->bookingLifecycleService->transition(
            $booking,
            BookingStatus::from($validated['status']),
            $request->user(),
        );
        $this->bookingNotificationService->sendStatusChanged($booking, $originalStatus);

        return back()->with('status', __('Đã cập nhật trạng thái đơn đặt.'));
    }
}

