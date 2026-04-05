<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-red-800 leading-tight">
            {{ __('Tạo khách sạn') }}
        </h2>
    </x-slot>

    <div class="py-10 px-4 sm:px-6 lg:px-8">
        <div class="max-w-5xl mx-auto">
            <div class="bg-white border border-red-100 rounded-2xl shadow-md shadow-red-900/5 p-8">
                @include('host.hotels._form', [
                    'action' => route('host.hotels.store'),
                    'method' => 'POST',
                    'hotel' => null,
                    'provinces' => $provinces,
                    'amenities' => $amenities,
                    'submitLabel' => __('Tạo khách sạn'),
                ])
            </div>
        </div>
    </div>
</x-app-layout>

