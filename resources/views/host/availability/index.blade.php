<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-bcom-navy leading-tight">
            {{ __('Lịch khả dụng phòng') }}
        </h2>
    </x-slot>

    <div class="py-10 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto space-y-5">
            <div id="hostAvailabilityFilterRoot" class="space-y-5">
            <form method="GET" class="rounded-2xl border border-slate-200 bg-white p-4" data-ajax-filter-form data-ajax-target="#hostAvailabilityFilterRoot">
                <label class="text-sm font-medium text-gray-700" for="hotel_id">{{ __('Chọn khách sạn') }}</label>
                <div class="mt-2 flex items-center gap-3">
                    <select id="hotel_id" name="hotel_id" class="w-full rounded-lg border-gray-200 text-sm focus:border-bcom-blue focus:ring-bcom-blue/20">
                        @foreach ($hotels as $hotel)
                            <option value="{{ $hotel->id }}" @selected($selectedHotel && $selectedHotel->id === $hotel->id)>{{ $hotel->name }}</option>
                        @endforeach
                    </select>
                    <x-primary-button>{{ __('Xem lịch') }}</x-primary-button>
                </div>
            </form>

            @if (! $selectedHotel)
                <div class="rounded-2xl border border-slate-200 bg-white p-6 text-sm text-gray-600">
                    {{ __('Không có dữ liệu khách sạn để hiển thị lịch.') }}
                </div>
            @else
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-md shadow-slate-900/5">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[980px] border-collapse text-left text-xs">
                            <thead class="border-b border-slate-200 bg-sky-50/70 font-semibold uppercase tracking-wide text-bcom-navy">
                                <tr>
                                    <th class="px-3 py-2">{{ __('Loại phòng') }}</th>
                                    @foreach ($dateKeys as $dateKey)
                                        <th class="px-3 py-2 whitespace-nowrap">{{ \Illuminate\Support\Carbon::parse($dateKey)->format('d/m') }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($selectedHotel->roomTypes->where('is_active', true) as $roomType)
                                    <tr class="align-top">
                                        <td class="px-3 py-3 font-medium text-gray-900 whitespace-nowrap">{{ $roomType->name }}</td>
                                        @foreach ($dateKeys as $dateKey)
                                            @php
                                                $cell = $matrix[$roomType->id][$dateKey] ?? ['booked' => 0, 'available' => $roomType->quantity, 'capacity' => $roomType->quantity];
                                                $isFull = $cell['available'] <= 0;
                                            @endphp
                                            <td class="px-3 py-3 {{ $isFull ? 'bg-red-50' : 'bg-emerald-50/40' }}">
                                                <p class="font-semibold {{ $isFull ? 'text-red-700' : 'text-emerald-700' }}">
                                                    {{ $cell['available'] }}/{{ $cell['capacity'] }}
                                                </p>
                                                <p class="text-[11px] text-gray-500">{{ __('Đã đặt') }}: {{ $cell['booked'] }}</p>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                @if (! empty($physicalRows))
                    <div class="mt-8 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-md shadow-slate-900/5">
                        <div class="border-b border-slate-200 bg-sky-50/60 px-6 py-4">
                            <h3 class="text-lg font-semibold text-bcom-navy">{{ __('Theo phòng vật lý (đơn đã gán)') }}</h3>
                            <p class="mt-1 text-xs text-gray-600">{{ __('Mỗi ô hiển thị mã đơn trong đêm đó; “—” nghĩa là chưa gán đơn cho phòng này.') }}</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[980px] border-collapse text-left text-xs">
                                <thead class="border-b border-slate-200 bg-sky-50/70 font-semibold uppercase tracking-wide text-bcom-navy">
                                    <tr>
                                        <th class="px-3 py-2">{{ __('Phòng vật lý') }}</th>
                                        @foreach ($dateKeys as $dateKey)
                                            <th class="px-3 py-2 whitespace-nowrap">{{ \Illuminate\Support\Carbon::parse($dateKey)->format('d/m') }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($physicalRows as $row)
                                        <tr class="align-top">
                                            <td class="px-3 py-3 whitespace-nowrap">
                                                <span class="font-medium text-gray-900">{{ $row['physicalRoom']->label }}</span>
                                                <span class="block text-[10px] text-gray-500">{{ $row['roomType']->name }}</span>
                                            </td>
                                            @foreach ($dateKeys as $dateKey)
                                                @php $code = $row['cells'][$dateKey] ?? '—'; @endphp
                                                <td class="px-3 py-3 {{ $code !== '—' ? 'bg-sky-50/80 font-mono text-[11px] text-bcom-navy' : 'bg-gray-50/50 text-gray-400' }}">
                                                    {{ $code }}
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <p class="mt-6 text-center text-xs text-gray-500">
                        {{ __('Thêm phòng vật lý trong mục Phòng → Phòng vật lý để xem lịch chi tiết theo từng phòng.') }}
                    </p>
                @endif
            @endif
            </div>
        </div>
    </div>
</x-app-layout>
