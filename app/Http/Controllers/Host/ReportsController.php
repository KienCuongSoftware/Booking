<?php

namespace App\Http\Controllers\Host;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\RoomType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportsController extends Controller
{
    public function __invoke(Request $request): View
    {
        $hostId = $request->user()->id;

        $hotelIds = DB::table('hotels')->where('host_id', $hostId)->pluck('id');

        if ($hotelIds->isEmpty()) {
            return view('host.reports.index', [
                'revenue' => 0.0,
                'cancelRate' => 0.0,
                'noShowRate' => 0.0,
                'topRoomTypes' => collect(),
                'totalBookings' => 0,
                'chartLabels' => [],
                'chartRevenueSeries' => [],
                'chartCancelRateSeries' => [],
                'chartNoShowRateSeries' => [],
            ]);
        }

        $revenue = (float) Booking::query()
            ->whereIn('hotel_id', $hotelIds)
            ->whereIn('status', [BookingStatus::Confirmed->value, BookingStatus::Completed->value])
            ->sum('total_price');

        $totalBookings = (int) Booking::query()->whereIn('hotel_id', $hotelIds)->count();
        $cancelled = (int) Booking::query()->whereIn('hotel_id', $hotelIds)->where('status', BookingStatus::Cancelled->value)->count();
        $noShow = (int) Booking::query()->whereIn('hotel_id', $hotelIds)->where('status', BookingStatus::NoShow->value)->count();

        $cancelRate = $totalBookings > 0 ? round(($cancelled / $totalBookings) * 100, 1) : 0.0;
        $noShowRate = $totalBookings > 0 ? round(($noShow / $totalBookings) * 100, 1) : 0.0;

        // Chart series (last 6 months)
        $monthsCount = 6;
        $start = now()->copy()->subMonths($monthsCount - 1)->startOfMonth();
        $end = now()->copy()->endOfMonth();

        $monthKeys = [];
        $chartLabels = [];
        $bucket = [];

        for ($i = 0; $i < $monthsCount; $i++) {
            $m = $start->copy()->addMonths($i);
            $key = $m->format('Y-m');
            $monthKeys[] = $key;
            $chartLabels[] = $m->format('M/Y');
            $bucket[$key] = [
                'revenue' => 0.0,
                'cancelled' => 0,
                'no_show' => 0,
                'total' => 0,
            ];
        }

        $bookingsForChart = Booking::query()
            ->whereIn('hotel_id', $hotelIds)
            ->whereBetween('created_at', [$start, $end])
            ->get(['created_at', 'status', 'total_price']);

        foreach ($bookingsForChart as $b) {
            $key = Carbon::parse($b->created_at)->format('Y-m');
            if (! isset($bucket[$key])) {
                continue;
            }

            $bucket[$key]['total']++;

            if ($b->status === BookingStatus::Cancelled->value) {
                $bucket[$key]['cancelled']++;
            }

            if ($b->status === BookingStatus::NoShow->value) {
                $bucket[$key]['no_show']++;
            }

            if (in_array($b->status, [BookingStatus::Confirmed->value, BookingStatus::Completed->value], true)) {
                $bucket[$key]['revenue'] += (float) $b->total_price;
            }
        }

        $chartRevenueSeries = [];
        $chartCancelRateSeries = [];
        $chartNoShowRateSeries = [];
        foreach ($monthKeys as $key) {
            $chartRevenueSeries[] = (float) $bucket[$key]['revenue'];

            $total = (int) $bucket[$key]['total'];
            $chartCancelRateSeries[] = $total > 0 ? round(($bucket[$key]['cancelled'] / $total) * 100, 1) : 0.0;
            $chartNoShowRateSeries[] = $total > 0 ? round(($bucket[$key]['no_show'] / $total) * 100, 1) : 0.0;
        }

        $topRoomTypes = RoomType::query()
            ->whereIn('hotel_id', $hotelIds)
            ->withCount(['bookings as bookings_count' => function ($q): void {
                $q->whereIn('status', [BookingStatus::Confirmed->value, BookingStatus::Completed->value, BookingStatus::Pending->value]);
            }])
            ->orderByDesc('bookings_count')
            ->limit(5)
            ->get(['id', 'hotel_id', 'name']);

        return view('host.reports.index', compact(
            'revenue',
            'cancelRate',
            'noShowRate',
            'topRoomTypes',
            'totalBookings',
            'chartLabels',
            'chartRevenueSeries',
            'chartCancelRateSeries',
            'chartNoShowRateSeries',
        ));
    }
}
