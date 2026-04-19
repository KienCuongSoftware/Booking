<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\PromoCode;
use App\Models\RoomType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PromoCodeController extends Controller
{
    public function index(Request $request): View
    {
        $hotelIds = Hotel::query()->where('host_id', $request->user()->id)->pluck('id');

        $promoCodes = PromoCode::query()
            ->whereIn('hotel_id', $hotelIds)
            ->with(['hotel:id,name', 'roomType:id,name'])
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('host.promo-codes.index', compact('promoCodes'));
    }

    public function create(Request $request): View
    {
        $hotels = Hotel::query()->where('host_id', $request->user()->id)->orderBy('name')->get(['id', 'name']);
        $roomTypes = RoomType::query()
            ->whereIn('hotel_id', $hotels->pluck('id'))
            ->orderBy('name')
            ->get(['id', 'hotel_id', 'name']);

        return view('host.promo-codes.create', compact('hotels', 'roomTypes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $hotelIds = Hotel::query()->where('host_id', $request->user()->id)->pluck('id')->all();

        $validated = $request->validate([
            'hotel_id' => ['required', 'integer', Rule::in($hotelIds)],
            'room_type_id' => ['nullable', 'integer'],
            'code' => ['required', 'string', 'max:40', 'unique:promo_codes,code'],
            'valid_from' => ['required', 'date'],
            'valid_to' => ['required', 'date', 'after_or_equal:valid_from'],
            'discount_type' => ['required', Rule::in(['percent', 'fixed'])],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if (! empty($validated['room_type_id'])) {
            RoomType::query()->where('id', $validated['room_type_id'])->where('hotel_id', $validated['hotel_id'])->firstOrFail();
        }

        if ($validated['discount_type'] === 'percent' && (float) $validated['discount_value'] > 100) {
            return back()->withErrors(['discount_value' => __('Phần trăm không được vượt quá 100.')])->withInput();
        }

        PromoCode::query()->create([
            'code' => mb_strtoupper(trim($validated['code'])),
            'hotel_id' => $validated['hotel_id'],
            'room_type_id' => $validated['room_type_id'] ?? null,
            'valid_from' => $validated['valid_from'],
            'valid_to' => $validated['valid_to'],
            'discount_type' => $validated['discount_type'],
            'discount_value' => $validated['discount_value'],
            'max_uses' => $validated['max_uses'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('host.promo-codes.index')->with('status', __('Đã tạo mã giảm giá.'));
    }

    public function edit(Request $request, PromoCode $promoCode): View
    {
        $this->authorizeHost($request, $promoCode);

        $hotels = Hotel::query()->where('host_id', $request->user()->id)->orderBy('name')->get(['id', 'name']);
        $roomTypes = RoomType::query()
            ->whereIn('hotel_id', $hotels->pluck('id'))
            ->orderBy('name')
            ->get(['id', 'hotel_id', 'name']);

        return view('host.promo-codes.edit', compact('promoCode', 'hotels', 'roomTypes'));
    }

    public function update(Request $request, PromoCode $promoCode): RedirectResponse
    {
        $this->authorizeHost($request, $promoCode);

        $hotelIds = Hotel::query()->where('host_id', $request->user()->id)->pluck('id')->all();

        $validated = $request->validate([
            'hotel_id' => ['required', 'integer', Rule::in($hotelIds)],
            'room_type_id' => ['nullable', 'integer'],
            'code' => ['required', 'string', 'max:40', Rule::unique('promo_codes', 'code')->ignore($promoCode->id)],
            'valid_from' => ['required', 'date'],
            'valid_to' => ['required', 'date', 'after_or_equal:valid_from'],
            'discount_type' => ['required', Rule::in(['percent', 'fixed'])],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if (! empty($validated['room_type_id'])) {
            RoomType::query()->where('id', $validated['room_type_id'])->where('hotel_id', $validated['hotel_id'])->firstOrFail();
        }

        if ($validated['discount_type'] === 'percent' && (float) $validated['discount_value'] > 100) {
            return back()->withErrors(['discount_value' => __('Phần trăm không được vượt quá 100.')])->withInput();
        }

        $promoCode->forceFill([
            'code' => mb_strtoupper(trim($validated['code'])),
            'hotel_id' => $validated['hotel_id'],
            'room_type_id' => $validated['room_type_id'] ?? null,
            'valid_from' => $validated['valid_from'],
            'valid_to' => $validated['valid_to'],
            'discount_type' => $validated['discount_type'],
            'discount_value' => $validated['discount_value'],
            'max_uses' => $validated['max_uses'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ])->save();

        return redirect()->route('host.promo-codes.index')->with('status', __('Đã cập nhật mã giảm giá.'));
    }

    public function destroy(Request $request, PromoCode $promoCode): RedirectResponse
    {
        $this->authorizeHost($request, $promoCode);
        $promoCode->delete();

        return redirect()->route('host.promo-codes.index')->with('status', __('Đã xoá mã giảm giá.'));
    }

    private function authorizeHost(Request $request, PromoCode $promoCode): void
    {
        $owns = Hotel::query()->where('host_id', $request->user()->id)->where('id', $promoCode->hotel_id)->exists();
        abort_unless($owns, 403);
    }
}
