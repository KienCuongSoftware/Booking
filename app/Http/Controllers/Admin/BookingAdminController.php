<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingAdminController extends Controller
{
    public function index(Request $request): View
    {
        $query = Booking::query()
            ->with(['customer:id,name,email', 'hotel:id,name', 'roomType:id,name'])
            ->latest('id');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->value());
        }

        if ($request->filled('q')) {
            $term = trim((string) $request->string('q')->value());
            if ($term !== '') {
                $like = '%'.$term.'%';
                $query->where(function ($q) use ($like): void {
                    $q->where('booking_code', 'like', $like)
                        ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', $like)->orWhere('email', 'like', $like))
                        ->orWhereHas('hotel', fn ($h) => $h->where('name', 'like', $like));
                });
            }
        }

        $bookings = $query->paginate(25)->withQueryString();

        return view('admin.bookings.index', compact('bookings'));
    }
}
