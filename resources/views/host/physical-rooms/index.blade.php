<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-bcom-navy">
            {{ __('Phòng vật lý') }} — {{ $roomType->name }}
        </h2>
    </x-slot>

    <div class="py-10 px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl space-y-6">
            <x-flash-status />
            <p class="text-sm text-gray-600">
                <a href="{{ route('host.rooms.index', ['hotel_id' => $roomType->hotel_id]) }}" class="font-semibold text-bcom-blue hover:underline">{{ __('← Quay lại danh sách loại phòng') }}</a>
                <span class="mx-2 text-gray-300">|</span>
                <span>{{ $roomType->hotel->name }}</span>
            </p>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-sm font-semibold text-bcom-navy">{{ __('Thêm phòng vật lý') }}</h3>
                <p class="mt-1 text-xs text-gray-500">{{ __('Ví dụ: 101, 102, Studio A — dùng để gán đơn và xem lịch theo từng phòng.') }}</p>
                <form method="POST" action="{{ route('host.room-types.physical-rooms.store', $roomType) }}" class="mt-4 flex flex-wrap items-end gap-3">
                    @csrf
                    <div class="min-w-[200px] flex-1">
                        <x-input-label for="label" :value="__('Nhãn phòng')" />
                        <x-text-input id="label" name="label" type="text" class="mt-1 block w-full" required maxlength="80" placeholder="101" />
                    </div>
                    <div class="w-28">
                        <x-input-label for="sort_order" :value="__('Thứ tự')" />
                        <x-text-input id="sort_order" name="sort_order" type="number" class="mt-1 block w-full" min="0" value="0" />
                    </div>
                    <x-primary-button>{{ __('Thêm') }}</x-primary-button>
                </form>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <table class="w-full border-collapse text-left text-sm">
                    <thead class="border-b border-slate-200 bg-sky-50/70 text-xs font-semibold uppercase text-bcom-navy">
                        <tr>
                            <th class="px-4 py-3">{{ __('Nhãn') }}</th>
                            <th class="px-4 py-3">{{ __('Thứ tự') }}</th>
                            <th class="px-4 py-3">{{ __('Hoạt động') }}</th>
                            <th class="px-4 py-3 text-right">{{ __('Thao tác') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($roomType->physicalRooms as $pr)
                            <tr class="align-middle">
                                <td class="px-4 py-3" colspan="4">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end">
                                        <form method="POST" action="{{ route('host.physical-rooms.update', $pr) }}" class="flex flex-1 flex-wrap items-end gap-3">
                                            @csrf
                                            @method('PATCH')
                                            <div class="min-w-[160px] flex-1">
                                                <x-input-label :for="'label_'.$pr->id" :value="__('Nhãn')" />
                                                <x-text-input :id="'label_'.$pr->id" name="label" type="text" class="mt-1 block w-full" value="{{ old('label', $pr->label) }}" required maxlength="80" />
                                            </div>
                                            <div class="w-24">
                                                <x-input-label :for="'sort_'.$pr->id" :value="__('STT')" />
                                                <x-text-input :id="'sort_'.$pr->id" name="sort_order" type="number" class="mt-1 block w-full" min="0" value="{{ old('sort_order', $pr->sort_order) }}" />
                                            </div>
                                            <div class="flex items-center gap-2 pb-1">
                                                <input type="hidden" name="is_active" value="0">
                                                <input id="active_{{ $pr->id }}" type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-bcom-blue" @checked(old('is_active', $pr->is_active))>
                                                <x-input-label :for="'active_'.$pr->id" :value="__('Bật')" class="!mb-0" />
                                            </div>
                                            <x-primary-button>{{ __('Lưu') }}</x-primary-button>
                                        </form>
                                        <form method="POST" action="{{ route('host.physical-rooms.destroy', $pr) }}" class="sm:ml-auto" onsubmit="return confirm(@js(__('Xóa phòng vật lý này?')));">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">{{ __('Xóa') }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-4 py-8 text-center text-sm text-gray-600" colspan="4">{{ __('Chưa có phòng vật lý.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
