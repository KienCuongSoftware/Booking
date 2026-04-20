<?php

namespace App\Http\Controllers\Public;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Models\Hotel;
use App\Models\Province;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class HotelCatalogController extends Controller
{
    public function index(Request $request): View
    {
        $selectedAmenityIds = collect((array) $request->input('amenity_ids', []))
            ->filter(fn ($id): bool => is_numeric($id))
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values();

        $minPrice = $request->filled('min_price') ? max(0, (int) $request->integer('min_price')) : null;
        $maxPrice = $request->filled('max_price') ? max(0, (int) $request->integer('max_price')) : null;
        if ($minPrice !== null && $maxPrice !== null && $minPrice > $maxPrice) {
            [$minPrice, $maxPrice] = [$maxPrice, $minPrice];
        }

        $query = Hotel::query()
            ->where('is_active', true)
            ->with('province')
            ->withAvg('reviews', 'rating');

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

        if ($minPrice !== null) {
            $query->whereRaw('COALESCE(new_price, base_price) >= ?', [$minPrice]);
        }
        if ($maxPrice !== null) {
            $query->whereRaw('COALESCE(new_price, base_price) <= ?', [$maxPrice]);
        }

        if ($selectedAmenityIds->isNotEmpty()) {
            foreach ($selectedAmenityIds as $amenityId) {
                $query->whereHas('amenities', fn ($q) => $q->where('amenities.id', $amenityId));
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
        $amenities = Amenity::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name']);
        $savedHotelIds = collect((array) $request->session()->get('guest_saved_hotel_ids', []))
            ->filter(fn ($id): bool => is_numeric($id))
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        return view('public.hotels.index', [
            'hotels' => $hotels,
            'provinces' => $provinces,
            'amenities' => $amenities,
            'savedHotelIds' => $savedHotelIds,
            'selectedAmenityIds' => $selectedAmenityIds->all(),
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
            'ogTitle' => config('app.name').' — '.__('Tìm khách sạn'),
            'ogDescription' => __('Chỉ hiển thị khách sạn đang mở. Đặt phòng theo ngày.'),
            'ogImage' => asset('ico.svg'),
            'canonicalUrl' => url()->current(),
        ]);
    }

    public function show(Request $request, Hotel $hotel): View
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

        $hotel->loadCount('reviews');
        $avgRating = $hotel->reviews()->avg('rating');

        $isFavorite = false;
        $user = $request->user();
        if ($user && $user->role === UserRole::Customer) {
            $isFavorite = $user->favoriteHotels()->where('hotel_id', $hotel->id)->exists();
        }

        return view('public.hotels.show', [
            'hotel' => $hotel,
            'avgRating' => $avgRating,
            'isFavorite' => $isFavorite,
            'ogTitle' => $hotel->name,
            'ogDescription' => Str::limit(strip_tags($hotel->description ?? ''), 160),
            'ogImage' => $hotel->thumbnailUrl(),
            'canonicalUrl' => route('public.hotels.show', $hotel, absolute: true),
        ]);
    }
}
