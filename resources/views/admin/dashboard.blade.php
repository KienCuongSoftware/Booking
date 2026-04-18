<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-bcom-navy leading-tight">{{ __('Bảng điều khiển') }} — {{ __('Quản trị') }}</h2>
    </x-slot>
    <div class="py-10 px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-6">
            <x-flash-status />
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-gray-500">{{ __('Người dùng') }}</p>
                    <p class="mt-2 text-3xl font-bold text-bcom-navy">{{ $stats['users'] }}</p>
                    <a href="{{ route('admin.users.index') }}" class="mt-2 inline-block text-sm font-semibold text-bcom-blue hover:underline">{{ __('Quản lý') }}</a>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-gray-500">{{ __('Khách sạn') }}</p>
                    <p class="mt-2 text-3xl font-bold text-bcom-navy">{{ $stats['hotels'] }}</p>
                    <a href="{{ route('admin.hotels.index') }}" class="mt-2 inline-block text-sm font-semibold text-bcom-blue hover:underline">{{ __('Xem') }}</a>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-gray-500">{{ __('Đơn đặt') }}</p>
                    <p class="mt-2 text-3xl font-bold text-bcom-navy">{{ $stats['bookings'] }}</p>
                    <a href="{{ route('admin.bookings.index') }}" class="mt-2 inline-block text-sm font-semibold text-bcom-blue hover:underline">{{ __('Xem') }}</a>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-gray-500">{{ __('Audit (7 ngày)') }}</p>
                    <p class="mt-2 text-3xl font-bold text-bcom-navy">{{ $stats['audit_logs_7d'] }}</p>
                    <a href="{{ route('admin.audit-logs.index') }}" class="mt-2 inline-block text-sm font-semibold text-bcom-blue hover:underline">{{ __('Mở nhật ký') }}</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
