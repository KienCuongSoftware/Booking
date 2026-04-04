<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Http\Requests\Host\StoreRoomTypeRequest;
use App\Http\Requests\Host\UpdateRoomTypeRequest;
use App\Models\Hotel;
use App\Models\RoomAmenity;
use App\Models\RoomType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RoomTypeController extends Controller
{
    public function index(Request $request): View
    {
        $hostId = (int) Auth::id();
        $hotels = Hotel::query()
            ->where('host_id', $hostId)
            ->orderBy('name')
            ->get();

        $hotelId = $request->integer('hotel_id') ?: null;

        $roomTypes = RoomType::query()
            ->with(['hotel', 'bedLines', 'amenities', 'images'])
            ->whereHas('hotel', fn ($q) => $q->where('host_id', $hostId))
            ->when($hotelId, fn ($q) => $q->where('hotel_id', $hotelId))
            ->orderBy('hotel_id')
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(5)
            ->withQueryString();

        return view('host.room-types.index', compact('hotels', 'roomTypes', 'hotelId'));
    }

    public function create(Hotel $hotel): View
    {
        $this->assertHotelOwned($hotel);

        [$amenitySections, $amenityCategoryLabels] = $this->amenityDataForRoomForm();

        return view('host.room-types.create', compact('hotel', 'amenitySections', 'amenityCategoryLabels'));
    }

    public function store(StoreRoomTypeRequest $request, Hotel $hotel): RedirectResponse
    {
        $this->assertHotelOwned($hotel);

        $data = $request->validated();
        $bedLines = $data['bed_lines'] ?? [];
        $roomAmenityIds = $data['room_amenity_ids'] ?? [];
        unset($data['bed_lines'], $data['room_amenity_ids'], $data['room_images']);

        $data['base_price'] = $data['new_price'] ?? 0;
        $data['is_active'] = $request->boolean('is_active', true);

        $roomType = $hotel->roomTypes()->create($data);
        $this->syncBedLines($roomType, $bedLines);
        $roomType->amenities()->sync($roomAmenityIds);
        $this->appendUploadedRoomTypeImages($roomType, $request->file('room_images'));

        return redirect()
            ->route('host.rooms.index', ['hotel_id' => $hotel->id])
            ->with('status', __('Đã tạo loại phòng.'));
    }

    public function edit(RoomType $roomType): View
    {
        $this->assertRoomTypeOwned($roomType);
        $roomType->load(['bedLines', 'amenities', 'images']);
        $hotel = $roomType->hotel;
        [$amenitySections, $amenityCategoryLabels] = $this->amenityDataForRoomForm();

        return view('host.room-types.edit', compact('hotel', 'roomType', 'amenitySections', 'amenityCategoryLabels'));
    }

    public function update(UpdateRoomTypeRequest $request, RoomType $roomType): RedirectResponse
    {
        $this->assertRoomTypeOwned($roomType);

        $data = $request->validated();
        $bedLines = $data['bed_lines'] ?? [];
        $roomAmenityIds = $data['room_amenity_ids'] ?? [];
        $removeRoomImageIds = array_values(array_filter($data['remove_room_image_ids'] ?? []));
        unset($data['bed_lines'], $data['room_amenity_ids'], $data['room_images'], $data['remove_room_image_ids']);

        $data['base_price'] = $data['new_price'] ?? $roomType->base_price;
        $data['is_active'] = $request->boolean('is_active', false);

        $roomType->update($data);
        $this->syncBedLines($roomType, $bedLines);
        $roomType->amenities()->sync($roomAmenityIds);

        if ($removeRoomImageIds !== []) {
            $roomType->images()->whereIn('id', $removeRoomImageIds)->get()->each->delete();
        }
        $this->appendUploadedRoomTypeImages($roomType, $request->file('room_images'));

        return redirect()
            ->route('host.rooms.index', ['hotel_id' => $roomType->hotel_id])
            ->with('status', __('Đã cập nhật loại phòng.'));
    }

    public function destroy(RoomType $roomType): RedirectResponse
    {
        $this->assertRoomTypeOwned($roomType);
        $hotelId = $roomType->hotel_id;
        $roomType->load('images');
        $roomType->images->each->delete();
        $roomType->delete();

        return redirect()
            ->route('host.rooms.index', ['hotel_id' => $hotelId])
            ->with('status', __('Đã xóa loại phòng.'));
    }

    /**
     * @return array{0: Collection<string, Collection<int, RoomAmenity>>, 1: array<string, string>}
     */
    private function amenityDataForRoomForm(): array
    {
        $labels = [
            'phong_tam' => __('Trong phòng tắm riêng của bạn'),
            'tien_nghi_phong' => __('Tiện nghi'),
            'chinh_sach' => __('Chính sách'),
            'chung' => __('Tiện nghi chung'),
        ];

        $all = RoomAmenity::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $grouped = $all->groupBy(fn (RoomAmenity $a) => $a->category_key ?: 'chung');

        $order = ['phong_tam', 'tien_nghi_phong', 'chung', 'chinh_sach'];
        $sections = collect();
        foreach ($order as $key) {
            if ($grouped->has($key)) {
                $sections->put($key, $grouped->get($key));
            }
        }
        foreach ($grouped as $key => $items) {
            if (! $sections->has($key)) {
                $sections->put($key, $items);
            }
        }

        return [$sections, $labels];
    }

    private function assertHotelOwned(Hotel $hotel): void
    {
        abort_unless($hotel->host_id === (int) Auth::id(), 403);
    }

    private function assertRoomTypeOwned(RoomType $roomType): void
    {
        abort_unless($roomType->hotel->host_id === (int) Auth::id(), 403);
    }

    /**
     * @param  array<int, array{area_name?: string|null, bed_summary?: string|null}>  $rows
     */
    private function syncBedLines(RoomType $roomType, array $rows): void
    {
        $roomType->bedLines()->delete();
        $filtered = collect($rows)
            ->filter(fn ($r) => filled($r['bed_summary'] ?? null))
            ->values();

        foreach ($filtered as $i => $row) {
            $roomType->bedLines()->create([
                'sort_order' => $i,
                'area_name' => filled($row['area_name'] ?? null) ? $row['area_name'] : null,
                'bed_summary' => $row['bed_summary'],
            ]);
        }
    }

    /**
     * @param  array<int, UploadedFile>|UploadedFile|null  $files
     */
    private function appendUploadedRoomTypeImages(RoomType $roomType, $files): void
    {
        $files = $this->normalizeUploadedRoomImages($files);
        if ($files === []) {
            return;
        }

        $next = (int) $roomType->images()->max('sort_order') + 1;
        foreach ($files as $file) {
            if (! $file->isValid()) {
                continue;
            }
            $path = $file->store('room-types', 'public');
            $roomType->images()->create([
                'path' => $path,
                'sort_order' => $next++,
            ]);
        }
    }

    /**
     * @param  array<int, UploadedFile>|UploadedFile|null  $files
     * @return list<UploadedFile>
     */
    private function normalizeUploadedRoomImages($files): array
    {
        if ($files === null) {
            return [];
        }

        if (! is_array($files)) {
            return array_values(array_filter([$files], fn ($f) => $f instanceof UploadedFile));
        }

        return array_values(array_filter($files, fn ($f) => $f instanceof UploadedFile));
    }
}
