<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-bcom-navy">
            {{ __('Có thể hủy') }}
        </h2>
    </x-slot>

    <div class="min-w-0 px-4 py-10 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-4xl min-w-0 space-y-6">
            <x-flash-status />

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-md shadow-slate-900/5 sm:p-8">
                <p class="text-sm leading-relaxed text-gray-700">
                    {{ __('Các đơn đủ điều kiện hủy theo chính sách (ví dụ trước giờ nhận phòng) sẽ hiển thị tại đây. Luồng hủy sẽ được kết nối khi đặt phòng theo ngày hoạt động.') }}
                </p>
            </div>

            <x-customer.empty-state
                :title="__('Không có đơn nào đang chờ hủy')"
                :description="__('Hiện chưa có đơn đặt nào trong khoảng thời gian cho phép hủy.')" />
        </div>
    </div>
</x-app-layout>
