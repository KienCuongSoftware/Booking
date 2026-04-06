<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-bcom-navy">
            {{ __('Đơn đặt của tôi') }}
        </h2>
    </x-slot>

    <div class="min-w-0 px-4 py-10 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-4xl min-w-0 space-y-6">
            <x-flash-status />

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-md shadow-slate-900/5 sm:p-8">
                <p class="text-sm leading-relaxed text-gray-700">
                    {{ __('Xem lịch sử đặt phòng và trạng thái đơn (chờ xử lý, đã xác nhận, đã hủy, hoàn tất). Chức năng đặt phòng theo ngày sẽ được bổ sung sau.') }}
                </p>
            </div>

            <x-customer.empty-state
                :title="__('Chưa có đơn đặt nào')"
                :description="__('Khi bạn đặt phòng, các đơn sẽ hiển thị tại đây.')" />
        </div>
    </div>
</x-app-layout>
