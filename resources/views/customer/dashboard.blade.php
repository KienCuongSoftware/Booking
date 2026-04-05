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
                    <p class="leading-relaxed">Xem lịch sử đặt phòng, theo dõi trạng thái và hủy đơn theo điều kiện cho phép.</p>
                    <p class="text-sm text-gray-500">Phạm vi truy cập: chỉ dữ liệu cá nhân</p>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl border border-red-100 bg-red-50/40 p-5">
                    <p class="text-sm text-gray-600">Lịch sử đơn đặt</p>
                    <p class="mt-1 text-sm font-medium text-red-700">Xem tất cả đơn đã đặt của tôi</p>
                </div>
                <div class="rounded-2xl border border-red-100 bg-red-50/40 p-5">
                    <p class="text-sm text-gray-600">Chi tiết trạng thái</p>
                    <p class="mt-1 text-sm font-medium text-red-700">chờ xử lý / xác nhận / hủy / hoàn tất</p>
                </div>
                <div class="rounded-2xl border border-red-100 bg-red-50/40 p-5">
                    <p class="text-sm text-gray-600">Hủy đơn</p>
                    <p class="mt-1 text-sm font-medium text-red-700">Được hủy trước giờ nhận phòng theo chính sách</p>
                </div>
                <div class="rounded-2xl border border-red-100 bg-red-50/40 p-5">
                    <p class="text-sm text-gray-600">Đặt lại nhanh</p>
                    <p class="mt-1 text-sm font-medium text-red-700">Đặt lại từ các lần lưu trú trước</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
