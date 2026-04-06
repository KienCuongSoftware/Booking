<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-bcom-navy leading-tight">
            {{ __('Chi tiết khách sạn') }}
        </h2>
    </x-slot>

    <div class="py-10 px-4 sm:px-6 lg:px-8">
        <div class="max-w-5xl mx-auto space-y-6">
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-md shadow-slate-900/5">
                <div class="space-y-5 p-6 sm:p-8">
                    @php
                        $gallery = $hotel->galleryImages;
                    @endphp

                    <div class="min-w-0">
                        <h3 class="text-2xl font-semibold text-gray-900">{{ $hotel->name }}</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ $hotel->province ? $hotel->province->type.' '.$hotel->province->name : $hotel->city }}
                            — {{ $hotel->address }}
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-4 text-sm">
                        @if ($hotel->old_price)
                            <p class="text-gray-500">
                                <span class="line-through">{{ number_format((float) $hotel->old_price, 0, ',', '.') }} VND</span>
                            </p>
                        @endif
                        <p class="font-medium text-bcom-blue">
                            {{ number_format((float) ($hotel->new_price ?? $hotel->base_price), 0, ',', '.') }} VND / {{ __('đêm') }}
                        </p>
                        <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs {{ $hotel->is_active ? 'border-green-200 bg-green-50 text-green-800' : 'border-gray-200 bg-gray-50 text-gray-700' }}">
                            {{ $hotel->is_active ? __('Đang hoạt động') : __('Tạm ẩn') }}
                        </span>
                    </div>

                    @if ($hotel->amenities->isNotEmpty())
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ __('Tiện nghi') }}</p>
                            <ul class="mt-2 flex flex-wrap gap-2">
                                @foreach ($hotel->amenities as $amenity)
                                    @switch($loop->index % 10)
                                        @case(0)
                                            <li class="inline-flex rounded-lg border-2 border-blue-500 bg-blue-100 px-2.5 py-1 text-xs font-medium text-blue-950">{{ $amenity->name }}</li>
                                            @break
                                        @case(1)
                                            <li class="inline-flex rounded-lg border-2 border-bcom-blue bg-sky-100 px-2.5 py-1 text-xs font-medium text-bcom-navy">{{ $amenity->name }}</li>
                                            @break
                                        @case(2)
                                            <li class="inline-flex rounded-lg border-2 border-emerald-500 bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-950">{{ $amenity->name }}</li>
                                            @break
                                        @case(3)
                                            <li class="inline-flex rounded-lg border-2 border-amber-500 bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-950">{{ $amenity->name }}</li>
                                            @break
                                        @case(4)
                                            <li class="inline-flex rounded-lg border-2 border-violet-500 bg-violet-100 px-2.5 py-1 text-xs font-medium text-violet-950">{{ $amenity->name }}</li>
                                            @break
                                        @case(5)
                                            <li class="inline-flex rounded-lg border-2 border-cyan-500 bg-cyan-100 px-2.5 py-1 text-xs font-medium text-cyan-950">{{ $amenity->name }}</li>
                                            @break
                                        @case(6)
                                            <li class="inline-flex rounded-lg border-2 border-orange-500 bg-orange-100 px-2.5 py-1 text-xs font-medium text-orange-950">{{ $amenity->name }}</li>
                                            @break
                                        @case(7)
                                            <li class="inline-flex rounded-lg border-2 border-pink-500 bg-pink-100 px-2.5 py-1 text-xs font-medium text-pink-950">{{ $amenity->name }}</li>
                                            @break
                                        @case(8)
                                            <li class="inline-flex rounded-lg border-2 border-indigo-500 bg-indigo-100 px-2.5 py-1 text-xs font-medium text-indigo-950">{{ $amenity->name }}</li>
                                            @break
                                        @case(9)
                                            <li class="inline-flex rounded-lg border-2 border-lime-500 bg-lime-100 px-2.5 py-1 text-xs font-medium text-lime-950">{{ $amenity->name }}</li>
                                            @break
                                    @endswitch
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ __('Hình ảnh khách sạn') }}</p>
                        <p class="mt-1 text-xs text-gray-500">{{ __('Ảnh đại diện và thư viện; khung cố định, ảnh được cắt gọn theo tỷ lệ — không kéo méo.') }}</p>

                        @if ($gallery->isEmpty())
                            <div class="mt-3 overflow-hidden rounded-xl border border-slate-200 bg-gray-100 shadow-sm">
                                <a href="{{ $hotel->thumbnailUrl() }}" target="_blank" rel="noopener noreferrer" class="block">
                                    <img src="{{ $hotel->thumbnailUrl() }}" alt="{{ $hotel->name }}"
                                        class="aspect-video w-full object-cover object-center sm:max-h-80"
                                        loading="eager" decoding="async">
                                </a>
                            </div>
                        @else
                            {{-- Ảnh trong luồng + aspect-ratio: tránh khung cao 0 khi chỉ có position:absolute --}}
                            <div class="mt-3 flex flex-col gap-2 lg:flex-row lg:items-start lg:gap-2">
                                <a href="{{ $hotel->thumbnailUrl() }}" target="_blank" rel="noopener noreferrer"
                                    class="block w-full overflow-hidden rounded-xl border border-slate-200 bg-gray-100 shadow-sm lg:w-[61.5%] lg:shrink-0">
                                    <img src="{{ $hotel->thumbnailUrl() }}" alt="{{ $hotel->name }}"
                                        class="aspect-[4/3] w-full object-cover object-center lg:aspect-[16/10]"
                                        loading="eager" decoding="async">
                                </a>
                                <div class="flex w-full flex-col gap-2 lg:w-[38.5%]">
                                    @foreach ($gallery->take(2) as $gimg)
                                        <a href="{{ $gimg->url() }}" target="_blank" rel="noopener noreferrer"
                                            class="block overflow-hidden rounded-xl border border-slate-200 bg-gray-100 shadow-sm">
                                            <img src="{{ $gimg->url() }}" alt=""
                                                class="aspect-video w-full object-cover object-center"
                                                loading="lazy" decoding="async">
                                        </a>
                                    @endforeach
                                </div>
                            </div>

                            @if ($gallery->count() > 2)
                                <div class="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-4">
                                    @foreach ($gallery->slice(2) as $gimg)
                                        <a href="{{ $gimg->url() }}" target="_blank" rel="noopener noreferrer"
                                            class="block overflow-hidden rounded-lg border border-slate-200 bg-gray-100 shadow-sm">
                                            <img src="{{ $gimg->url() }}" alt=""
                                                class="aspect-[4/3] w-full object-cover object-center"
                                                loading="lazy" decoding="async">
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        @endif
                    </div>

                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ __('Mô tả') }}</p>
                        <p class="mt-2 whitespace-pre-line text-sm text-gray-700">{{ $hotel->description ?: __('Chưa có mô tả.') }}</p>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-md shadow-slate-900/5">
                <div class="border-b border-slate-200 bg-sky-50/60 px-6 py-4">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <h3 class="text-lg font-semibold text-bcom-navy">{{ __('Phòng trống') }}</h3>
                        <a href="{{ route('host.rooms.index', ['hotel_id' => $hotel->id]) }}" class="text-sm font-medium text-bcom-blue hover:text-bcom-navy">{{ __('Quản lý loại phòng') }}</a>
                    </div>
                    <p class="mt-1 text-xs text-amber-800">{{ __('Khách chọn ngày nhận/trả phòng sẽ thấy giá và số phòng còn — phần đặt chỗ sẽ bổ sung sau.') }}</p>
                </div>
                @if ($hotel->roomTypes->isEmpty())
                    <div class="p-8 text-center text-sm text-gray-600">
                        {{ __('Chưa có loại phòng.') }}
                        <a href="{{ route('host.hotels.room-types.create', $hotel) }}" class="ml-1 font-medium text-bcom-blue hover:text-bcom-navy">{{ __('Thêm loại phòng') }}</a>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[720px] border-collapse text-left text-sm">
                            <thead class="border-b border-slate-200 bg-white text-xs font-semibold uppercase tracking-wide text-bcom-navy">
                                <tr>
                                    <th class="px-4 py-3">{{ __('Loại chỗ ở') }}</th>
                                    <th class="px-4 py-3">{{ __('Diện tích (m²)') }}</th>
                                    <th class="px-4 py-3">{{ __('Số khách') }}</th>
                                    <th class="px-4 py-3">{{ __('Giá / đêm') }}</th>
                                    <th class="px-4 py-3">{{ __('Còn') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($hotel->roomTypes->where('is_active', true) as $rt)
                                    <tr class="align-top hover:bg-sky-50/40">
                                        <td class="px-4 py-4">
                                            <div class="flex gap-3">
                                                @php $firstRoomImg = $rt->images->first(); @endphp
                                                @if ($firstRoomImg)
                                                    <div class="relative isolate h-16 w-[4.5rem] shrink-0 overflow-hidden rounded-md border border-slate-200 bg-gray-100">
                                                        <img src="{{ $firstRoomImg->url() }}" alt=""
                                                            class="absolute inset-0 h-full w-full object-cover object-center">
                                                    </div>
                                                @endif
                                                <div class="min-w-0">
                                            <p class="font-semibold text-blue-700">{{ $rt->name }}</p>
                                            @if ($rt->bedLines->isNotEmpty())
                                                <ul class="mt-2 space-y-1 text-xs text-gray-700">
                                                    @foreach ($rt->bedLines as $line)
                                                        <li class="flex items-start gap-1.5">
                                                            <x-icon.bed class="mt-0.5 h-3.5 w-3.5 shrink-0 text-bcom-blue" />
                                                            <span>
                                                                @if ($line->area_name)
                                                                    <span class="font-medium">{{ $line->area_name }}:</span>
                                                                @endif
                                                                {{ $line->bed_summary }}
                                                            </span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                            @if ($rt->amenities->isNotEmpty())
                                                <ul class="mt-2 flex flex-wrap gap-1.5 text-xs text-gray-700">
                                                    @foreach ($rt->amenities as $am)
                                                        <li class="inline-flex items-center gap-1 rounded-md border border-gray-200 bg-gray-50/80 px-2 py-0.5">
                                                            <x-icon.check class="text-emerald-600" />
                                                            {{ $am->name }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-gray-800">
                                            @if ($rt->area_sqm !== null)
                                                {{ rtrim(rtrim(number_format((float) $rt->area_sqm, 2, ',', '.'), '0'), ',') }} m²
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="flex flex-wrap gap-0.5">
                                                @for ($i = 0; $i < min($rt->max_occupancy, 8); $i++)
                                                    <x-icon.user class="h-4 w-4 text-sky-500" />
                                                @endfor
                                                @if ($rt->max_occupancy > 8)
                                                    <span class="text-xs text-gray-600">+{{ $rt->max_occupancy - 8 }}</span>
                                                @endif
                                            </div>
                                            <p class="mt-1 text-xs text-gray-500">{{ __('Tối đa') }} {{ $rt->max_occupancy }} {{ __('người') }}</p>
                                        </td>
                                        <td class="px-4 py-4">
                                            @if ($rt->old_price)
                                                <p class="text-xs text-gray-500 line-through">{{ number_format((float) $rt->old_price, 0, ',', '.') }} VND</p>
                                            @endif
                                            <p class="font-medium text-bcom-blue">{{ number_format((float) ($rt->new_price ?? $rt->base_price), 0, ',', '.') }} VND</p>
                                        </td>
                                        <td class="px-4 py-4 text-gray-800">{{ $rt->quantity }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if ($hotel->roomTypes->where('is_active', true)->isEmpty() && $hotel->roomTypes->isNotEmpty())
                        <p class="border-t border-slate-100 px-4 py-3 text-xs text-gray-600">{{ __('Tất cả loại phòng đang tạm ẩn.') }}</p>
                    @endif
                @endif
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('host.hotels.index') }}" class="text-sm text-gray-600 hover:text-bcom-blue">{{ __('Quay lại danh sách') }}</a>
                <a href="{{ route('host.hotels.edit', $hotel) }}" class="inline-flex items-center rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-bcom-blue hover:bg-sky-50">
                    {{ __('Chỉnh sửa') }}
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
