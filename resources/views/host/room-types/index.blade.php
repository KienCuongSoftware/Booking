<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-red-800 leading-tight">
            {{ __('Phòng và giá') }}
        </h2>
    </x-slot>

    <div class="py-10 px-4 sm:px-6 lg:px-8">
        <div
            class="max-w-7xl mx-auto space-y-6"
            x-data="{
                deleteOpen: false,
                deleteAction: '',
                deleteName: '',
                addRoomUrls: {{ \Illuminate\Support\Js::from($hotels->mapWithKeys(fn ($h) => [(string) $h->id => route('host.hotels.room-types.create', $h)])) }},
            }"
        >
            <x-flash-status />

            <div class="flex flex-col gap-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <form method="GET" action="{{ route('host.rooms.index') }}" class="flex flex-wrap items-end gap-3">
                        <div>
                            <x-input-label for="hotel_id" :value="__('Lọc theo khách sạn')" />
                            <select id="hotel_id" name="hotel_id" onchange="this.form.submit()"
                                class="mt-1 block min-w-[220px] rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm focus:border-red-500 focus:ring-2 focus:ring-red-500/20">
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
                                class="mt-1 block w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm focus:border-red-500 focus:ring-2 focus:ring-red-500/20">
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
                            class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-red-600 text-white text-sm font-medium hover:bg-red-700 w-full sm:w-auto"
                            x-on:click="const id = $refs.hotelForAdd.value; if (addRoomUrls[id]) window.location.href = addRoomUrls[id]">
                            {{ __('Thêm loại phòng') }}
                        </button>
                    </div>
                @endif
            </div>

            @if ($hotels->isEmpty())
                <div class="rounded-2xl border border-red-100 bg-white p-10 text-center shadow-md shadow-red-900/5">
                    <p class="text-gray-700">{{ __('Bạn chưa có khách sạn. Hãy tạo khách sạn trước khi thêm phòng.') }}</p>
                    <a href="{{ route('host.hotels.create') }}" class="mt-4 inline-block text-sm font-medium text-red-700 hover:text-red-800">{{ __('Tạo khách sạn') }}</a>
                </div>
            @elseif ($roomTypes->count() === 0)
                <div class="rounded-2xl border border-red-100 bg-white p-10 text-center shadow-md shadow-red-900/5">
                    <p class="text-gray-700">{{ __('Chưa có loại phòng nào.') }}</p>
                </div>
            @else
                <div class="overflow-hidden rounded-2xl border border-red-100 bg-white shadow-md shadow-red-900/5">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[1180px] table-fixed border-collapse text-left text-sm">
                            <thead class="border-b border-red-100 bg-red-50/70 text-xs font-semibold uppercase tracking-wide text-red-800">
                                <tr>
                                    <th class="w-14 whitespace-nowrap px-3 py-3">{{ __('STT') }}</th>
                                    <th class="w-[200px] px-3 py-3">{{ __('Khách sạn') }}</th>
                                    <th class="w-[240px] px-3 py-3">{{ __('Loại chỗ ở') }}</th>
                                    <th class="w-[104px] whitespace-nowrap px-3 py-3">{{ __('Số khách') }}</th>
                                    <th class="w-16 whitespace-nowrap px-3 py-3">{{ __('Số phòng') }}</th>
                                    <th class="w-[88px] whitespace-nowrap px-3 py-3">{{ __('Diện tích (m²)') }}</th>
                                    <th class="w-[136px] px-3 py-3">{{ __('Giá / đêm') }}</th>
                                    <th class="w-[118px] whitespace-nowrap px-3 py-3">{{ __('Trạng thái') }}</th>
                                    <th class="w-[210px] whitespace-nowrap px-3 py-3 text-right">{{ __('Thao tác') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-red-50">
                                @foreach ($roomTypes as $rt)
                                    <tr class="align-top hover:bg-red-50/20">
                                        <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-gray-700">
                                            {{ ($roomTypes->firstItem() ?? 1) + $loop->index }}
                                        </td>
                                        <td class="max-w-[200px] px-3 py-4 align-top">
                                            <p class="line-clamp-2 break-words text-sm text-gray-800" title="{{ $rt->hotel->name }}">{{ $rt->hotel->name }}</p>
                                        </td>
                                        <td class="max-w-[240px] px-3 py-4 align-top">
                                            <div class="flex gap-2">
                                                @php $thumb = $rt->images->first(); @endphp
                                                @if ($thumb)
                                                    <img src="{{ $thumb->url() }}" alt="" class="h-10 w-12 shrink-0 rounded-md border border-red-100 object-cover">
                                                @endif
                                                <div class="min-w-0 flex-1">
                                                    <p class="line-clamp-2 break-words text-sm font-semibold leading-snug text-blue-700" title="{{ $rt->name }}">{{ $rt->name }}</p>
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
                                        <td class="px-3 py-4 whitespace-nowrap">
                                            <div class="flex flex-wrap items-center gap-0.5" title="{{ __('Tối đa') }} {{ $rt->max_occupancy }} {{ __('người') }}">
                                                @for ($i = 0; $i < min($rt->max_occupancy, 8); $i++)
                                                    <x-icon.user class="h-4 w-4 text-red-400" />
                                                @endfor
                                                @if ($rt->max_occupancy > 8)
                                                    <span class="text-xs text-gray-600">+{{ $rt->max_occupancy - 8 }}</span>
                                                @endif
                                            </div>
                                            <p class="mt-1 text-xs text-gray-500">{{ __('Tối đa') }} {{ $rt->max_occupancy }} {{ __('người') }}</p>
                                        </td>
                                        <td class="px-3 py-4">{{ $rt->quantity }}</td>
                                        <td class="px-3 py-4 whitespace-nowrap text-gray-800">
                                            @if ($rt->area_sqm !== null)
                                                {{ rtrim(rtrim(number_format((float) $rt->area_sqm, 2, ',', '.'), '0'), ',') }} m²
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="max-w-[136px] px-3 py-4 align-top">
                                            @if ($rt->old_price)
                                                <p class="break-words text-xs text-gray-500 line-through">{{ number_format((float) $rt->old_price, 0, ',', '.') }}</p>
                                            @endif
                                            <p class="break-words text-sm font-medium leading-snug text-red-700">{{ number_format((float) ($rt->new_price ?? $rt->base_price), 0, ',', '.') }} <span class="text-xs font-normal text-gray-600">VND</span></p>
                                        </td>
                                        <td class="px-3 py-4">
                                            <span class="inline-flex rounded-full border px-2 py-0.5 text-xs whitespace-nowrap {{ $rt->is_active ? 'border-green-200 bg-green-50 text-green-800' : 'border-gray-200 bg-gray-50 text-gray-600' }}">
                                                {{ $rt->is_active ? __('Đang bán') : __('Tạm ẩn') }}
                                            </span>
                                        </td>
                                        <td class="w-[210px] max-w-[210px] px-2 py-4 align-top">
                                            <div class="flex flex-nowrap items-center justify-end gap-2">
                                                <a href="{{ route('host.hotels.show', $rt->hotel) }}" class="inline-flex shrink-0 items-center rounded-lg border border-blue-300 bg-blue-50 px-2.5 py-1.5 text-xs font-semibold text-blue-800 hover:bg-blue-100" title="{{ __('Trang khách sạn (loại phòng nằm trong khách sạn này)') }}">{{ __('Xem') }}</a>
                                                <a href="{{ route('host.room-types.edit', $rt) }}" class="inline-flex shrink-0 items-center rounded-lg border border-amber-300 bg-amber-50 px-2.5 py-1.5 text-xs font-semibold text-amber-800 hover:bg-amber-100">{{ __('Sửa') }}</a>
                                                <button type="button"
                                                    class="inline-flex shrink-0 items-center rounded-lg border border-red-300 bg-red-50 px-2.5 py-1.5 text-xs font-semibold text-red-800 hover:bg-red-100"
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
                        <div class="relative w-full max-w-md rounded-2xl border border-red-100 bg-white p-6 shadow-2xl">
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
