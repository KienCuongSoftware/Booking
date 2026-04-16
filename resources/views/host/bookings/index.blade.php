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
                        <option value="no_show" @selected(request('status') === 'no_show')>{{ __('Không đến') }}</option>
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
                                            <span class="inline-flex rounded-full border px-2 py-0.5 text-xs {{ in_array($booking->status->value, ['cancelled', 'no_show'], true) ? 'border-red-200 bg-red-50 text-red-700' : ($booking->status->value === 'completed' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-sky-200 bg-sky-50 text-bcom-blue') }}">
                                                {{ $booking->status->labelVi() }}
                                            </span>
                                            @if ($booking->transactions->isNotEmpty())
                                                <p class="mt-2 text-xs text-gray-500">
                                                    {{ __('Ledger') }}: {{ $booking->transactions->count() }} {{ __('giao dịch') }}
                                                </p>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4">
                                            <form method="POST" action="{{ route('host.bookings.update-status', $booking) }}" class="space-y-2">
                                                @csrf
                                                @method('PATCH')
                                                <select name="status" class="w-full rounded-lg border-gray-200 text-xs focus:border-bcom-blue focus:ring-bcom-blue/20">
                                                    @if ($booking->status->value === 'pending')
                                                        <option value="confirmed">{{ __('Xác nhận') }}</option>
                                                        <option value="cancelled">{{ __('Từ chối / Hủy') }}</option>
                                                    @elseif ($booking->status->value === 'confirmed')
                                                        <option value="completed">{{ __('Hoàn tất') }}</option>
                                                        <option value="cancelled">{{ __('Hủy đơn') }}</option>
                                                        <option value="no_show">{{ __('Không đến (No-show)') }}</option>
                                                    @else
                                                        <option value="" selected disabled>{{ __('Trạng thái hiện tại không thể cập nhật') }}</option>
                                                    @endif
                                                </select>
                                                <div>
                                                    <label class="text-xs font-medium text-gray-700" for="host_note_{{ $booking->id }}">{{ __('Ghi chú nội bộ') }}</label>
                                                    <textarea id="host_note_{{ $booking->id }}" name="host_note" rows="2" class="mt-1 w-full rounded-lg border-gray-200 text-xs focus:border-bcom-blue focus:ring-bcom-blue/20">{{ old('host_note', $booking->host_note) }}</textarea>
                                                </div>
                                                <div>
                                                    <label class="text-xs font-medium text-gray-700" for="internal_tags_{{ $booking->id }}">{{ __('Tag nội bộ (cách nhau bởi dấu phẩy)') }}</label>
                                                    <input id="internal_tags_{{ $booking->id }}" type="text" name="internal_tags" value="{{ old('internal_tags', is_array($booking->internal_tags) ? implode(', ', $booking->internal_tags) : '') }}" class="mt-1 w-full rounded-lg border-gray-200 text-xs focus:border-bcom-blue focus:ring-bcom-blue/20" placeholder="VIP, công tác">
                                                </div>
                                                @if ($booking->hold_expires_at)
                                                    <p class="text-xs text-amber-800">{{ __('Giữ chỗ đến') }}: {{ $booking->hold_expires_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</p>
                                                @endif
                                                <label class="inline-flex items-center gap-2 text-xs text-gray-600">
                                                    <input type="checkbox" name="mark_paid" value="1" class="rounded border-gray-300 text-bcom-blue focus:ring-bcom-blue">
                                                    {{ __('Đánh dấu đã thanh toán') }}
                                                </label>
                                                <button
                                                    type="submit"
                                                    @disabled(in_array($booking->status->value, ['cancelled', 'completed', 'no_show'], true))
                                                    class="inline-flex items-center rounded-lg bg-bcom-blue px-3 py-1.5 text-xs font-semibold text-white hover:bg-bcom-blue/90 disabled:cursor-not-allowed disabled:bg-slate-300">
                                                    {{ __('Cập nhật') }}
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('host.bookings.check-in', $booking) }}" class="mt-3 space-y-1">
                                                @csrf
                                                <label class="text-xs font-medium text-gray-700" for="checkin_token_{{ $booking->id }}">{{ __('Check-in (QR payload)') }}</label>
                                                <div class="flex gap-1">
                                                    <input id="checkin_token_{{ $booking->id }}" type="text" name="token" class="w-full rounded-lg border-gray-200 text-xs focus:border-bcom-blue focus:ring-bcom-blue/20" placeholder='JSON / BK...|token / URL có token'>
                                                    <button type="submit" class="inline-flex shrink-0 items-center rounded-lg bg-emerald-600 px-2 py-1 text-xs font-semibold text-white hover:bg-emerald-700">{{ __('OK') }}</button>
                                                </div>
                                                <p class="text-[11px] text-gray-500">{{ __('Chỉ check-in được đơn đã xác nhận và đúng ngày lưu trú.') }}</p>
                                            </form>
                                            @php
                                                $refunds = $booking->transactions->where('type', 'refund');
                                            @endphp
                                            @if ($refunds->isNotEmpty())
                                                <div class="mt-3 space-y-2 rounded-lg border border-amber-200 bg-amber-50 p-2">
                                                    <p class="text-xs font-semibold text-amber-800">{{ __('Refund workflow') }}</p>
                                                    @foreach ($refunds as $refundTx)
                                                        <form method="POST" action="{{ route('host.bookings.update-refund-status', [$booking, $refundTx]) }}" class="space-y-1">
                                                            @csrf
                                                            @method('PATCH')
                                                            <p class="text-xs text-amber-900">
                                                                {{ __('Số tiền') }}: {{ number_format((float) $refundTx->amount, 0, ',', '.') }} {{ $refundTx->currency }}
                                                                · {{ __('Hiện tại') }}: {{ strtoupper($refundTx->status) }}
                                                            </p>
                                                            <div class="flex items-center gap-2">
                                                                <select name="status" class="w-full rounded-lg border-amber-200 bg-white text-xs focus:border-amber-400 focus:ring-amber-200">
                                                                    <option value="processing">{{ __('PROCESSING') }}</option>
                                                                    <option value="refunded">{{ __('REFUNDED') }}</option>
                                                                    <option value="failed">{{ __('FAILED') }}</option>
                                                                </select>
                                                                <button type="submit" class="inline-flex items-center rounded-lg bg-amber-600 px-2 py-1 text-xs font-semibold text-white hover:bg-amber-700">
                                                                    {{ __('Lưu') }}
                                                                </button>
                                                            </div>
                                                        </form>
                                                    @endforeach
                                                </div>
                                            @endif
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
