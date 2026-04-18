<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Hotel;
use App\Models\User;
use Illuminate\View\View;

class SystemOverviewController extends Controller
{
    public function __invoke(): View
    {
        $bookingsByStatus = Booking::query()
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        $usersByRole = User::query()
            ->selectRaw('role, COUNT(*) as c')
            ->groupBy('role')
            ->pluck('c', 'role');

        $recentBookings = Booking::query()
            ->with(['customer:id,name', 'hotel:id,name'])
            ->latest('id')
            ->limit(10)
            ->get();

        $topHotels = Hotel::query()
            ->withCount('bookings')
            ->orderByDesc('bookings_count')
            ->limit(5)
            ->get(['id', 'name', 'city']);

        $pendingBookings = Booking::query()
            ->where('status', BookingStatus::Pending->value)
            ->count();

        return view('admin.overview', compact(
            'bookingsByStatus',
            'usersByRole',
            'recentBookings',
            'topHotels',
            'pendingBookings',
        ));
    }
}
