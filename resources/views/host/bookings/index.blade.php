<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-bcom-navy leading-tight">
            {{ __('Đơn đặt của khách sạn') }}
        </h2>
    </x-slot>

    <div class="py-10 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto space-y-5">
            <x-flash-status />

            <form method="GET" class="flex flex-wrap items-end gap-3">
                <div class="w-full sm:w-64">
                    <x-input-label for="status" :value="__('Lọc trạng thái')" />
                    <select id="status" name="status" class="mt-1 block w-full rounded-xl border-gray-200 text-sm focus:border-bcom-blue focus:ring-bcom-blue/20">
                        <option value="">{{ __('Tất cả') }}</option>
                        <option value="pending" @selected(request('status') === 'pending')>{{ __('Chờ xử lý') }}</option>
                        <option value="confirmed" @selected(request('status') === 'confirmed')>{{ __('Đã xác nhận') }}</option>
                        <option value="completed" @selected(request('status') === 'completed')>{{ __('Hoàn tất') }}</option>
                        <option value="cancelled" @selected(request('status') === 'cancelled')>{{ __('Đã hủy') }}</option>
                    </select>
                </div>
                <x-primary-button>{{ __('Lọc') }}</x-primary-button>
            </form>

            @if ($bookings->isEmpty())
                <div class="bg-white border border-slate-200 rounded-2xl shadow-md shadow-slate-900/5 p-8">
                    <p class="text-gray-700">{{ __('Chưa có đơn đặt nào cho khách sạn của bạn.') }}</p>
                </div>
            @else
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-md shadow-slate-900/5">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[980px] border-collapse text-left text-sm">
                            <thead class="border-b border-slate-200 bg-sky-50/70 text-xs font-semibold uppercase tracking-wide text-bcom-navy">
                                <tr>
                                    <th class="px-4 py-3">{{ __('Mã đơn') }}</th>
                                    <th class="px-4 py-3">{{ __('Khách') }}</th>
                                    <th class="px-4 py-3">{{ __('Khách sạn / Phòng') }}</th>
                                    <th class="px-4 py-3">{{ __('Lưu trú') }}</th>
                                    <th class="px-4 py-3">{{ __('Thanh toán') }}</th>
                                    <th class="px-4 py-3">{{ __('Trạng thái') }}</th>
                                    <th class="px-4 py-3">{{ __('Xử lý') }}</th>
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
                                            <p class="font-medium text-gray-900">{{ $booking->customer->name }}</p>
                                            <p class="mt-1 text-xs text-gray-600">{{ $booking->customer->email }}</p>
                                        </td>
                                        <td class="px-4 py-4">
                                            <p class="font-medium text-gray-900">{{ $booking->hotel->name }}</p>
                                            <p class="mt-1 text-xs text-gray-600">{{ $booking->roomType->name }}</p>
                                        </td>
                                        <td class="px-4 py-4">
                                            <p>{{ $booking->check_in_date->format('d/m/Y') }} → {{ $booking->check_out_date->format('d/m/Y') }}</p>
                                            <p class="mt-1 text-xs text-gray-500">{{ $booking->nights }} {{ __('đêm') }} · {{ $booking->guest_count }} {{ __('khách') }}</p>
                                            <p class="mt-1 text-xs font-semibold text-bcom-blue">{{ number_format((float) $booking->total_price, 0, ',', '.') }} {{ $booking->currency }}</p>
                                        </td>
                                        <td class="px-4 py-4">
                                            <p>{{ $booking->payment_method->labelVi() }}</p>
                                            <p class="mt-1 text-xs text-gray-500">{{ $booking->payment_provider?->labelVi() ?? __('Không áp dụng') }}</p>
                                            <p class="mt-1 text-xs text-gray-500">{{ $booking->payment_status->labelVi() }}</p>
                                            @if ($booking->payment_reference)
                                                <p class="mt-1 text-xs text-gray-500">Ref: {{ $booking->payment_reference }}</p>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="inline-flex rounded-full border px-2 py-0.5 text-xs {{ $booking->status->value === 'cancelled' ? 'border-red-200 bg-red-50 text-red-700' : ($booking->status->value === 'completed' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-sky-200 bg-sky-50 text-bcom-blue') }}">
                                                {{ $booking->status->labelVi() }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4">
                                            <form method="POST" action="{{ route('host.bookings.update-status', $booking) }}" class="space-y-2">
                                                @csrf
                                                @method('PATCH')
                                                <select name="status" class="w-full rounded-lg border-gray-200 text-xs focus:border-bcom-blue focus:ring-bcom-blue/20">
                                                    <option value="confirmed">{{ __('Xác nhận') }}</option>
                                                    <option value="completed">{{ __('Hoàn tất') }}</option>
                                                    <option value="cancelled">{{ __('Từ chối / Hủy') }}</option>
                                                </select>
                                                <label class="inline-flex items-center gap-2 text-xs text-gray-600">
                                                    <input type="checkbox" name="mark_paid" value="1" class="rounded border-gray-300 text-bcom-blue focus:ring-bcom-blue">
                                                    {{ __('Đánh dấu đã thanh toán') }}
                                                </label>
                                                <button type="submit" class="inline-flex items-center rounded-lg bg-bcom-blue px-3 py-1.5 text-xs font-semibold text-white hover:bg-bcom-blue/90">
                                                    {{ __('Cập nhật') }}
                                                </button>
                                            </form>
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
