<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-bcom-navy">
            {{ __('Đặt lại') }}
        </h2>
    </x-slot>

    <div class="min-w-0 px-4 py-10 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-4xl min-w-0 space-y-6">
            <x-flash-status />

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-md shadow-slate-900/5 sm:p-8">
                <p class="text-sm leading-relaxed text-gray-700">
                    {{ __('Đặt lại nhanh từ các lần lưu trú trước sẽ có sau khi hệ thống lưu lịch sử đặt phòng đầy đủ.') }}
                </p>
            </div>

            <x-customer.empty-state
                :title="__('Chưa có gợi ý đặt lại')"
                :description="__('Các lần lưu trú trước sẽ xuất hiện tại đây để bạn chọn đặt lại.')" />
        </div>
    </div>
</x-app-layout>
