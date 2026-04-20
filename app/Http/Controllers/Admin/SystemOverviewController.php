<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Hotel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SystemOverviewController extends Controller
{
    public function __invoke(Request $request): View
    {
        $timeWindowOptions = [7, 30, 90];
        $timeWindowDays = (int) $request->integer('days', 30);
        if (! in_array($timeWindowDays, $timeWindowOptions, true)) {
            $timeWindowDays = 30;
        }
        $timeWindowStart = now()->copy()->subDays($timeWindowDays - 1)->startOfDay();

        $bookingsByStatus = Booking::query()
            ->selectRaw('status, COUNT(*) as c')
            ->where('created_at', '>=', $timeWindowStart)
            ->groupBy('status')
            ->pluck('c', 'status');

        $usersByRole = User::query()
            ->selectRaw('role, COUNT(*) as c')
            ->where('created_at', '>=', $timeWindowStart)
            ->groupBy('role')
            ->pluck('c', 'role');

        $recentBookings = Booking::query()
            ->with(['customer:id,name', 'hotel:id,name'])
            ->latest('id')
            ->limit(10)
            ->get();

        $topHotels = Hotel::query()
            ->withCount([
                'bookings as bookings_count' => fn ($q) => $q->where('created_at', '>=', $timeWindowStart),
            ])
            ->orderByDesc('bookings_count')
            ->limit(5)
            ->get(['id', 'name', 'city']);

        $pendingBookings = Booking::query()
            ->where('status', BookingStatus::Pending->value)
            ->count();

        $statusOrder = [
            BookingStatus::Pending,
            BookingStatus::Confirmed,
            BookingStatus::Completed,
            BookingStatus::Cancelled,
            BookingStatus::NoShow,
        ];
        $statusLabels = array_map(fn (BookingStatus $s): string => $s->labelVi(), $statusOrder);
        $statusSeries = array_map(
            fn (BookingStatus $s): int => (int) ($bookingsByStatus[$s->value] ?? 0),
            $statusOrder,
        );

        $roleOrder = [
            UserRole::Admin,
            UserRole::Host,
            UserRole::Staff,
            UserRole::Customer,
        ];
        $roleLabels = array_map(fn (UserRole $r): string => $r->shortLabelVi(), $roleOrder);
        $roleSeries = array_map(
            fn (UserRole $r): int => (int) ($usersByRole[$r->value] ?? 0),
            $roleOrder,
        );

        $topHotelLabels = $topHotels
            ->map(fn (Hotel $h): string => $h->name.' ('.$h->city.')')
            ->values()
            ->all();
        $topHotelSeries = $topHotels
            ->pluck('bookings_count')
            ->map(fn ($v): int => (int) $v)
            ->values()
            ->all();

        $totals = [
            'bookings' => (int) Booking::query()->count(),
            'users' => (int) User::query()->count(),
            'hotels' => (int) Hotel::query()->count(),
            'pending' => $pendingBookings,
        ];

        return view('admin.overview', compact(
            'bookingsByStatus',
            'usersByRole',
            'recentBookings',
            'topHotels',
            'pendingBookings',
            'statusLabels',
            'statusSeries',
            'roleLabels',
            'roleSeries',
            'topHotelLabels',
            'topHotelSeries',
            'totals',
            'timeWindowDays',
            'timeWindowOptions',
        ));
    }
}
