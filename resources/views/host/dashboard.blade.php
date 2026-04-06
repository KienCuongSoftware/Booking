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
                <div class="rounded-2xl border border-slate-200 bg-sky-50/50 p-5">
                    <p class="text-sm text-gray-600">Khách sạn của tôi</p>
                    <p class="mt-1 text-sm font-medium text-bcom-blue">Tạo và cập nhật thông tin khách sạn</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-sky-50/50 p-5">
                    <p class="text-sm text-gray-600">Phòng và giá</p>
                    <p class="mt-1 text-sm font-medium text-bcom-blue">Quản lý loại phòng và giá theo ngày</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-sky-50/50 p-5">
                    <p class="text-sm text-gray-600">Đơn đặt của khách sạn</p>
                    <p class="mt-1 text-sm font-medium text-bcom-blue">Theo dõi đơn thuộc khách sạn sở hữu</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-sky-50/50 p-5">
                    <p class="text-sm text-gray-600">Hiệu suất</p>
                    <p class="mt-1 text-sm font-medium text-bcom-blue">Doanh thu và công suất phòng (sắp có)</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
