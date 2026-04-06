<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-bcom-navy leading-tight">
            {{ __('Tổng quan hệ thống') }}
        </h2>
    </x-slot>

    <div class="py-10 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="bg-white border border-slate-200 rounded-2xl shadow-md shadow-slate-900/5 p-8">
                <p class="text-gray-700">{{ __('Phân tích, quản lý người dùng và điều khiển toàn hệ thống.') }}</p>
            </div>
        </div>
    </div>
</x-app-layout>

