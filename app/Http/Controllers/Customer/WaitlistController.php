<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\WaitlistEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WaitlistController extends Controller
{
    public function index(Request $request): View
    {
        $entries = WaitlistEntry::query()
            ->where('user_id', $request->user()->id)
            ->with(['hotel:id,name,slug', 'roomType:id,name'])
            ->latest('id')
            ->paginate(12);

        return view('customer.waitlist.index', compact('entries'));
    }

    public function create(Request $request, Hotel $hotel): View
    {
        abort_unless($hotel->is_active, 404);

        $hotel->load([
            'roomTypes' => fn ($q) => $q->where('is_active', true)->orderBy('name')->orderBy('id'),
        ]);

        return view('customer.waitlist.create', compact('hotel'));
    }

    public function store(Request $request, Hotel $hotel): RedirectResponse
    {
        abort_unless($hotel->is_active, 404);

        $validated = $request->validate([
            'room_type_id' => ['required', 'integer'],
            'check_in_date' => ['required', 'date', 'after_or_equal:today'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'guest_count' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        $roomType = $hotel->roomTypes()
            ->where('is_active', true)
            ->where('id', $validated['room_type_id'])
            ->firstOrFail();

        WaitlistEntry::query()->updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'hotel_id' => $hotel->id,
                'room_type_id' => $roomType->id,
                'check_in_date' => $validated['check_in_date'],
                'check_out_date' => $validated['check_out_date'],
            ],
            [
                'guest_count' => (int) $validated['guest_count'],
                'notified_at' => null,
            ],
        );

        return redirect()
            ->route('customer.waitlist.index')
            ->with('status', __('Đã đăng ký chờ. Bạn sẽ nhận email khi có chỗ trống.'));
    }
}
