<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-bcom-navy leading-tight">
            {{ __('Phòng và giá') }}
        </h2>
    </x-slot>

    <div class="min-w-0 py-10 px-4 sm:px-6 lg:px-8">
        <div
            class="mx-auto max-w-7xl min-w-0 space-y-6"
            x-data="{
                deleteOpen: false,
                deleteAction: '',
                deleteName: '',
                addRoomUrls: {{ \Illuminate\Support\Js::from($hotels->mapWithKeys(fn ($h) => [(string) $h->id => route('host.hotels.room-types.create', $h)])) }},
            }"
        >
            <x-flash-status />

            <div id="hostRoomTypesFilterRoot" class="flex flex-col gap-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <form method="GET" action="{{ route('host.rooms.index') }}" class="flex flex-wrap items-end gap-3" data-ajax-filter-form data-ajax-target="#hostRoomTypesFilterRoot">
                        <div>
                            <x-input-label for="hotel_id" :value="__('Lọc theo khách sạn')" />
                            <select id="hotel_id" name="hotel_id"
                                class="mt-1 block min-w-[220px] rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm focus:border-bcom-blue focus:ring-2 focus:ring-bcom-blue/20">
                                <option value="">{{ __('— Tất cả khách sạn —') }}</option>
                                @foreach ($hotels as $h)
                                    <option value="{{ $h->id }}" @selected((string) ($hotelId ?? '') === (string) $h->id)>{{ $h->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </form>

                    @if ($hotels->isNotEmpty())
                        @php
                            $defaultAddHotelId = (string) ($hotelId ?: $hotels->first()->id);
                        @endphp
                        <div class="w-full lg:w-auto lg:min-w-[260px]">
                            <x-input-label for="add_room_hotel_id" :value="__('Khách sạn để thêm phòng')" />
                            <select id="add_room_hotel_id" x-ref="hotelForAdd"
                                class="mt-1 block w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm focus:border-bcom-blue focus:ring-2 focus:ring-bcom-blue/20">
                                @foreach ($hotels as $h)
                                    <option value="{{ $h->id }}" @selected((string) $h->id === $defaultAddHotelId)>{{ $h->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>

                @if ($hotels->isNotEmpty())
                    <div class="flex items-center justify-end gap-4">
                        <button type="button"
                            class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-bcom-blue text-white text-sm font-medium hover:bg-bcom-blue/90 w-full sm:w-auto"
                            x-on:click="const id = $refs.hotelForAdd.value; if (addRoomUrls[id]) window.location.href = addRoomUrls[id]">
                            {{ __('Thêm loại phòng') }}
                        </button>
                    </div>
                @endif
            </div>

            @if ($hotels->isEmpty())
                <div class="rounded-2xl border border-slate-200 bg-white p-10 text-center shadow-md shadow-slate-900/5">
                    <p class="text-gray-700">{{ __('Bạn chưa có khách sạn. Hãy tạo khách sạn trước khi thêm phòng.') }}</p>
                    <a href="{{ route('host.hotels.create') }}" class="mt-4 inline-block text-sm font-medium text-bcom-blue hover:text-bcom-navy">{{ __('Tạo khách sạn') }}</a>
                </div>
            @elseif ($roomTypes->count() === 0)
                <div class="rounded-2xl border border-slate-200 bg-white p-10 text-center shadow-md shadow-slate-900/5">
                    <p class="text-gray-700">{{ __('Chưa có loại phòng nào.') }}</p>
                </div>
            @else
                <div class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-md shadow-slate-900/5">
                    <div class="min-w-0">
                        <table class="w-full min-w-0 table-fixed border-collapse text-left text-sm">
                            <thead class="border-b border-slate-200 bg-sky-50/80 text-[10px] font-semibold uppercase leading-tight tracking-wide text-bcom-navy sm:text-xs">
                                <tr>
                                    <th class="w-[3%] min-w-0 px-1 py-3 text-center sm:px-2">{{ __('STT') }}</th>
                                    <th class="w-[16%] min-w-0 px-2 py-3 sm:px-3">{{ __('Khách sạn') }}</th>
                                    <th class="w-[19%] min-w-0 px-2 py-3 sm:px-3">{{ __('Loại chỗ ở') }}</th>
                                    <th class="w-[9%] min-w-0 whitespace-normal px-2 py-3 sm:px-3">{{ __('Số khách') }}</th>
                                    <th class="w-[5%] min-w-0 px-2 py-3 sm:px-3">{{ __('Số phòng') }}</th>
                                    <th class="w-[7%] min-w-0 whitespace-normal px-2 py-3 sm:px-3" title="{{ __('Diện tích (m²)') }}">{{ __('m²') }}</th>
                                    <th class="w-[13%] min-w-0 whitespace-normal px-2 py-3 sm:px-3">{{ __('Giá / đêm') }}</th>
                                    <th class="w-[8%] min-w-0 whitespace-normal px-2 py-3 sm:px-3">{{ __('Trạng thái') }}</th>
                                    <th class="w-[20%] min-w-0 px-2 py-3 text-right sm:px-3">{{ __('Thao tác') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($roomTypes as $rt)
                                    <tr class="align-top hover:bg-sky-50/30">
                                        <td class="min-w-0 px-1 py-4 text-center text-sm font-medium tabular-nums text-gray-700 sm:px-2">
                                            {{ ($roomTypes->firstItem() ?? 1) + $loop->index }}
                                        </td>
                                        <td class="min-w-0 px-2 py-4 align-top sm:px-3">
                                            <p class="line-clamp-2 break-words text-sm text-gray-800" title="{{ $rt->hotel->name }}">{{ $rt->hotel->name }}</p>
                                        </td>
                                        <td class="min-w-0 px-2 py-4 align-top sm:px-3">
                                            <div class="flex min-w-0 gap-2">
                                                @php $thumb = $rt->images->first(); @endphp
                                                @if ($thumb)
                                                    <img src="{{ $thumb->url() }}" alt="" class="h-10 w-12 shrink-0 rounded-md border border-slate-200 object-cover">
                                                @endif
                                                <div class="min-w-0 flex-1">
                                                    <p class="line-clamp-2 break-words text-sm font-semibold leading-snug text-bcom-blue" title="{{ $rt->name }}">{{ $rt->name }}</p>
                                                    @if ($rt->bedLines->isNotEmpty())
                                                        @php $bedFirst = $rt->bedLines->first(); @endphp
                                                        <p class="mt-1 line-clamp-2 text-xs leading-snug text-gray-700" title="{{ $rt->bedLines->map(fn ($l) => trim(($l->area_name ? $l->area_name.': ' : '').$l->bed_summary))->implode(' · ') }}">
                                                            @if ($bedFirst->area_name)
                                                                <span class="font-medium">{{ $bedFirst->area_name }}:</span>
                                                            @endif
                                                            {{ $bedFirst->bed_summary }}
                                                            @if ($rt->bedLines->count() > 1)
                                                                <span class="text-gray-500"> (+{{ $rt->bedLines->count() - 1 }})</span>
                                                            @endif
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="min-w-0 px-2 py-4 sm:px-3">
                                            <div class="flex flex-wrap items-center gap-0.5" title="{{ __('Tối đa') }} {{ $rt->max_occupancy }} {{ __('người') }}">
                                                @for ($i = 0; $i < min($rt->max_occupancy, 8); $i++)
                                                    <x-icon.user class="h-4 w-4 shrink-0 text-sky-500" />
                                                @endfor
                                                @if ($rt->max_occupancy > 8)
                                                    <span class="text-xs text-gray-600">+{{ $rt->max_occupancy - 8 }}</span>
                                                @endif
                                            </div>
                                            <p class="mt-1 text-xs leading-snug text-gray-500">{{ __('Tối đa') }} {{ $rt->max_occupancy }} {{ __('người') }}</p>
                                        </td>
                                        <td class="min-w-0 px-2 py-4 sm:px-3">{{ $rt->quantity }}</td>
                                        <td class="min-w-0 px-2 py-4 text-gray-800 sm:px-3">
                                            @if ($rt->area_sqm !== null)
                                                <span class="break-words">{{ rtrim(rtrim(number_format((float) $rt->area_sqm, 2, ',', '.'), '0'), ',') }} m²</span>
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="min-w-0 px-2 py-4 align-top sm:px-3">
                                            @if ($rt->old_price)
                                                <p class="break-words text-xs text-gray-500 line-through">{{ number_format((float) $rt->old_price, 0, ',', '.') }}</p>
                                            @endif
                                            <p class="break-words text-sm font-medium leading-snug text-bcom-blue">{{ number_format((float) ($rt->new_price ?? $rt->base_price), 0, ',', '.') }} <span class="text-xs font-normal text-gray-600">VND</span></p>
                                        </td>
                                        <td class="min-w-0 px-2 py-4 sm:px-3">
                                            <span class="inline-flex max-w-full rounded-full border px-2 py-0.5 text-[10px] leading-tight sm:text-xs {{ $rt->is_active ? 'border-green-200 bg-green-50 text-green-800' : 'border-gray-200 bg-gray-50 text-gray-600' }}">
                                                {{ $rt->is_active ? __('Đang bán') : __('Tạm ẩn') }}
                                            </span>
                                        </td>
                                        <td class="min-w-0 px-1.5 py-4 align-top sm:px-2">
                                            <div class="flex flex-nowrap items-center justify-end gap-1">
                                                <a href="{{ route('host.hotels.show', $rt->hotel) }}" class="inline-flex shrink-0 items-center rounded-md border border-blue-300 bg-blue-50 px-2 py-1 text-[11px] font-semibold leading-none text-blue-800 hover:bg-blue-100 sm:rounded-lg sm:px-2.5 sm:py-1.5 sm:text-xs" title="{{ __('Trang khách sạn (loại phòng nằm trong khách sạn này)') }}">{{ __('Xem') }}</a>
                                                <a href="{{ route('host.room-types.physical-rooms.index', $rt) }}" class="inline-flex shrink-0 items-center rounded-md border border-emerald-300 bg-emerald-50 px-2 py-1 text-[11px] font-semibold leading-none text-emerald-900 hover:bg-emerald-100 sm:rounded-lg sm:px-2.5 sm:py-1.5 sm:text-xs" title="{{ __('Phòng vật lý') }}">{{ __('Phòng vật lý') }}</a>
                                                <a href="{{ route('host.room-types.edit', $rt) }}" class="inline-flex shrink-0 items-center rounded-md border border-amber-300 bg-amber-50 px-2 py-1 text-[11px] font-semibold leading-none text-amber-800 hover:bg-amber-100 sm:rounded-lg sm:px-2.5 sm:py-1.5 sm:text-xs">{{ __('Sửa') }}</a>
                                                <button type="button"
                                                    class="inline-flex shrink-0 items-center rounded-md border border-sky-300 bg-sky-50 px-2 py-1 text-[11px] font-semibold leading-none text-bcom-navy hover:bg-sky-100 sm:rounded-lg sm:px-2.5 sm:py-1.5 sm:text-xs"
                                                    x-on:click="deleteOpen = true; deleteAction = '{{ route('host.room-types.destroy', $rt) }}'; deleteName = @js($rt->name)">
                                                    {{ __('Xóa') }}
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div>{{ $roomTypes->links() }}</div>

                <form method="POST" x-bind:action="deleteAction" class="hidden" x-ref="deleteRoomForm">
                    @csrf
                    @method('DELETE')
                </form>

                <template x-teleport="body">
                    <div
                        x-show="deleteOpen"
                        x-cloak
                        class="fixed inset-0 z-[200] flex items-center justify-center p-4"
                        role="dialog"
                        aria-modal="true"
                    >
                        <div class="absolute inset-0 bg-gray-900/60" x-on:click="deleteOpen = false"></div>
                        <div class="relative w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl">
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('Xóa loại phòng?') }}</h3>
                            <p class="mt-2 text-sm text-gray-600">
                                {{ __('Loại phòng') }} <span class="font-semibold text-gray-900" x-text="deleteName"></span>
                            </p>
                            <div class="mt-6 flex justify-end gap-3">
                                <button type="button" class="rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50" x-on:click="deleteOpen = false">{{ __('Hủy') }}</button>
                                <button type="button" class="rounded-lg bg-red-600 px-3 py-2 text-sm font-medium text-white hover:bg-red-700" x-on:click="$refs.deleteRoomForm.submit()">{{ __('Xóa') }}</button>
                            </div>
                        </div>
                    </div>
                </template>
            @endif
        </div>
    </div>
</x-app-layout>
