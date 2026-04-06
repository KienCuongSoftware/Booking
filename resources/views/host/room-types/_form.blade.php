@props([
    'hotel',
    'roomType' => null,
    'action',
    'method' => 'POST',
    'submitLabel' => __('Lưu'),
    'bedLinesInit' => [],
    'amenitySections' => null,
    'amenityCategoryLabels' => [],
])

@php
    $bedLinesInit = count($bedLinesInit) > 0 ? $bedLinesInit : [['area_name' => '', 'bed_summary' => '']];
    $amenitySections = $amenitySections ?? collect();
    $selectedRoomAmenityIds = collect(old('room_amenity_ids', $roomType?->relationLoaded('amenities') ? $roomType->amenities->pluck('id')->all() : []))
        ->map(fn ($id) => (int) $id)
        ->all();
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <p class="text-sm text-gray-600">
        {{ __('Khách sạn:') }} <span class="font-semibold text-bcom-navy">{{ $hotel->name }}</span>
    </p>

    <div class="grid gap-5 md:grid-cols-2">
        <div class="md:col-span-2">
            <x-input-label for="name" :value="__('Tên loại phòng')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                :value="old('name', $roomType?->name)" required />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div class="md:col-span-2">
            <x-input-label for="room_images" :value="__('Ảnh loại phòng')" />
            <p class="mt-1 text-xs text-gray-500">
                {{ __('Chỉ là danh sách ảnh (không phân ảnh chính/phụ). Có thể thêm nhiều ảnh mỗi lần lưu; tổng số không giới hạn.') }}
            </p>
            <input id="room_images" name="room_images[]" type="file" accept=".jpg,.jpeg,.png,.webp" multiple
                class="mt-2 block w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5" />
            <x-input-error class="mt-2" :messages="$errors->get('room_images')" />
            <x-input-error class="mt-2" :messages="$errors->get('room_images.*')" />

            @if ($roomType?->relationLoaded('images') && $roomType->images->isNotEmpty())
                <p class="mt-4 text-xs font-medium text-gray-700">{{ __('Ảnh hiện có — đánh dấu để xóa khi lưu') }}</p>
                <div class="mt-2 flex flex-wrap gap-3">
                    @foreach ($roomType->images as $rimg)
                        <label class="flex flex-col gap-1 rounded-lg border border-gray-200 bg-white p-2 text-xs text-gray-600 hover:border-slate-200">
                            <img src="{{ $rimg->url() }}" alt="" class="h-20 w-28 object-cover rounded-md border border-gray-100">
                            <span class="inline-flex items-center gap-1.5">
                                <input type="checkbox" name="remove_room_image_ids[]" value="{{ $rimg->id }}"
                                    class="rounded border-gray-300 text-bcom-blue focus:ring-bcom-blue">
                                {{ __('Xóa') }}
                            </span>
                        </label>
                    @endforeach
                </div>
                <x-input-error class="mt-2" :messages="$errors->get('remove_room_image_ids')" />
            @endif
        </div>

        <div>
            <x-input-label for="max_occupancy" :value="__('Số khách tối đa')" />
            <x-text-input id="max_occupancy" name="max_occupancy" type="number" min="1" max="30" class="mt-1 block w-full"
                :value="old('max_occupancy', $roomType?->max_occupancy ?? 2)" required />
            <x-input-error class="mt-2" :messages="$errors->get('max_occupancy')" />
        </div>

        <div>
            <x-input-label for="quantity" :value="__('Số phòng (tồn kho loại này)')" />
            <x-text-input id="quantity" name="quantity" type="number" min="1" max="9999" class="mt-1 block w-full"
                :value="old('quantity', $roomType?->quantity ?? 1)" required />
            <x-input-error class="mt-2" :messages="$errors->get('quantity')" />
        </div>

        <div>
            <x-input-label for="area_sqm" :value="__('Diện tích (m²)')" />
            <x-text-input id="area_sqm" name="area_sqm" type="number" min="0.01" max="999999.99" step="0.01" class="mt-1 block w-full"
                :value="old('area_sqm', $roomType?->area_sqm)" required />
            <p class="mt-1 text-xs text-gray-500">{{ __('Diện tích sàn một phòng thuộc loại này (mét vuông).') }}</p>
            <x-input-error class="mt-2" :messages="$errors->get('area_sqm')" />
        </div>

        <div>
            <x-input-label for="old_price" :value="__('Giá cũ (VND, tuỳ chọn)')" />
            <x-text-input id="old_price" name="old_price" type="number" min="0" step="1000" class="mt-1 block w-full"
                :value="old('old_price', $roomType?->old_price)" />
            <x-input-error class="mt-2" :messages="$errors->get('old_price')" />
        </div>

        <div>
            <x-input-label for="new_price" :value="__('Giá hiện tại / đêm (VND)')" />
            <x-text-input id="new_price" name="new_price" type="number" min="0" step="1000" class="mt-1 block w-full"
                :value="old('new_price', $roomType?->new_price ?? $roomType?->base_price ?? 0)" required />
            <x-input-error class="mt-2" :messages="$errors->get('new_price')" />
        </div>

        <div class="flex items-end pb-1">
            <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-bcom-blue focus:ring-bcom-blue"
                    @checked(old('is_active', $roomType?->is_active ?? true)) />
                <span class="text-sm text-gray-700">{{ __('Đang mở bán / hiển thị') }}</span>
            </label>
        </div>

        @if ($amenitySections->isNotEmpty())
            <div class="md:col-span-2 space-y-6 rounded-xl border border-slate-200 bg-sky-50/30 p-5">
                <div>
                    <x-input-label :value="__('Tiện nghi trong phòng')" />
                    <p class="mt-1 text-xs text-gray-600">{{ __('Chọn tiện nghi có trong loại phòng này (danh mục riêng với tiện nghi khách sạn).') }}</p>
                </div>
                @foreach ($amenitySections as $categoryKey => $amenities)
                    <div>
                        <p class="text-sm font-semibold text-gray-900">
                            {{ $amenityCategoryLabels[$categoryKey] ?? __('Khác') }}
                        </p>
                        <div class="mt-2 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($amenities as $amenity)
                                <label class="flex items-start gap-2 rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-800 hover:border-slate-200 has-[:checked]:border-sky-300 has-[:checked]:bg-sky-50/50">
                                    <input type="checkbox" name="room_amenity_ids[]" value="{{ $amenity->id }}"
                                        class="mt-0.5 rounded border-gray-300 text-bcom-blue focus:ring-bcom-blue"
                                        @checked(in_array($amenity->id, $selectedRoomAmenityIds, true)) />
                                    <span>{{ $amenity->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
                <x-input-error class="mt-2" :messages="$errors->get('room_amenity_ids')" />
            </div>
        @endif

        <div class="md:col-span-2 rounded-xl border border-slate-200 bg-sky-50/40 p-4">
            <x-input-label :value="__('Cấu hình giường')" />
            <p class="mt-1 text-xs text-gray-600">{{ __('Mỗi dòng: khu vực (tuỳ chọn) + mô tả giường. Ví dụ: Phòng ngủ 1 — 1 giường đôi.') }}</p>

            <div class="mt-3 space-y-2" x-data="{ lines: @js($bedLinesInit) }">
                <template x-for="(line, index) in lines" :key="index">
                    {{-- Grid: 3 cột tách biệt — tránh nút “Xóa dòng” chồng lên ô giường (flex-1) --}}
                    <div class="grid grid-cols-1 gap-2 rounded-lg border border-slate-200 bg-white p-3 sm:grid-cols-[minmax(0,11rem)_minmax(0,1fr)_auto] sm:items-center sm:gap-3">
                        <input type="text"
                            class="block w-full min-w-0 rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-bcom-blue focus:outline-none focus:ring-2 focus:ring-bcom-blue/20 [field-sizing:fixed]"
                            :name="'bed_lines[' + index + '][area_name]'" x-model="line.area_name"
                            placeholder="{{ __('Khu vực (tuỳ chọn)') }}">
                        <input type="text"
                            class="block w-full min-w-0 rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-bcom-blue focus:outline-none focus:ring-2 focus:ring-bcom-blue/20 [field-sizing:fixed]"
                            :name="'bed_lines[' + index + '][bed_summary]'" x-model="line.bed_summary"
                            placeholder="{{ __('Giường (vd: 1 giường đôi)') }}">
                        <button type="button"
                            class="justify-self-start text-sm text-bcom-blue hover:underline sm:justify-self-end sm:whitespace-nowrap sm:px-1"
                            @click="lines.splice(index, 1); if (lines.length === 0) lines.push({ area_name: '', bed_summary: '' })">
                            {{ __('Xóa dòng') }}
                        </button>
                    </div>
                </template>
                <button type="button" class="text-sm font-medium text-bcom-blue hover:text-bcom-navy"
                    @click="lines.push({ area_name: '', bed_summary: '' })">
                    + {{ __('Thêm dòng giường') }}
                </button>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('bed_lines')" />
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-3">
        <x-primary-button>{{ $submitLabel }}</x-primary-button>
        <a href="{{ route('host.rooms.index', ['hotel_id' => $hotel->id]) }}" class="text-sm text-gray-600 hover:text-bcom-blue">{{ __('Quay lại danh sách phòng') }}</a>
    </div>
</form>
