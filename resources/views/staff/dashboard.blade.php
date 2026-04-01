<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-red-800 leading-tight">
            {{ __('Staff') }} — {{ auth()->user()->role->labelVi() }}
        </h2>
    </x-slot>

    <div class="py-10 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="bg-white border border-red-100 rounded-2xl shadow-md shadow-red-900/5 overflow-hidden">
                <div class="p-8 text-gray-800 space-y-3">
                    <p class="leading-relaxed">{{ __('Khu vực nhân viên: xử lý booking, xác nhận và hỗ trợ khách.') }}</p>
                    <p class="text-sm text-gray-500">{{ __('Route:') }} <code class="text-xs bg-red-50 text-red-800 px-2 py-1 rounded-lg border border-red-100">/staff/dashboard</code></p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
