<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerBookingApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->role === UserRole::Customer, 403);

        $bookings = Booking::query()
            ->where('customer_id', $request->user()->id)
            ->with(['hotel:id,name,slug', 'roomType:id,name'])
            ->latest('id')
            ->paginate(min(50, max(1, (int) $request->integer('per_page', 15))));

        return response()->json($bookings);
    }
}
