<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HotelCatalogApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
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

        $hotels = $query->latest('id')->paginate(min(50, max(1, (int) $request->integer('per_page', 15))));

        return response()->json($hotels);
    }

    public function show(Hotel $hotel): JsonResponse
    {
        abort_unless($hotel->is_active, 404);

        $hotel->load([
            'province',
            'roomTypes' => fn ($q) => $q->where('is_active', true)->orderBy('name'),
        ]);

        return response()->json([
            'hotel' => $hotel,
            'avg_rating' => $hotel->reviews()->avg('rating'),
        ]);
    }
}
