<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-red-800 leading-tight">
            {{ __('Khách sạn của tôi') }}
        </h2>
    </x-slot>

    <div class="py-10 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto space-y-6">
            <x-flash-status />

            <div class="flex items-center justify-end gap-4">
                <a href="{{ route('host.hotels.create') }}"
                    class="inline-flex items-center px-4 py-2 rounded-xl bg-red-600 text-white text-sm font-medium hover:bg-red-700">
                    {{ __('Thêm khách sạn') }}
                </a>
            </div>

            @if ($hotels->count() === 0)
                <div class="bg-white border border-red-100 rounded-2xl shadow-md shadow-red-900/5 p-10 text-center">
                    <p class="text-gray-700">{{ __('Chưa có khách sạn nào. Hãy tạo khách sạn đầu tiên.') }}</p>
                </div>
            @else
                <div class="overflow-hidden rounded-2xl border border-red-100 bg-white shadow-md shadow-red-900/5">
                    <div class="overflow-x-auto">
                        <table class="w-full table-fixed border-collapse divide-y divide-red-100">
                            <thead class="bg-red-50/70">
                                <tr>
                                    <th class="border-r border-red-100 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-red-800 last:border-r-0 w-16">{{ __('STT') }}</th>
                                    <th class="border-r border-red-100 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-red-800 last:border-r-0 w-[220px]">{{ __('Khách sạn') }}</th>
                                    <th class="border-r border-red-100 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-red-800 last:border-r-0 w-[130px]">{{ __('Tỉnh/Thành phố') }}</th>
                                    <th class="border-r border-red-100 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-red-800 last:border-r-0 w-[180px]">{{ __('Địa chỉ') }}</th>
                                    <th class="border-r border-red-100 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-red-800 last:border-r-0 w-[200px]">{{ __('Giá') }}</th>
                                    <th class="border-r border-red-100 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-red-800 whitespace-nowrap last:border-r-0 w-[158px]">{{ __('Trạng thái') }}</th>
                                    <th class="border-r border-red-100 px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-red-800 last:border-r-0">{{ __('Thao tác') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-red-50">
                                @foreach ($hotels as $hotel)
                                    <tr class="align-top hover:bg-red-50/30">
                                        <td class="border-r border-red-100 px-4 py-4 text-sm font-medium text-gray-700 last:border-r-0">
                                            {{ ($hotels->firstItem() ?? 1) + $loop->index }}
                                        </td>
                                        <td class="border-r border-red-100 px-4 py-4 last:border-r-0 max-w-[230px]">
                                            <div class="flex items-start gap-3">
                                                <img
                                                    src="{{ $hotel->thumbnailUrl() }}"
                                                    alt="{{ $hotel->name }}"
                                                    class="h-14 w-20 shrink-0 rounded-lg border border-red-100 object-cover"
                                                />
                                                <div class="min-w-0 flex-1">
                                                    <p class="line-clamp-2 break-words font-semibold text-gray-900">{{ $hotel->name }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="border-r border-red-100 px-4 py-4 text-sm text-gray-700 last:border-r-0">
                                            {{ $hotel->province ? $hotel->province->type.' '.$hotel->province->name : $hotel->city }}
                                        </td>
                                        <td class="border-r border-red-100 px-4 py-4 text-sm text-gray-700 last:border-r-0">{{ $hotel->address }}</td>
                                        <td class="border-r border-red-100 px-4 py-4 align-top last:border-r-0 w-[200px] max-w-[200px]">
                                            @if ($hotel->old_price)
                                                <p class="text-xs text-gray-500 break-words">
                                                    <span class="line-through">{{ number_format((float) $hotel->old_price, 0, ',', '.') }} VND</span>
                                                </p>
                                            @endif
                                            <p class="mt-1 text-sm font-medium leading-snug text-red-700 break-words">
                                                {{ number_format((float) ($hotel->new_price ?? $hotel->base_price), 0, ',', '.') }} VND / {{ __('đêm') }}
                                            </p>
                                        </td>
                                        <td class="border-r border-red-100 px-4 py-4 align-top whitespace-nowrap last:border-r-0 w-[158px] max-w-[158px]">
                                            <span style="white-space: nowrap;" class="inline-flex items-center text-xs {{ $hotel->is_active ? 'text-green-700 bg-green-50 border-green-100' : 'text-gray-600 bg-gray-50 border-gray-200' }} border rounded-full px-2 py-1">
                                                {{ $hotel->is_active ? __('Đang hoạt động') : __('Tạm ẩn') }}
                                            </span>
                                        </td>
                                        <td class="border-r border-red-100 px-4 py-4 last:border-r-0 whitespace-nowrap">
                                            <div class="flex flex-nowrap items-center justify-end gap-2.5">
                                                <a href="{{ route('host.hotels.edit', $hotel) }}" class="inline-flex shrink-0 items-center rounded-lg border border-amber-300 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-800 hover:bg-amber-100">
                                                    {{ __('Sửa') }}
                                                </a>
                                                <a href="{{ route('host.hotels.show', $hotel) }}" class="inline-flex shrink-0 items-center rounded-lg border border-blue-300 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-800 hover:bg-blue-100">
                                                    {{ __('Xem') }}
                                                </a>
                                                <button
                                                    type="button"
                                                    class="inline-flex shrink-0 items-center rounded-lg border border-red-300 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-800 hover:bg-red-100"
                                                    data-hotel-delete-url="{{ route('host.hotels.destroy', $hotel) }}"
                                                    data-hotel-name="{{ e($hotel->name) }}"
                                                    onclick="bookingOpenHotelDeleteDialog(this)"
                                                >
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
                <div>{{ $hotels->links() }}</div>

                <dialog id="hotel-delete-dialog" class="w-[calc(100%-2rem)] max-w-md rounded-2xl border border-red-100 bg-white p-6 shadow-2xl">
                    <form id="hotel-delete-form" method="POST" action="">
                        @csrf
                        @method('DELETE')
                        <h3 id="delete-hotel-title" class="text-lg font-semibold text-gray-900">
                            {{ __('Xác nhận xóa khách sạn') }}
                        </h3>
                        <p class="mt-2 text-sm text-gray-600">
                            {{ __('Bạn có chắc muốn xóa khách sạn') }}
                            <span id="hotel-delete-dialog-name" class="font-semibold text-gray-900"></span>?
                        </p>
                        <p class="mt-1 text-xs text-red-600">{{ __('Hành động này không thể hoàn tác.') }}</p>

                        <div class="mt-6 flex items-center justify-end gap-3">
                            <button
                                type="button"
                                class="inline-flex items-center rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                onclick="document.getElementById('hotel-delete-dialog').close()"
                            >
                                {{ __('Hủy') }}
                            </button>
                            <button
                                type="submit"
                                class="inline-flex items-center rounded-lg bg-red-600 px-3 py-2 text-sm font-medium text-white hover:bg-red-700"
                            >
                                {{ __('Xóa khách sạn') }}
                            </button>
                        </div>
                    </form>
                </dialog>

                <script>
                    window.bookingOpenHotelDeleteDialog = function (el) {
                        var dlg = document.getElementById('hotel-delete-dialog');
                        var form = document.getElementById('hotel-delete-form');
                        var nameSpan = document.getElementById('hotel-delete-dialog-name');
                        if (!dlg || !form) {
                            return;
                        }
                        form.action = el.getAttribute('data-hotel-delete-url') || '';
                        if (nameSpan) {
                            nameSpan.textContent = el.getAttribute('data-hotel-name') || '';
                        }
                        dlg.showModal();
                    };
                </script>
            @endif
        </div>
    </div>
</x-app-layout>
