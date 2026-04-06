@props([
    'action',
    'method' => 'POST',
    'hotel' => null,
    'provinces' => collect(),
    'amenities' => collect(),
    'submitLabel' => __('Lưu khách sạn'),
])

@php
    $selectedAmenityIds = collect(old('amenity_ids', $hotel?->relationLoaded('amenities') ? $hotel->amenities->pluck('id')->all() : []))
        ->map(fn ($id) => (int) $id)
        ->all();
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="grid gap-5 md:grid-cols-2">
        <div class="md:col-span-2">
            <x-input-label for="name" :value="__('Tên khách sạn')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                :value="old('name', $hotel?->name)" required />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="province_code" :value="__('Tỉnh/Thành phố')" />
            <select id="province_code" name="province_code" required
                class="mt-1 block w-full px-4 py-2.5 border border-gray-200 rounded-xl bg-white focus:border-bcom-blue focus:ring-2 focus:ring-bcom-blue/20">
                <option value="">{{ __('-- Chọn Tỉnh/Thành phố --') }}</option>
                @foreach ($provinces as $province)
                    <option value="{{ $province->code }}"
                        @selected(old('province_code', $hotel?->province_code) === $province->code)>
                        {{ $province->type }} {{ $province->name }}
                    </option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('province_code')" />
        </div>

        <div>
            <x-input-label for="address" :value="__('Địa chỉ')" />
            <x-text-input id="address" name="address" type="text" class="mt-1 block w-full"
                :value="old('address', $hotel?->address)" required />
            <x-input-error class="mt-2" :messages="$errors->get('address')" />
        </div>

        <div>
            <x-input-label for="old_price" :value="__('Giá cũ (VND)')" />
            <x-text-input id="old_price" name="old_price" type="number" min="0" step="1000" class="mt-1 block w-full"
                :value="old('old_price', $hotel?->old_price)" />
            <x-input-error class="mt-2" :messages="$errors->get('old_price')" />
        </div>

        <div>
            <x-input-label for="new_price" :value="__('Giá mới (VND)')" />
            <x-text-input id="new_price" name="new_price" type="number" min="0" step="1000" class="mt-1 block w-full"
                :value="old('new_price', $hotel?->new_price ?? $hotel?->base_price ?? 0)" required />
            <x-input-error class="mt-2" :messages="$errors->get('new_price')" />
        </div>

        <div class="md:col-span-2">
            <x-input-label for="thumbnail" :value="__('Ảnh đại diện')" />
            <input id="thumbnail" name="thumbnail" type="file" accept=".jpg,.jpeg,.png,.webp"
                class="mt-1 block w-full px-4 py-2.5 border border-gray-200 rounded-xl bg-white" />
            @if ($hotel?->thumbnail)
                <p class="mt-2 text-xs text-gray-500">{{ __('Ảnh hiện tại') }}</p>
                <img src="{{ $hotel->thumbnailUrl() }}" alt="{{ $hotel->name }}" class="mt-2 h-24 w-36 object-cover rounded-lg border border-slate-200">
            @endif
            <x-input-error class="mt-2" :messages="$errors->get('thumbnail')" />
        </div>

        <div class="md:col-span-2">
            <x-input-label for="gallery_images" :value="__('Ảnh phụ (thư viện)')" />
            <p class="mt-1 text-xs text-gray-500">
                {{ __('Có thể chọn nhiều ảnh mỗi lần lưu; tổng số ảnh phụ không giới hạn.') }}
            </p>
            <input id="gallery_images" name="gallery_images[]" type="file" accept=".jpg,.jpeg,.png,.webp" multiple
                class="mt-2 block w-full px-4 py-2.5 border border-gray-200 rounded-xl bg-white" />
            <x-input-error class="mt-2" :messages="$errors->get('gallery_images')" />
            <x-input-error class="mt-2" :messages="$errors->get('gallery_images.*')" />

            @if ($hotel?->relationLoaded('galleryImages') && $hotel->galleryImages->isNotEmpty())
                <p class="mt-4 text-xs font-medium text-gray-700">{{ __('Ảnh phụ hiện có — đánh dấu để xóa khi lưu') }}</p>
                <div class="mt-2 flex flex-wrap gap-3">
                    @foreach ($hotel->galleryImages as $gimg)
                        <label class="flex flex-col gap-1 rounded-lg border border-gray-200 bg-white p-2 text-xs text-gray-600 hover:border-slate-200">
                            <img src="{{ $gimg->url() }}" alt="" class="h-20 w-28 object-cover rounded-md border border-gray-100">
                            <span class="inline-flex items-center gap-1.5">
                                <input type="checkbox" name="remove_gallery_image_ids[]" value="{{ $gimg->id }}"
                                    class="rounded border-gray-300 text-bcom-blue focus:ring-bcom-blue">
                                {{ __('Xóa') }}
                            </span>
                        </label>
                    @endforeach
                </div>
                <x-input-error class="mt-2" :messages="$errors->get('remove_gallery_image_ids')" />
            @endif
        </div>

        <div class="md:col-span-2">
            <x-input-label for="description" :value="__('Mô tả')" />
            <textarea id="description" name="description" rows="5"
                class="mt-1 block w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:border-bcom-blue focus:ring-2 focus:ring-bcom-blue/20">{{ old('description', $hotel?->description) }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('description')" />
        </div>

        <div class="md:col-span-2">
            <x-input-label :value="__('Tiện nghi')" />
            <p class="mt-1 text-xs text-gray-500">{{ __('Chọn các tiện nghi khách sạn có (nếu có).') }}</p>
            <div class="mt-3 grid gap-2 sm:grid-cols-2">
                @foreach ($amenities as $amenity)
                    <label class="flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-800 hover:border-slate-200 has-[:checked]:border-sky-300 has-[:checked]:bg-sky-50/50">
                        <input type="checkbox" name="amenity_ids[]" value="{{ $amenity->id }}"
                            class="rounded border-gray-300 text-bcom-blue focus:ring-bcom-blue"
                            @checked(in_array($amenity->id, $selectedAmenityIds, true)) />
                        <span>{{ $amenity->name }}</span>
                    </label>
                @endforeach
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('amenity_ids')" />
        </div>
    </div>

    <label class="inline-flex items-center gap-2">
        <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-bcom-blue focus:ring-bcom-blue"
            @checked(old('is_active', $hotel?->is_active ?? true)) />
        <span class="text-sm text-gray-700">{{ __('Đang hoạt động và hiển thị để đặt phòng') }}</span>
    </label>

    <div class="flex items-center gap-3">
        <x-primary-button>{{ $submitLabel }}</x-primary-button>
        <a href="{{ route('host.hotels.index') }}" class="text-sm text-gray-600 hover:text-bcom-blue">{{ __('Quay lại danh sách') }}</a>
    </div>
</form>

