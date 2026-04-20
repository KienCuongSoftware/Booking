<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-bcom-navy">{{ $hotel->name }}</h2>
    </x-slot>

    @php
        $gallery = $hotel->galleryImages;
    @endphp

    <div class="py-8 px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-5xl space-y-6">
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="space-y-5 p-6 sm:p-8">
                    <div class="min-w-0">
                        <h3 class="text-2xl font-semibold text-gray-900">{{ $hotel->name }}</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ $hotel->province ? $hotel->province->type.' '.$hotel->province->name : $hotel->city }}
                            — {{ $hotel->address }}
                        </p>
                    </div>

                    <dl class="grid gap-3 text-sm sm:grid-cols-2">
                        <div>
                            <dt class="font-semibold text-bcom-navy">{{ __('Chủ khách sạn') }}</dt>
                            <dd class="mt-0.5 text-gray-700">{{ $hotel->host?->name }} <span class="text-gray-500">({{ $hotel->host?->email }})</span></dd>
                        </div>
                        <div>
                            <dt class="font-semibold text-bcom-navy">{{ __('Định danh URL') }}</dt>
                            <dd class="mt-0.5 font-mono text-xs text-gray-700">{{ $hotel->slug }}</dd>
                        </div>
                        <div>
                            <dt class="font-semibold text-bcom-navy">{{ __('Tỉnh / Thành (mã)') }}</dt>
                            <dd class="mt-0.5 text-gray-700">{{ $hotel->province_code ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="font-semibold text-bcom-navy">{{ __('Thành phố (trường city)') }}</dt>
                            <dd class="mt-0.5 text-gray-700">{{ $hotel->city }}</dd>
                        </div>
                        <div>
                            <dt class="font-semibold text-bcom-navy">{{ __('Hạng sao') }}</dt>
                            <dd class="mt-0.5 text-gray-700">{{ $hotel->star_rating }} {{ __('sao') }}</dd>
                        </div>
                        <div>
                            <dt class="font-semibold text-bcom-navy">{{ __('Hiển thị công khai') }}</dt>
                            <dd class="mt-0.5">
                                <span class="inline-flex rounded-full border px-2 py-0.5 text-xs {{ $hotel->is_active ? 'border-green-200 bg-green-50 text-green-800' : 'border-gray-200 bg-gray-50 text-gray-700' }}">
                                    {{ $hotel->is_active ? __('Đang hoạt động') : __('Tạm ẩn') }}
                                </span>
                            </dd>
                        </div>
                    </dl>

                    <div class="flex flex-wrap gap-4 border-t border-slate-100 pt-4 text-sm">
                        @if ($hotel->old_price)
                            <p class="text-gray-500">
                                <span class="font-medium text-bcom-navy">{{ __('Giá gạch') }}:</span>
                                <span class="line-through">{{ number_format((float) $hotel->old_price, 0, ',', '.') }} VND</span>
                            </p>
                        @endif
                        <p>
                            <span class="font-medium text-bcom-navy">{{ __('Giá từ') }}:</span>
                            <span class="font-semibold text-bcom-blue">{{ number_format((float) ($hotel->new_price ?? $hotel->base_price), 0, ',', '.') }} VND / {{ __('đêm') }}</span>
                            <span class="text-gray-500">({{ __('Giá nền') }} {{ number_format((float) $hotel->base_price, 0, ',', '.') }})</span>
                        </p>
                    </div>

                    <div class="grid gap-2 border-t border-slate-100 pt-4 text-xs text-gray-700 sm:grid-cols-2">
                        <p><span class="font-semibold text-bcom-navy">{{ __('Cuối tuần ×') }}</span> {{ $hotel->weekend_multiplier ?? '—' }}</p>
                        <p><span class="font-semibold text-bcom-navy">{{ __('Ngày lễ ×') }}</span> {{ $hotel->holiday_multiplier ?? '—' }}</p>
                        <p><span class="font-semibold text-bcom-navy">{{ __('Last-minute (giờ)') }}</span> {{ $hotel->last_minute_hours ?? '—' }}</p>
                        <p><span class="font-semibold text-bcom-navy">{{ __('Giảm last-minute (%)') }}</span> {{ $hotel->last_minute_discount_percent ?? '—' }}</p>
                    </div>

                    @if ($hotel->amenities->isNotEmpty())
                        <div class="border-t border-slate-100 pt-4">
                            <p class="text-sm font-semibold text-bcom-navy">{{ __('Tiện nghi khách sạn') }}</p>
                            <ul class="mt-2 flex flex-wrap gap-2">
                                @foreach ($hotel->amenities as $amenity)
                                    <li class="inline-flex rounded-lg border border-slate-200 bg-sky-50/80 px-2.5 py-1 text-xs font-medium text-bcom-navy">{{ $amenity->name }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="border-t border-slate-100 pt-4">
                        <p class="text-sm font-semibold text-bcom-navy">{{ __('Hình ảnh') }}</p>
                        <p class="mt-1 text-xs text-gray-500">{{ __('Ảnh đại diện và thư viện.') }}</p>

                        @if ($gallery->isEmpty())
                            <div class="mt-3 overflow-hidden rounded-xl border border-slate-200 bg-gray-100 shadow-sm">
                                <a href="{{ $hotel->thumbnailUrl() }}" target="_blank" rel="noopener noreferrer" class="block">
                                    <img src="{{ $hotel->thumbnailUrl() }}" alt="{{ $hotel->name }}"
                                        class="aspect-video w-full object-cover object-center sm:max-h-80"
                                        loading="eager" decoding="async">
                                </a>
                            </div>
                        @else
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

                    <div class="border-t border-slate-100 pt-4">
                        <p class="text-sm font-semibold text-bcom-navy">{{ __('Mô tả') }}</p>
                        <p class="mt-2 whitespace-pre-line text-sm text-gray-700">{{ $hotel->description ?: __('Chưa có mô tả.') }}</p>
                    </div>

                    @if (! empty($hotel->email_templates))
                        <div class="border-t border-slate-100 pt-4">
                            <p class="text-sm font-semibold text-bcom-navy">{{ __('Mẫu email (JSON)') }}</p>
                            <pre class="mt-2 max-h-64 overflow-auto rounded-lg border border-slate-200 bg-slate-50 p-3 text-xs text-gray-800">{{ json_encode($hotel->email_templates, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    @endif
                </div>
            </div>

            @if ($hotel->cancellationPolicy)
                @php $pol = $hotel->cancellationPolicy; @endphp
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 bg-sky-50/60 px-6 py-3">
                        <h3 class="text-lg font-semibold text-bcom-navy">{{ __('Chính sách hủy') }}</h3>
                    </div>
                    <div class="space-y-3 p-6 text-sm">
                        <p><span class="font-semibold text-bcom-navy">{{ __('Tên') }}:</span> {{ $pol->name }}</p>
                        <p>
                            <span class="font-semibold text-bcom-navy">{{ __('Kích hoạt') }}:</span>
                            {{ $pol->is_active ? __('Có') : __('Không') }}
                        </p>
                        <p class="text-xs text-gray-600">
                            {{ __('Nhắc D-3') }}: {{ $pol->send_reminder_d3 ? __('Có') : __('Không') }} —
                            {{ __('Nhắc D-1') }}: {{ $pol->send_reminder_d1 ? __('Có') : __('Không') }} —
                            {{ __('Nhắc H-6') }}: {{ $pol->send_reminder_h6 ? __('Có') : __('Không') }}
                        </p>
                        @if ($pol->tiers->isNotEmpty())
                            <table class="mt-2 w-full border-collapse text-left text-xs">
                                <thead class="border-b border-slate-200 text-bcom-navy">
                                    <tr>
                                        <th class="py-2 pr-2">{{ __('Từ (giờ trước)') }}</th>
                                        <th class="py-2 pr-2">{{ __('Đến (giờ)') }}</th>
                                        <th class="py-2">{{ __('Phí (%)') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($pol->tiers as $tier)
                                        <tr>
                                            <td class="py-1.5 pr-2">{{ $tier->min_hours_before }}</td>
                                            <td class="py-1.5 pr-2">{{ $tier->max_hours_before ?? '—' }}</td>
                                            <td class="py-1.5">{{ $tier->fee_percent }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            @endif

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 bg-sky-50/60 px-6 py-4">
                    <h3 class="text-lg font-semibold text-bcom-navy">{{ __('Loại phòng') }}</h3>
                </div>
                @if ($hotel->roomTypes->isEmpty())
                    <div class="p-8 text-center text-sm text-gray-600">{{ __('Chưa có loại phòng.') }}</div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[760px] border-collapse text-left text-sm">
                            <thead class="border-b border-slate-200 bg-white text-xs font-semibold uppercase tracking-wide text-bcom-navy">
                                <tr>
                                    <th class="px-4 py-3">{{ __('Loại phòng') }}</th>
                                    <th class="px-4 py-3">{{ __('Định danh URL') }}</th>
                                    <th class="px-4 py-3">{{ __('Diện tích') }}</th>
                                    <th class="px-4 py-3">{{ __('Khách tối đa') }}</th>
                                    <th class="px-4 py-3">{{ __('Giá / đêm') }}</th>
                                    <th class="px-4 py-3">{{ __('Số phòng') }}</th>
                                    <th class="px-4 py-3">{{ __('Hoạt động') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($hotel->roomTypes as $rt)
                                    <tr class="align-top hover:bg-sky-50/40">
                                        <td class="px-4 py-4">
                                            <div class="flex gap-3">
                                                @php $firstRoomImg = $rt->images->first(); @endphp
                                                @if ($firstRoomImg)
                                                    <a href="{{ $firstRoomImg->url() }}" target="_blank" rel="noopener noreferrer" class="relative isolate h-16 w-[4.5rem] shrink-0 overflow-hidden rounded-md border border-slate-200 bg-gray-100">
                                                        <img src="{{ $firstRoomImg->url() }}" alt=""
                                                            class="absolute inset-0 h-full w-full object-cover object-center">
                                                    </a>
                                                @endif
                                                <div class="min-w-0">
                                                    <p class="font-semibold text-blue-700">{{ $rt->name }}</p>
                                                    @if ($rt->images->count() > 1)
                                                        <div class="mt-2 flex flex-wrap gap-1">
                                                            @foreach ($rt->images->slice(1)->take(6) as $img)
                                                                <a href="{{ $img->url() }}" target="_blank" rel="noopener noreferrer" class="block h-10 w-14 overflow-hidden rounded border border-slate-200 bg-gray-100">
                                                                    <img src="{{ $img->url() }}" alt="" class="h-full w-full object-cover object-center">
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    @endif
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
                                        <td class="px-4 py-4 font-mono text-xs text-gray-600">{{ $rt->slug }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-gray-800">
                                            @if ($rt->area_sqm !== null)
                                                {{ rtrim(rtrim(number_format((float) $rt->area_sqm, 2, ',', '.'), '0'), ',') }} m²
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="flex flex-wrap gap-0.5">
                                                @for ($i = 0; $i < min($rt->max_occupancy, 8); $i++)
                                                    <x-icon.user class="h-4 w-4 text-sky-500" />
                                                @endfor
                                                @if ($rt->max_occupancy > 8)
                                                    <span class="text-xs text-gray-600">+{{ $rt->max_occupancy - 8 }}</span>
                                                @endif
                                            </div>
                                            <p class="mt-1 text-xs text-gray-500">{{ $rt->max_occupancy }} {{ __('người') }}</p>
                                        </td>
                                        <td class="px-4 py-4">
                                            @if ($rt->old_price)
                                                <p class="text-xs text-gray-500 line-through">{{ number_format((float) $rt->old_price, 0, ',', '.') }}</p>
                                            @endif
                                            <p class="font-medium text-bcom-blue">{{ number_format((float) ($rt->new_price ?? $rt->base_price), 0, ',', '.') }} VND</p>
                                            <p class="text-xs text-gray-500">{{ __('Giá nền') }} {{ number_format((float) $rt->base_price, 0, ',', '.') }}</p>
                                            <p class="text-xs text-gray-500">× {{ __('cuối tuần') }} {{ $rt->weekend_multiplier ?? '—' }}, {{ __('lễ') }} {{ $rt->holiday_multiplier ?? '—' }}</p>
                                        </td>
                                        <td class="px-4 py-4 text-gray-800">{{ $rt->quantity }}</td>
                                        <td class="px-4 py-4">
                                            {{ $rt->is_active ? __('Có') : __('Không') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <a href="{{ route('admin.hotels.index') }}" class="inline-block text-sm font-semibold text-bcom-blue hover:underline">{{ __('← Danh sách') }}</a>
        </div>
    </div>
</x-app-layout>
