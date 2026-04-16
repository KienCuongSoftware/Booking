<?php

namespace App\Http\Controllers\Host;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\RoomType;
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

        $topRoomTypes = RoomType::query()
            ->whereIn('hotel_id', $hotelIds)
            ->withCount(['bookings as bookings_count' => function ($q): void {
                $q->whereIn('status', [BookingStatus::Confirmed->value, BookingStatus::Completed->value, BookingStatus::Pending->value]);
            }])
            ->orderByDesc('bookings_count')
            ->limit(5)
            ->get(['id', 'hotel_id', 'name']);

        return view('host.reports.index', compact('revenue', 'cancelRate', 'noShowRate', 'topRoomTypes', 'totalBookings'));
    }
}
