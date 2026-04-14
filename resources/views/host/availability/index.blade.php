<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-bcom-navy leading-tight">
            {{ __('Lịch khả dụng phòng') }}
        </h2>
    </x-slot>

    <div class="py-10 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto space-y-5">
            <form method="GET" class="rounded-2xl border border-slate-200 bg-white p-4">
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
            @endif
        </div>
    </div>
</x-app-layout>
