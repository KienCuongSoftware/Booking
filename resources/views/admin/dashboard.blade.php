<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-red-800 leading-tight">
            {{ __('Bảng điều khiển') }} - {{ auth()->user()->role->shortLabelVi() }}
        </h2>
    </x-slot>

    <div class="py-10 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto space-y-6">
            <div class="bg-white border border-red-100 rounded-2xl shadow-md shadow-red-900/5 overflow-hidden">
                <div class="p-8 text-gray-800 space-y-3">
                    <p class="leading-relaxed">Bạn có toàn quyền quản lý người dùng, khách sạn, phòng, đơn đặt và cấu hình hệ thống.</p>
                    <p class="text-sm text-gray-500">Phạm vi truy cập: toàn hệ thống</p>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl border border-red-100 bg-red-50/40 p-5">
                    <p class="text-sm text-gray-600">Quản lý người dùng</p>
                    <p class="mt-1 text-sm font-medium text-red-700">Phân quyền và kiểm soát tài khoản</p>
                </div>
                <div class="rounded-2xl border border-red-100 bg-red-50/40 p-5">
                    <p class="text-sm text-gray-600">Quản lý khách sạn và phòng</p>
                    <p class="mt-1 text-sm font-medium text-red-700">Theo dõi dữ liệu toàn nền tảng</p>
                </div>
                <div class="rounded-2xl border border-red-100 bg-red-50/40 p-5">
                    <p class="text-sm text-gray-600">Giám sát đơn đặt</p>
                    <p class="mt-1 text-sm font-medium text-red-700">Theo dõi trạng thái và sự cố</p>
                </div>
                <div class="rounded-2xl border border-red-100 bg-red-50/40 p-5">
                    <p class="text-sm text-gray-600">Cấu hình hệ thống</p>
                    <p class="mt-1 text-sm font-medium text-red-700">Mail, OAuth và tham số nghiệp vụ</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
