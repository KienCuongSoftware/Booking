<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\Province;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HotelCatalogController extends Controller
{
    public function index(Request $request): View
    {
        $query = Hotel::query()
            ->where('is_active', true)
            ->with('province');

        if ($request->filled('province_code')) {
            $query->where('province_code', $request->input('province_code'));
        }

        if ($request->filled('q')) {
            $q = $request->string('q')->trim()->value();
            if ($q !== '') {
                $like = '%'.$q.'%';
                $query->where(function ($builder) use ($like): void {
                    $builder->where('name', 'like', $like)
                        ->orWhere('address', 'like', $like)
                        ->orWhere('city', 'like', $like);
                });
            }
        }

        $sort = $request->string('sort', 'newest')->value();
        match ($sort) {
            'price_asc' => $query->orderByRaw('COALESCE(new_price, base_price) ASC')->orderBy('name'),
            'price_desc' => $query->orderByRaw('COALESCE(new_price, base_price) DESC')->orderBy('name'),
            'name' => $query->orderBy('name')->orderBy('id'),
            default => $query->latest('id'),
        };

        $hotels = $query->paginate(9)->withQueryString();

        $provinces = Province::query()
            ->orderBy('name')
            ->get(['code', 'name', 'type']);

        return view('public.hotels.index', [
            'hotels' => $hotels,
            'provinces' => $provinces,
        ]);
    }

    public function show(Hotel $hotel): View
    {
        abort_unless($hotel->is_active, 404);

        $hotel->load([
            'province',
            'amenities',
            'galleryImages',
            'roomTypes' => fn ($q) => $q->where('is_active', true)->orderBy('name')->orderBy('id'),
            'roomTypes.bedLines',
            'roomTypes.amenities',
            'roomTypes.images',
        ]);

        return view('public.hotels.show', compact('hotel'));
    }
}
