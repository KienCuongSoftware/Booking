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
                    <p class="leading-relaxed">Xử lý vận hành đơn đặt phòng: duyệt đơn, đổi trạng thái và hỗ trợ khách hàng.</p>
                    <p class="text-sm text-gray-500">Phạm vi truy cập: nghiệp vụ được phân công</p>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl border border-red-100 bg-red-50/40 p-5">
                    <p class="text-sm text-gray-600">Đơn chờ xử lý</p>
                    <p class="mt-1 text-sm font-medium text-red-700">Rà soát và xác nhận yêu cầu</p>
                </div>
                <div class="rounded-2xl border border-red-100 bg-red-50/40 p-5">
                    <p class="text-sm text-gray-600">Cập nhật trạng thái</p>
                    <p class="mt-1 text-sm font-medium text-red-700">chờ xử lý / xác nhận / hủy / hoàn tất</p>
                </div>
                <div class="rounded-2xl border border-red-100 bg-red-50/40 p-5">
                    <p class="text-sm text-gray-600">Hỗ trợ khách hàng</p>
                    <p class="mt-1 text-sm font-medium text-red-700">Xử lý sự cố và yêu cầu đặt phòng</p>
                </div>
                <div class="rounded-2xl border border-red-100 bg-red-50/40 p-5">
                    <p class="text-sm text-gray-600">Lịch sử thao tác</p>
                    <p class="mt-1 text-sm font-medium text-red-700">Theo dõi thay đổi trạng thái đơn</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
