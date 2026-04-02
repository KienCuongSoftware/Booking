<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-red-800 leading-tight">
            {{ __('Customer Dashboard') }} - {{ auth()->user()->role->labelVi() }}
        </h2>
    </x-slot>

    <div class="py-10 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto space-y-6">
            <div class="bg-white border border-red-100 rounded-2xl shadow-md shadow-red-900/5 overflow-hidden">
                <div class="p-8 text-gray-800 space-y-3">
                    <p class="leading-relaxed">{{ __('Customer can view booking history, track statuses, and cancel bookings by policy.') }}</p>
                    <p class="text-sm text-gray-500">{{ __('Access scope: only personal bookings') }}</p>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <a href="{{ route('customer.bookings.index') }}" class="rounded-2xl border border-red-100 bg-red-50/40 p-5 hover:bg-red-100/60 transition">
                    <p class="text-sm text-gray-600">{{ __('Booking History') }}</p>
                    <p class="mt-1 text-sm font-medium text-red-700">{{ __('View all my bookings') }}</p>
                </a>
                <a href="{{ route('customer.bookings.index') }}" class="rounded-2xl border border-red-100 bg-red-50/40 p-5 hover:bg-red-100/60 transition">
                    <p class="text-sm text-gray-600">{{ __('Status Details') }}</p>
                    <p class="mt-1 text-sm font-medium text-red-700">{{ __('pending / confirmed / cancelled / completed') }}</p>
                </a>
                <a href="{{ route('customer.bookings.cancellable') }}" class="rounded-2xl border border-red-100 bg-red-50/40 p-5 hover:bg-red-100/60 transition">
                    <p class="text-sm text-gray-600">{{ __('Cancel Booking') }}</p>
                    <p class="mt-1 text-sm font-medium text-red-700">{{ __('Allowed before check-in by policy') }}</p>
                </a>
                <a href="{{ route('customer.bookings.rebook') }}" class="rounded-2xl border border-red-100 bg-red-50/40 p-5 hover:bg-red-100/60 transition">
                    <p class="text-sm text-gray-600">{{ __('Quick Rebook') }}</p>
                    <p class="mt-1 text-sm font-medium text-red-700">{{ __('Rebook from previous stays') }}</p>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
