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
            ->with(['roomTypes:id,hotel_id,name,quantity,is_active', 'roomTypes.physicalRooms' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')->orderBy('id')])
            ->first();

        $start = Carbon::today();
        $days = 14;
        $dateKeys = collect(CarbonPeriod::create($start, $start->copy()->addDays($days - 1)))
            ->map(fn (Carbon $date) => $date->toDateString())
            ->values();

        $matrix = [];
        $physicalRows = [];
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

            $assignedBookings = Booking::query()
                ->where('hotel_id', $selectedHotel->id)
                ->whereNotNull('physical_room_id')
                ->whereIn('status', [
                    BookingStatus::Pending->value,
                    BookingStatus::Confirmed->value,
                    BookingStatus::Completed->value,
                ])
                ->whereDate('check_in_date', '<=', $start->copy()->addDays($days)->toDateString())
                ->whereDate('check_out_date', '>', $start->toDateString())
                ->get(['physical_room_id', 'check_in_date', 'check_out_date', 'booking_code']);

            foreach ($selectedHotel->roomTypes->where('is_active', true) as $roomType) {
                $rooms = $roomType->physicalRooms;
                if ($rooms->isEmpty()) {
                    continue;
                }
                foreach ($rooms as $physicalRoom) {
                    $cells = [];
                    foreach ($dateKeys as $dateKey) {
                        $night = Carbon::parse($dateKey)->startOfDay();
                        $code = $assignedBookings->first(function (Booking $b) use ($physicalRoom, $night): bool {
                            return (int) $b->physical_room_id === (int) $physicalRoom->id
                                && $b->check_in_date->lte($night)
                                && $b->check_out_date->gt($night);
                        })?->booking_code;
                        $cells[$dateKey] = $code ?? '—';
                    }
                    $physicalRows[] = [
                        'roomType' => $roomType,
                        'physicalRoom' => $physicalRoom,
                        'cells' => $cells,
                    ];
                }
            }
        }

        return view('host.availability.index', [
            'hotels' => $hotels,
            'selectedHotel' => $selectedHotel,
            'dateKeys' => $dateKeys,
            'matrix' => $matrix,
            'physicalRows' => $physicalRows,
        ]);
    }
}
