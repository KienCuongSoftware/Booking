<?php

namespace App\Http\Controllers\Customer;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\BookingMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerInboxController extends Controller
{
    public function unreadCount(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user && $user->role === UserRole::Customer, 403);

        $unread = BookingMessage::query()
            ->whereHas('booking', fn ($q) => $q->where('customer_id', $user->id))
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->count();

        return response()->json(['unread' => $unread]);
    }
}
