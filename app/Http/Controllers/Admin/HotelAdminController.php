<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HotelAdminController extends Controller
{
    public function index(Request $request): View
    {
        $query = Hotel::query()->with('host:id,name,email')->orderByDesc('id');

        if ($request->filled('q')) {
            $term = '%'.trim((string) $request->string('q')->value()).'%';
            $query->where(function ($q) use ($term): void {
                $q->where('name', 'like', $term)
                    ->orWhere('city', 'like', $term)
                    ->orWhere('slug', 'like', $term);
            });
        }

        $hotels = $query->paginate(20)->withQueryString();

        return view('admin.hotels.index', compact('hotels'));
    }

    public function show(Hotel $hotel): View
    {
        $hotel->load([
            'host:id,name,email',
            'province',
            'galleryImages',
            'amenities',
            'cancellationPolicy.tiers',
            'roomTypes' => fn ($q) => $q->orderBy('name')->orderBy('id'),
            'roomTypes.images',
            'roomTypes.bedLines',
            'roomTypes.amenities',
        ]);

        return view('admin.hotels.show', compact('hotel'));
    }
}
