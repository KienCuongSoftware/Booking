<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-red-800 leading-tight">
            {{ __('Lịch sử đặt phòng của tôi') }}
        </h2>
    </x-slot>

    <div class="py-10 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="bg-white border border-red-100 rounded-2xl shadow-md shadow-red-900/5 p-8">
                <p class="text-gray-700">{{ __('Khu vực Khách hàng: danh sách toàn bộ đơn đặt của bạn.') }}</p>
            </div>
        </div>
    </div>
</x-app-layout>
