<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-bcom-navy leading-tight">
            {{ __('Thêm loại phòng') }}
        </h2>
    </x-slot>

    <div class="py-10 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white border border-slate-200 rounded-2xl shadow-md shadow-slate-900/5 p-8">
                @include('host.room-types._form', [
                    'hotel' => $hotel,
                    'roomType' => null,
                    'action' => route('host.hotels.room-types.store', $hotel),
                    'method' => 'POST',
                    'submitLabel' => __('Tạo loại phòng'),
                    'bedLinesInit' => old('bed_lines', [['area_name' => '', 'bed_summary' => '']]),
                    'amenitySections' => $amenitySections,
                    'amenityCategoryLabels' => $amenityCategoryLabels,
                ])
            </div>
        </div>
    </div>
</x-app-layout>
