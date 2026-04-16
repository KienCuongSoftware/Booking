<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Báo cáo vận hành') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl space-y-6 sm:px-6 lg:px-8">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Doanh thu (đơn đã xác nhận/hoàn tất)') }}</p>
                    <p class="mt-2 text-2xl font-bold text-bcom-navy">{{ number_format($revenue, 0, ',', '.') }} VND</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Tỉ lệ hủy') }}</p>
                    <p class="mt-2 text-2xl font-bold text-amber-800">{{ $cancelRate }}%</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Tỉ lệ no-show') }}</p>
                    <p class="mt-2 text-2xl font-bold text-rose-800">{{ $noShowRate }}%</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Tổng đơn') }}</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ $totalBookings }}</p>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 bg-sky-50/60 px-6 py-4">
                    <h3 class="text-lg font-semibold text-bcom-navy">{{ __('Top loại phòng (theo số đơn)') }}</h3>
                </div>
                <div class="p-6">
                    @if ($topRoomTypes->isEmpty())
                        <p class="text-sm text-gray-600">{{ __('Chưa có dữ liệu.') }}</p>
                    @else
                        <ol class="list-decimal space-y-2 pl-5 text-sm text-gray-800">
                            @foreach ($topRoomTypes as $rt)
                                <li>
                                    <span class="font-medium">{{ $rt->name }}</span>
                                    <span class="text-gray-500">— {{ $rt->bookings_count }} {{ __('đơn') }}</span>
                                </li>
                            @endforeach
                        </ol>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
