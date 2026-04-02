<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-red-800 leading-tight">
            {{ __('Host Dashboard') }} - {{ auth()->user()->role->labelVi() }}
        </h2>
    </x-slot>

    <div class="py-10 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto space-y-6">
            <div class="bg-white border border-red-100 rounded-2xl shadow-md shadow-red-900/5 overflow-hidden">
                <div class="p-8 text-gray-800 space-y-3">
                    <p class="leading-relaxed">{{ __('Host can manage only owned hotels, rooms, pricing, and related bookings.') }}</p>
                    <p class="text-sm text-gray-500">{{ __('Access scope: own properties only') }}</p>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <a href="{{ route('host.hotels.index') }}" class="rounded-2xl border border-red-100 bg-red-50/40 p-5 hover:bg-red-100/60 transition">
                    <p class="text-sm text-gray-600">{{ __('My Hotels') }}</p>
                    <p class="mt-1 text-sm font-medium text-red-700">{{ __('Create and update property info') }}</p>
                </a>
                <a href="{{ route('host.rooms.index') }}" class="rounded-2xl border border-red-100 bg-red-50/40 p-5 hover:bg-red-100/60 transition">
                    <p class="text-sm text-gray-600">{{ __('Rooms and Pricing') }}</p>
                    <p class="mt-1 text-sm font-medium text-red-700">{{ __('Manage room types and daily prices') }}</p>
                </a>
                <a href="{{ route('host.bookings.index') }}" class="rounded-2xl border border-red-100 bg-red-50/40 p-5 hover:bg-red-100/60 transition">
                    <p class="text-sm text-gray-600">{{ __('Hotel Bookings') }}</p>
                    <p class="mt-1 text-sm font-medium text-red-700">{{ __('Track bookings for owned hotels') }}</p>
                </a>
                <div class="rounded-2xl border border-red-100 bg-red-50/40 p-5">
                    <p class="text-sm text-gray-600">{{ __('Performance') }}</p>
                    <p class="mt-1 text-sm font-medium text-red-700">{{ __('Revenue and occupancy (coming soon)') }}</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
