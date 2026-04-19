<header class="sticky top-0 z-40 border-b border-bcom-navy-dark/40 bg-bcom-navy shadow-md">
    <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3 px-4 py-3 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="flex items-center gap-2.5 rounded-lg text-white outline-none ring-white/40 focus-visible:ring-2">
            <x-application-logo variant="light" class="h-9 w-9 shrink-0" />
            <span class="text-lg font-semibold tracking-tight">{{ config('app.name', 'Booking') }}</span>
        </a>

        <nav class="flex flex-wrap items-center justify-end gap-1 text-sm sm:gap-2" aria-label="{{ __('Điều hướng chính') }}">
            <a href="{{ route('home') }}"
                class="rounded-lg px-3 py-2 font-medium {{ request()->routeIs('home', 'public.hotels.show') ? 'bg-white/15 text-white' : 'text-white/90 hover:bg-white/10 hover:text-white' }}">
                {{ __('Khách sạn') }}
            </a>
            <a href="{{ route('customer.bookings.index') }}"
                class="rounded-lg px-3 py-2 font-medium {{ request()->routeIs('customer.bookings.*') && ! request()->routeIs('customer.favorites.*') ? 'bg-white/15 text-white' : 'text-white/90 hover:bg-white/10 hover:text-white' }}">
                {{ __('Đơn đặt của tôi') }}
            </a>
            <a href="{{ route('customer.favorites.index') }}"
                class="rounded-lg px-3 py-2 font-medium {{ request()->routeIs('customer.favorites.*') ? 'bg-white/15 text-white' : 'text-white/90 hover:bg-white/10 hover:text-white' }}">
                {{ __('Yêu thích') }}
            </a>

            @include('layouts.app-header-user')
        </nav>
    </div>
</header>
