<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Http\Requests\Host\StoreHotelRequest;
use App\Http\Requests\Host\UpdateHotelRequest;
use App\Models\Amenity;
use App\Models\Hotel;
use App\Models\Province;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class HotelController extends Controller
{
    public function index(): View
    {
        $hotels = Hotel::query()
            ->where('host_id', Auth::id())
            ->with('province')
            ->latest()
            ->paginate(5);

        return view('host.hotels.index', compact('hotels'));
    }

    public function create(): View
    {
        $provinces = Province::query()
            ->orderBy('name')
            ->get(['code', 'name', 'type']);
        $amenities = Amenity::query()->orderBy('sort_order')->orderBy('name')->get();

        return view('host.hotels.create', compact('provinces', 'amenities'));
    }

    public function store(StoreHotelRequest $request): RedirectResponse
    {
        $hostId = (int) $request->user()->getAuthIdentifier();
        $data = $request->validated();
        $amenityIds = $data['amenity_ids'] ?? [];
        unset($data['amenity_ids'], $data['gallery_images']);

        $province = Province::query()->where('code', $data['province_code'])->firstOrFail();
        $data['city'] = $province->name;

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('hotels', 'public');
        }
        $data['base_price'] = $data['new_price'] ?? 0;

        $hotel = Hotel::query()->create([
            ...$data,
            'host_id' => $hostId,
            'is_active' => $request->boolean('is_active', true),
        ]);

        $hotel->amenities()->sync($amenityIds);
        $this->appendUploadedHotelGallery($hotel, $request->file('gallery_images'));

        return redirect()
            ->route('host.hotels.index')
            ->with('status', __('Tạo khách sạn thành công.'));
    }

    public function edit(Hotel $hotel): View
    {
        $hostId = (int) Auth::id();
        abort_unless($hotel->host_id === $hostId, 403);

        $hotel->load(['amenities', 'galleryImages']);
        $provinces = Province::query()
            ->orderBy('name')
            ->get(['code', 'name', 'type']);
        $amenities = Amenity::query()->orderBy('sort_order')->orderBy('name')->get();

        return view('host.hotels.edit', compact('hotel', 'provinces', 'amenities'));
    }

    public function show(Hotel $hotel): View
    {
        $hostId = (int) Auth::id();
        abort_unless($hotel->host_id === $hostId, 403);

        $hotel->load([
            'province',
            'amenities',
            'galleryImages',
            'roomTypes.bedLines',
            'roomTypes.amenities',
            'roomTypes.images',
        ]);

        return view('host.hotels.show', compact('hotel'));
    }

    public function update(UpdateHotelRequest $request, Hotel $hotel): RedirectResponse
    {
        $hostId = (int) Auth::id();
        abort_unless($hotel->host_id === $hostId, 403);

        $data = $request->validated();
        $amenityIds = $data['amenity_ids'] ?? [];
        $removeGalleryIds = array_values(array_filter($data['remove_gallery_image_ids'] ?? []));
        unset($data['amenity_ids'], $data['remove_gallery_image_ids'], $data['gallery_images']);

        $province = Province::query()->where('code', $data['province_code'])->firstOrFail();
        $data['city'] = $province->name;

        if ($request->hasFile('thumbnail')) {
            if ($hotel->thumbnail && ! str_starts_with($hotel->thumbnail, 'http')) {
                Storage::disk('public')->delete($hotel->thumbnail);
            }
            $data['thumbnail'] = $request->file('thumbnail')->store('hotels', 'public');
        }
        $data['base_price'] = $data['new_price'] ?? $hotel->base_price;

        $hotel->update([
            ...$data,
            'is_active' => $request->boolean('is_active', false),
        ]);

        $hotel->amenities()->sync($amenityIds);

        if ($removeGalleryIds !== []) {
            $hotel->galleryImages()->whereIn('id', $removeGalleryIds)->get()->each->delete();
        }
        $this->appendUploadedHotelGallery($hotel, $request->file('gallery_images'));

        return redirect()
            ->route('host.hotels.index')
            ->with('status', __('Cập nhật khách sạn thành công.'));
    }

    public function destroy(Hotel $hotel): RedirectResponse
    {
        $hostId = (int) Auth::id();
        abort_unless($hotel->host_id === $hostId, 403);
        $hotel->load('galleryImages');
        $hotel->galleryImages->each->delete();
        if ($hotel->thumbnail && ! str_starts_with($hotel->thumbnail, 'http')) {
            Storage::disk('public')->delete($hotel->thumbnail);
        }
        $hotel->delete();

        return redirect()
            ->route('host.hotels.index')
            ->with('status', __('Đã xóa khách sạn.'));
    }

    /**
     * @param  array<int, UploadedFile>|UploadedFile|null  $files
     */
    private function appendUploadedHotelGallery(Hotel $hotel, $files): void
    {
        $files = $this->normalizeUploadedFiles($files);
        if ($files === []) {
            return;
        }

        $next = (int) $hotel->galleryImages()->max('sort_order') + 1;
        foreach ($files as $file) {
            if (! $file->isValid()) {
                continue;
            }
            $path = $file->store('hotels/gallery', 'public');
            $hotel->galleryImages()->create([
                'path' => $path,
                'sort_order' => $next++,
            ]);
        }
    }

    /**
     * @param  array<int, UploadedFile>|UploadedFile|null  $files
     * @return list<UploadedFile>
     */
    private function normalizeUploadedFiles($files): array
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
