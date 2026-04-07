<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-bcom-navy">
            {{ __('Đơn đặt của tôi') }}
        </h2>
    </x-slot>

    <div class="min-w-0 px-4 py-10 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-4xl min-w-0 space-y-6">
            <x-flash-status />

            @if ($bookings->isEmpty())
                <x-customer.empty-state
                    :title="__('Chưa có đơn đặt nào')"
                    :description="__('Khi bạn đặt phòng, các đơn sẽ hiển thị tại đây.')" />
            @else
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-md shadow-slate-900/5">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[760px] border-collapse text-left text-sm">
                            <thead class="border-b border-slate-200 bg-sky-50/70 text-xs font-semibold uppercase tracking-wide text-bcom-navy">
                                <tr>
                                    <th class="px-4 py-3">{{ __('Mã đơn') }}</th>
                                    <th class="px-4 py-3">{{ __('Khách sạn / Phòng') }}</th>
                                    <th class="px-4 py-3">{{ __('Ngày ở') }}</th>
                                    <th class="px-4 py-3">{{ __('Thanh toán') }}</th>
                                    <th class="px-4 py-3">{{ __('Trạng thái') }}</th>
                                    <th class="px-4 py-3 text-right">{{ __('Tổng') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($bookings as $booking)
                                    <tr class="align-top hover:bg-sky-50/30">
                                        <td class="px-4 py-4">
                                            <p class="font-semibold text-bcom-navy">{{ $booking->booking_code }}</p>
                                            <p class="mt-1 text-xs text-gray-500">{{ $booking->created_at?->format('d/m/Y H:i') }}</p>
                                        </td>
                                        <td class="px-4 py-4">
                                            <p class="font-medium text-gray-900">{{ $booking->hotel->name }}</p>
                                            <p class="mt-1 text-xs text-gray-600">{{ $booking->roomType->name }}</p>
                                        </td>
                                        <td class="px-4 py-4">
                                            <p>{{ $booking->check_in_date->format('d/m/Y') }} → {{ $booking->check_out_date->format('d/m/Y') }}</p>
                                            <p class="mt-1 text-xs text-gray-500">{{ $booking->nights }} {{ __('đêm') }} · {{ $booking->guest_count }} {{ __('khách') }}</p>
                                        </td>
                                        <td class="px-4 py-4">
                                            <p class="text-gray-800">{{ $booking->payment_method->labelVi() }}</p>
                                            <p class="mt-1 text-xs text-gray-500">
                                                {{ $booking->payment_provider?->labelVi() ?? __('Không áp dụng') }} · {{ $booking->payment_status->labelVi() }}
                                            </p>
                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="inline-flex rounded-full border px-2 py-0.5 text-xs {{ $booking->status->value === 'cancelled' ? 'border-red-200 bg-red-50 text-red-700' : ($booking->status->value === 'completed' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-sky-200 bg-sky-50 text-bcom-blue') }}">
                                                {{ $booking->status->labelVi() }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-right font-semibold text-bcom-blue">
                                            {{ number_format((float) $booking->total_price, 0, ',', '.') }} {{ $booking->currency }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div>{{ $bookings->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
