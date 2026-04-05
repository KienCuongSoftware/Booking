<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-red-800 leading-tight">
            {{ __('Sửa loại phòng') }}: {{ $roomType->name }}
        </h2>
    </x-slot>

    <div class="py-10 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto space-y-5">
            <x-flash-status />
            <div class="bg-white border border-red-100 rounded-2xl shadow-md shadow-red-900/5 p-8">
                @php
                    $bedFromModel = $roomType->bedLines->map(fn ($l) => [
                        'area_name' => $l->area_name ?? '',
                        'bed_summary' => $l->bed_summary,
                    ])->values()->all();
                    if (count($bedFromModel) === 0) {
                        $bedFromModel = [['area_name' => '', 'bed_summary' => '']];
                    }
                @endphp
                @include('host.room-types._form', [
                    'hotel' => $hotel,
                    'roomType' => $roomType,
                    'action' => route('host.room-types.update', $roomType),
                    'method' => 'PUT',
                    'submitLabel' => __('Cập nhật loại phòng'),
                    'bedLinesInit' => old('bed_lines', $bedFromModel),
                    'amenitySections' => $amenitySections,
                    'amenityCategoryLabels' => $amenityCategoryLabels,
                ])
            </div>
        </div>
    </div>
</x-app-layout>
