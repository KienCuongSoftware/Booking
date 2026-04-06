<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-bcom-navy leading-tight">
            {{ __('Chỉnh sửa khách sạn') }}: {{ $hotel->name }}
        </h2>
    </x-slot>

    <div class="py-10 px-4 sm:px-6 lg:px-8">
        <div class="max-w-5xl mx-auto space-y-5">
            <x-flash-status />

            <div class="bg-white border border-slate-200 rounded-2xl shadow-md shadow-slate-900/5 p-8">
                @include('host.hotels._form', [
                    'action' => route('host.hotels.update', $hotel),
                    'method' => 'PUT',
                    'hotel' => $hotel,
                    'provinces' => $provinces,
                    'amenities' => $amenities,
                    'submitLabel' => __('Cập nhật khách sạn'),
                ])
            </div>
        </div>
    </div>
</x-app-layout>

