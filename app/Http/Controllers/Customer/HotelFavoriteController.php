<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HotelFavoriteController extends Controller
{
    public function index(Request $request): View
    {
        $hotels = $request->user()
            ->favoriteHotels()
            ->where('hotels.is_active', true)
            ->with('province')
            ->orderByPivot('created_at', 'desc')
            ->paginate(12);

        return view('customer.favorites.index', compact('hotels'));
    }

    public function toggle(Request $request, Hotel $hotel): RedirectResponse
    {
        abort_unless($hotel->is_active, 404);

        if ($request->user()->favoriteHotels()->where('hotel_id', $hotel->id)->exists()) {
            $request->user()->favoriteHotels()->detach($hotel->id);

            return back()->with('status', __('Đã bỏ khách sạn khỏi yêu thích.'));
        }

        $request->user()->favoriteHotels()->attach($hotel->id);

        return back()->with('status', __('Đã thêm khách sạn vào yêu thích.'));
    }
}
