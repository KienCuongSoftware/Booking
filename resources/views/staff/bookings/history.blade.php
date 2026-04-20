<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-bcom-navy">{{ __('Lịch sử') }}</h2></x-slot>
    <div class="py-8 px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-4">
            <div id="staffHistoryBookingsFilterRoot" class="space-y-4">
                <form method="GET" class="flex flex-wrap gap-2" data-ajax-filter-form data-ajax-target="#staffHistoryBookingsFilterRoot">
                    <input type="text" name="q" value="{{ request('q') }}" class="rounded-xl border-gray-200 text-sm" placeholder="{{ __('Tìm…') }}">
                    <x-primary-button type="submit">{{ __('Lọc') }}</x-primary-button>
                </form>
                @include('staff.bookings._table')
            </div>
        </div>
    </div>
</x-app-layout>
