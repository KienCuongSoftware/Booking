<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuestSavedHotelController extends Controller
{
    private const SESSION_KEY = 'guest_saved_hotel_ids';

    public function index(Request $request): View
    {
        $savedIds = $this->savedIds($request);
        $savedMap = array_flip($savedIds);

        $hotels = Hotel::query()
            ->whereIn('id', $savedIds)
            ->where('is_active', true)
            ->with('province')
            ->withAvg('reviews', 'rating')
            ->get()
            ->sortBy(fn (Hotel $hotel): int => $savedMap[$hotel->id] ?? PHP_INT_MAX)
            ->values();

        return view('public.hotels.saved', [
            'hotels' => $hotels,
            'savedHotelIds' => $savedIds,
            'ogTitle' => config('app.name').' — '.__('Danh sách xem sau'),
            'ogDescription' => __('Các khách sạn bạn đã lưu để xem lại sau.'),
            'ogImage' => asset('ico.svg'),
            'canonicalUrl' => route('public.hotels.saved', absolute: true),
        ]);
    }

    public function toggle(Request $request, Hotel $hotel): RedirectResponse
    {
        if (! $hotel->is_active) {
            return back()->withErrors(['saved' => __('Khách sạn này hiện không khả dụng.')]);
        }

        $ids = $this->savedIds($request);
        if (in_array($hotel->id, $ids, true)) {
            $ids = array_values(array_filter($ids, fn (int $id): bool => $id !== $hotel->id));
            $message = __('Đã bỏ khỏi danh sách xem sau.');
        } else {
            array_unshift($ids, $hotel->id);
            $ids = array_values(array_unique(array_slice($ids, 0, 50)));
            $message = __('Đã lưu vào danh sách xem sau.');
        }

        $request->session()->put(self::SESSION_KEY, $ids);

        return back()->with('status', $message);
    }

    /**
     * @return array<int, int>
     */
    private function savedIds(Request $request): array
    {
        $raw = $request->session()->get(self::SESSION_KEY, []);
        if (! is_array($raw)) {
            return [];
        }

        return array_values(array_unique(array_map('intval', array_filter($raw, fn ($v): bool => is_numeric($v)))));
    }
}
