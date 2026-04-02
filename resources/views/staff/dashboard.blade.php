<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-red-800 leading-tight">
            {{ __('Staff Dashboard') }} - {{ auth()->user()->role->labelVi() }}
        </h2>
    </x-slot>

    <div class="py-10 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto space-y-6">
            <div class="bg-white border border-red-100 rounded-2xl shadow-md shadow-red-900/5 overflow-hidden">
                <div class="p-8 text-gray-800 space-y-3">
                    <p class="leading-relaxed">{{ __('Staff handles booking operations: review, update status, and support customers.') }}</p>
                    <p class="text-sm text-gray-500">{{ __('Access scope: assigned booking operations') }}</p>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <a href="{{ route('staff.bookings.pending') }}" class="rounded-2xl border border-red-100 bg-red-50/40 p-5 hover:bg-red-100/60 transition">
                    <p class="text-sm text-gray-600">{{ __('Pending Bookings') }}</p>
                    <p class="mt-1 text-sm font-medium text-red-700">{{ __('Review and confirm requests') }}</p>
                </a>
                <a href="{{ route('staff.bookings.index') }}" class="rounded-2xl border border-red-100 bg-red-50/40 p-5 hover:bg-red-100/60 transition">
                    <p class="text-sm text-gray-600">{{ __('Status Updates') }}</p>
                    <p class="mt-1 text-sm font-medium text-red-700">{{ __('pending / confirmed / cancelled / completed') }}</p>
                </a>
                <a href="{{ route('staff.bookings.index') }}" class="rounded-2xl border border-red-100 bg-red-50/40 p-5 hover:bg-red-100/60 transition">
                    <p class="text-sm text-gray-600">{{ __('Customer Support') }}</p>
                    <p class="mt-1 text-sm font-medium text-red-700">{{ __('Handle booking incidents and requests') }}</p>
                </a>
                <a href="{{ route('staff.bookings.history') }}" class="rounded-2xl border border-red-100 bg-red-50/40 p-5 hover:bg-red-100/60 transition">
                    <p class="text-sm text-gray-600">{{ __('Action History') }}</p>
                    <p class="mt-1 text-sm font-medium text-red-700">{{ __('Track booking status changes') }}</p>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
