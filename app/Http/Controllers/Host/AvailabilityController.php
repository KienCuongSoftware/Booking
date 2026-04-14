<?php

namespace App\Http\Controllers\Host;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Hotel;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AvailabilityController extends Controller
{
    public function index(Request $request): View
    {
        $hotels = Hotel::query()
            ->where('host_id', $request->user()->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        $selectedHotel = Hotel::query()
            ->where('host_id', $request->user()->id)
            ->where('id', $request->integer('hotel_id') ?: ($hotels->first()->id ?? 0))
            ->with('roomTypes:id,hotel_id,name,quantity,is_active')
            ->first();

        $start = Carbon::today();
        $days = 14;
        $dateKeys = collect(CarbonPeriod::create($start, $start->copy()->addDays($days - 1)))
            ->map(fn (Carbon $date) => $date->toDateString())
            ->values();

        $matrix = [];
        if ($selectedHotel) {
            $bookings = Booking::query()
                ->where('hotel_id', $selectedHotel->id)
                ->whereIn('status', [BookingStatus::Pending->value, BookingStatus::Confirmed->value])
                ->whereDate('check_in_date', '<=', $start->copy()->addDays($days)->toDateString())
                ->whereDate('check_out_date', '>', $start->toDateString())
                ->get(['id', 'room_type_id', 'check_in_date', 'check_out_date']);

            foreach ($selectedHotel->roomTypes->where('is_active', true) as $roomType) {
                foreach ($dateKeys as $dateKey) {
                    $date = Carbon::parse($dateKey);
                    $booked = $bookings->filter(function (Booking $booking) use ($roomType, $date): bool {
                        return $booking->room_type_id === $roomType->id
                            && $booking->check_in_date->lt($date->copy()->addDay())
                            && $booking->check_out_date->gt($date);
                    })->count();

                    $available = max(0, (int) $roomType->quantity - $booked);
                    $matrix[$roomType->id][$dateKey] = [
                        'booked' => $booked,
                        'available' => $available,
                        'capacity' => (int) $roomType->quantity,
                    ];
                }
            }
        }

        return view('host.availability.index', [
            'hotels' => $hotels,
            'selectedHotel' => $selectedHotel,
            'dateKeys' => $dateKeys,
            'matrix' => $matrix,
        ]);
    }
}
