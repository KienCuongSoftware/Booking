<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-red-800 leading-tight">
            {{ __('Chỉnh sửa khách sạn') }}: {{ $hotel->name }}
        </h2>
    </x-slot>

    <div class="py-10 px-4 sm:px-6 lg:px-8">
        <div class="max-w-5xl mx-auto space-y-5">
            <x-flash-status />

            <div class="bg-white border border-red-100 rounded-2xl shadow-md shadow-red-900/5 p-8">
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

