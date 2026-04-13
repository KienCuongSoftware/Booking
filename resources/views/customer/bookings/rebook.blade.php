<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-bcom-navy">
            {{ __('Đặt lại') }}
        </h2>
    </x-slot>

    <div class="min-w-0 px-4 py-10 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-4xl min-w-0 space-y-6">
            <x-flash-status />

            @if ($bookings->isEmpty())
                <x-customer.empty-state
                    :title="__('Chưa có gợi ý đặt lại')"
                    :description="__('Các lần lưu trú trước sẽ xuất hiện tại đây để bạn chọn đặt lại.')" />
            @else
                <div class="grid gap-4">
                    @foreach ($bookings as $booking)
                        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-900/5">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm text-gray-500">{{ $booking->booking_code }}</p>
                                    <p class="font-semibold text-gray-900">{{ $booking->hotel->name }}</p>
                                    <p class="mt-1 text-sm text-gray-600">{{ $booking->roomType->name }}</p>
                                    <p class="mt-1 text-xs text-gray-500">{{ $booking->check_in_date->format('d/m/Y') }} → {{ $booking->check_out_date->format('d/m/Y') }}</p>
                                    @php $suggestions = $rebookSuggestions[$booking->id] ?? []; @endphp
                                    @if (! empty($suggestions))
                                        <div class="mt-3 flex flex-wrap gap-2">
                                            @foreach ($suggestions as $suggestion)
                                                <a
                                                    href="{{ route('public.hotels.show', ['hotel' => $booking->hotel->slug, 'room_type_id' => $booking->room_type_id, 'check_in_date' => $suggestion['check_in_date'], 'check_out_date' => $suggestion['check_out_date'], 'guest_count' => $booking->guest_count]) }}"
                                                    class="inline-flex items-center rounded-lg border border-sky-200 bg-sky-50 px-2.5 py-1 text-xs font-semibold text-bcom-blue hover:bg-sky-100">
                                                    {{ \Illuminate\Support\Carbon::parse($suggestion['check_in_date'])->format('d/m') }}
                                                    → {{ \Illuminate\Support\Carbon::parse($suggestion['check_out_date'])->format('d/m') }}
                                                    @if ($suggestion['same_weekday'])
                                                        · {{ __('Cùng thứ') }}
                                                    @endif
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                @php
                                    $defaultCheckIn = $booking->check_in_date->isFuture()
                                        ? $booking->check_in_date->toDateString()
                                        : now()->addDay()->toDateString();
                                    $defaultCheckOut = \Illuminate\Support\Carbon::parse($defaultCheckIn)
                                        ->addDays(max(1, (int) $booking->nights))
                                        ->toDateString();
                                @endphp
                                <a href="{{ route('public.hotels.show', ['hotel' => $booking->hotel->slug, 'room_type_id' => $booking->room_type_id, 'check_in_date' => $defaultCheckIn, 'check_out_date' => $defaultCheckOut, 'guest_count' => $booking->guest_count]) }}"
                                    class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-bcom-navy hover:bg-sky-50">
                                    {{ __('Đặt lại ngay') }}
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div>{{ $bookings->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
