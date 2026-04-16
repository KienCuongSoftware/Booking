<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-bcom-navy">
            {{ __('Danh sách chờ') }}
        </h2>
    </x-slot>

    <div class="min-w-0 px-4 py-10 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-4xl min-w-0 space-y-6">
            <x-flash-status />

            <p class="text-sm text-gray-600">
                {{ __('Khi có chỗ trống trùng loại phòng và ngày bạn đã chọn, hệ thống sẽ gửi email thông báo.') }}
            </p>

            @if ($entries->isEmpty())
                <x-customer.empty-state
                    :title="__('Chưa có đăng ký chờ')"
                    :description="__('Vào trang khách sạn và chọn «Chỉ đăng ký chờ» hoặc tick chờ khi đặt phòng nếu hết chỗ.')" />
            @else
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-md shadow-slate-900/5">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[640px] border-collapse text-left text-sm">
                            <thead class="border-b border-slate-200 bg-sky-50/70 text-xs font-semibold uppercase tracking-wide text-bcom-navy">
                                <tr>
                                    <th class="px-4 py-3">{{ __('Khách sạn') }}</th>
                                    <th class="px-4 py-3">{{ __('Phòng') }}</th>
                                    <th class="px-4 py-3">{{ __('Lịch') }}</th>
                                    <th class="px-4 py-3">{{ __('Khách') }}</th>
                                    <th class="px-4 py-3">{{ __('Đã báo') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($entries as $entry)
                                    <tr class="align-top hover:bg-sky-50/30">
                                        <td class="px-4 py-4 font-medium text-gray-900">{{ $entry->hotel->name }}</td>
                                        <td class="px-4 py-4 text-gray-700">{{ $entry->roomType->name }}</td>
                                        <td class="px-4 py-4 text-gray-700">
                                            {{ $entry->check_in_date->format('d/m/Y') }} → {{ $entry->check_out_date->format('d/m/Y') }}
                                        </td>
                                        <td class="px-4 py-4 text-gray-700">{{ $entry->guest_count }}</td>
                                        <td class="px-4 py-4 text-xs text-gray-600">
                                            {{ $entry->notified_at ? $entry->notified_at->format('d/m/Y H:i') : __('Chưa') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div>{{ $entries->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
