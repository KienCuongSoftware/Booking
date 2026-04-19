<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\BookingStatusUpdateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(
        private readonly BookingStatusUpdateService $bookingStatusUpdateService,
    ) {}

    public function index(Request $request): View
    {
        $bookings = $this->bookingsQuery($request, null)->paginate(20)->withQueryString();

        return view('staff.bookings.index', compact('bookings'));
    }

    public function pending(Request $request): View
    {
        $request->merge(['status' => 'pending']);
        $bookings = $this->bookingsQuery($request, 'pending')->paginate(20)->withQueryString();

        return view('staff.bookings.pending', compact('bookings'));
    }

    public function history(Request $request): View
    {
        $bookings = Booking::query()
            ->with(['customer:id,name,email', 'hotel:id,name', 'roomType:id,name'])
            ->whereIn('status', ['completed', 'cancelled', 'no_show'])
            ->when($request->filled('q'), function ($q) use ($request): void {
                $term = '%'.trim((string) $request->string('q')->value()).'%';
                $q->where(function ($inner) use ($term): void {
                    $inner->where('booking_code', 'like', $term)
                        ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', $term)->orWhere('email', 'like', $term));
                });
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('staff.bookings.history', compact('bookings'));
    }

    public function updateStatus(Request $request, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:confirmed,cancelled,completed,no_show'],
            'host_note' => ['nullable', 'string', 'max:1000'],
            'internal_tags' => ['nullable', 'string', 'max:500'],
            'mark_paid' => ['nullable', 'boolean'],
        ]);

        try {
            $this->bookingStatusUpdateService->apply($booking, $request->user(), $validated, $request, 'staff_status_updated');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('status', __('Đã cập nhật trạng thái đơn đặt.'));
    }

    private function bookingsQuery(Request $request, ?string $forcedStatus)
    {
        $query = Booking::query()
            ->with(['customer:id,name,email', 'hotel:id,name', 'roomType:id,name'])
            ->latest('id');

        $status = $forcedStatus ?? $request->string('status')->value();
        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        if ($request->filled('q')) {
            $term = '%'.trim((string) $request->string('q')->value()).'%';
            $query->where(function ($q) use ($term): void {
                $q->where('booking_code', 'like', $term)
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', $term)->orWhere('email', 'like', $term))
                    ->orWhereHas('hotel', fn ($h) => $h->where('name', 'like', $term));
            });
        }

        return $query;
    }
}
