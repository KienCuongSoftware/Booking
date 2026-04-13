<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-bcom-navy">
            {{ __('Có thể hủy') }}
        </h2>
    </x-slot>

    <div class="min-w-0 px-4 py-10 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-4xl min-w-0 space-y-6">
            <x-flash-status />

            @if ($bookings->isEmpty())
                <x-customer.empty-state
                    :title="__('Không có đơn nào đang chờ hủy')"
                    :description="__('Hiện chưa có đơn đặt nào trong khoảng thời gian cho phép hủy.')" />
            @else
                <div class="grid gap-4">
                    @foreach ($bookings as $booking)
                        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-900/5">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm text-gray-500">{{ $booking->booking_code }}</p>
                                    <p class="font-semibold text-gray-900">{{ $booking->hotel->name }}</p>
                                    <p class="mt-1 text-sm text-gray-600">{{ $booking->roomType->name }}</p>
                                    <p class="mt-1 text-xs text-gray-500">{{ $booking->check_in_date->format('d/m/Y') }} → {{ $booking->check_out_date->format('d/m/Y') }} ({{ $booking->nights }} {{ __('đêm') }})</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-bcom-blue">{{ number_format((float) $booking->total_price, 0, ',', '.') }} {{ $booking->currency }}</p>
                                    <p class="mt-1 text-xs text-gray-500">{{ $booking->payment_method->labelVi() }} · {{ $booking->payment_status->labelVi() }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div>{{ $bookings->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
