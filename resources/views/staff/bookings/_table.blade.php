<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[900px] text-left text-sm">
            <thead class="border-b bg-sky-50/70 text-xs font-semibold uppercase text-bcom-navy">
                <tr>
                    <th class="px-3 py-2">{{ __('Mã') }}</th>
                    <th class="px-3 py-2">{{ __('Khách') }}</th>
                    <th class="px-3 py-2">{{ __('Khách sạn') }}</th>
                    <th class="px-3 py-2">{{ __('Lưu trú') }}</th>
                    <th class="px-3 py-2">{{ __('TT') }}</th>
                    <th class="px-3 py-2">{{ __('Xử lý') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($bookings as $booking)
                    <tr class="align-top">
                        <td class="px-3 py-2 font-mono text-xs">{{ $booking->booking_code }}</td>
                        <td class="px-3 py-2">{{ $booking->customer?->name }}<br><span class="text-xs text-gray-500">{{ $booking->customer?->email }}</span></td>
                        <td class="px-3 py-2">{{ $booking->hotel?->name }}<br><span class="text-xs text-gray-500">{{ $booking->roomType?->name }}</span></td>
                        <td class="px-3 py-2 text-xs">{{ $booking->check_in_date->format('d/m/Y') }} → {{ $booking->check_out_date->format('d/m/Y') }}</td>
                        <td class="px-3 py-2 text-xs">{{ $booking->status->labelVi() }}</td>
                        <td class="px-3 py-2">
                            <form method="POST" action="{{ route('staff.bookings.update-status', $booking) }}" class="space-y-1">
                                @csrf
                                @method('PATCH')
                                <select name="status" class="w-full rounded border-gray-200 text-xs">
                                    @if ($booking->status->value === 'pending')
                                        <option value="confirmed">{{ __('Xác nhận') }}</option>
                                        <option value="cancelled">{{ __('Hủy') }}</option>
                                    @elseif ($booking->status->value === 'confirmed')
                                        <option value="completed">{{ __('Hoàn tất') }}</option>
                                        <option value="cancelled">{{ __('Hủy') }}</option>
                                        <option value="no_show">{{ __('Không đến') }}</option>
                                    @else
                                        <option value="" disabled selected>{{ __('—') }}</option>
                                    @endif
                                </select>
                                <textarea name="host_note" rows="1" class="w-full rounded border-gray-200 text-xs" placeholder="{{ __('Ghi chú') }}">{{ old('host_note', $booking->host_note) }}</textarea>
                                <label class="flex items-center gap-1 text-xs text-gray-600"><input type="checkbox" name="mark_paid" value="1" class="rounded border-gray-300"> {{ __('Đã thanh toán') }}</label>
                                <button type="submit" @disabled(in_array($booking->status->value, ['cancelled', 'completed', 'no_show'], true)) class="w-full rounded bg-bcom-blue px-2 py-1 text-xs font-semibold text-white disabled:bg-slate-300">{{ __('Cập nhật') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="mt-4">{{ $bookings->links() }}</div>
