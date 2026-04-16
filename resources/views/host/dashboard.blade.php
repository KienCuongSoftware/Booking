<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-bcom-navy leading-tight">
            {{ __('Bảng điều khiển') }} - {{ auth()->user()->role->shortLabelVi() }}
        </h2>
    </x-slot>

    <div class="py-10 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto space-y-6">
            <div class="bg-white border border-slate-200 rounded-2xl shadow-md shadow-slate-900/5 overflow-hidden">
                <div class="p-8 text-gray-800 space-y-3">
                    <p class="leading-relaxed">Quản lý khách sạn, phòng, giá và đơn đặt thuộc tài khoản của bạn.</p>
                    <p class="text-sm text-gray-500">Phạm vi truy cập: tài sản thuộc sở hữu</p>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <a href="{{ route('host.hotels.index') }}"
                    class="rounded-2xl border border-slate-200 bg-sky-50/50 p-5 transition hover:border-bcom-blue/30 hover:shadow-md">
                    <p class="text-sm text-gray-600">{{ __('Khách sạn của tôi') }}</p>
                    <p class="mt-1 text-sm font-medium text-bcom-blue">{{ __('Tạo và cập nhật thông tin khách sạn') }}</p>
                </a>
                <a href="{{ route('host.rooms.index') }}"
                    class="rounded-2xl border border-slate-200 bg-sky-50/50 p-5 transition hover:border-bcom-blue/30 hover:shadow-md">
                    <p class="text-sm text-gray-600">{{ __('Phòng và giá') }}</p>
                    <p class="mt-1 text-sm font-medium text-bcom-blue">{{ __('Quản lý loại phòng và giá theo ngày') }}</p>
                </a>
                <a href="{{ route('host.bookings.index') }}"
                    class="rounded-2xl border border-slate-200 bg-sky-50/50 p-5 transition hover:border-bcom-blue/30 hover:shadow-md">
                    <p class="text-sm text-gray-600">{{ __('Đơn đặt của khách sạn') }}</p>
                    <p class="mt-1 text-sm font-medium text-bcom-blue">{{ __('Theo dõi đơn thuộc khách sạn sở hữu') }}</p>
                </a>
                <a href="{{ route('host.reports.index') }}"
                    class="rounded-2xl border border-slate-200 bg-sky-50/50 p-5 transition hover:border-bcom-blue/30 hover:shadow-md">
                    <p class="text-sm text-gray-600">{{ __('Hiệu suất') }}</p>
                    <p class="mt-1 text-sm font-medium text-bcom-blue">{{ __('Doanh thu, hủy, no-show và top phòng') }}</p>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
