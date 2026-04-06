<aside class="flex w-72 shrink-0 flex-col bg-gradient-to-b from-bcom-navy to-bcom-navy-dark text-sky-100 min-h-screen">
    <div class="border-b border-white/15 px-6 py-5">
        <a href="{{ auth()->user()->role->value === 'customer' ? route('home') : route(auth()->user()->role->dashboardRouteName()) }}" class="flex items-center gap-3">
            <x-application-logo variant="light" class="h-8 w-8" />
            <div>
                <p class="font-semibold text-white">{{ config('app.name', 'Booking') }}</p>
                <p class="text-xs text-sky-200/90">{{ auth()->user()->role->shortLabelVi() }}</p>
            </div>
        </a>
    </div>

    <nav class="flex-1 space-y-2 px-4 py-5 text-sm">
        @if (auth()->user()->role->value !== 'customer')
            <a href="{{ route(auth()->user()->role->dashboardRouteName()) }}"
                class="block rounded-lg px-3 py-2 {{ request()->routeIs('*.dashboard') ? 'bg-white/20 font-semibold text-white' : 'text-sky-100 hover:bg-white/10 hover:text-white' }}">
                {{ __('Bảng điều khiển') }}
            </a>
        @else
            <a href="{{ route('home') }}"
                class="block rounded-lg px-3 py-2 {{ request()->routeIs('home', 'public.hotels.show') ? 'bg-white/20 font-semibold text-white' : 'text-sky-100 hover:bg-white/10 hover:text-white' }}">
                {{ __('Trang chủ') }}
            </a>
        @endif

        @if (auth()->user()->role->value === 'admin')
            <a href="{{ route('admin.overview') }}"
                class="block rounded-lg px-3 py-2 {{ request()->routeIs('admin.overview') ? 'bg-white/20 font-semibold text-white' : 'text-sky-100 hover:bg-white/10 hover:text-white' }}">
                {{ __('Tổng quan hệ thống') }}
            </a>
        @endif

        @if (auth()->user()->role->value === 'host')
            <a href="{{ route('host.hotels.index') }}"
                class="block rounded-lg px-3 py-2 {{ request()->routeIs('host.hotels.index', 'host.hotels.create', 'host.hotels.edit', 'host.hotels.show') ? 'bg-white/20 font-semibold text-white' : 'text-sky-100 hover:bg-white/10 hover:text-white' }}">
                {{ __('Khách sạn') }}
            </a>
            <a href="{{ route('host.rooms.index') }}"
                class="block rounded-lg px-3 py-2 {{ request()->routeIs('host.rooms.*') ? 'bg-white/20 font-semibold text-white' : 'text-sky-100 hover:bg-white/10 hover:text-white' }}">
                {{ __('Phòng') }}
            </a>
            <a href="{{ route('host.bookings.index') }}"
                class="block rounded-lg px-3 py-2 {{ request()->routeIs('host.bookings.*') ? 'bg-white/20 font-semibold text-white' : 'text-sky-100 hover:bg-white/10 hover:text-white' }}">
                {{ __('Đơn đặt') }}
            </a>
        @endif

        @if (auth()->user()->role->value === 'staff')
            <a href="{{ route('staff.bookings.pending') }}"
                class="block rounded-lg px-3 py-2 {{ request()->routeIs('staff.bookings.pending') ? 'bg-white/20 font-semibold text-white' : 'text-sky-100 hover:bg-white/10 hover:text-white' }}">
                {{ __('Chờ xử lý') }}
            </a>
            <a href="{{ route('staff.bookings.index') }}"
                class="block rounded-lg px-3 py-2 {{ request()->routeIs('staff.bookings.index') ? 'bg-white/20 font-semibold text-white' : 'text-sky-100 hover:bg-white/10 hover:text-white' }}">
                {{ __('Tất cả đơn đặt') }}
            </a>
            <a href="{{ route('staff.bookings.history') }}"
                class="block rounded-lg px-3 py-2 {{ request()->routeIs('staff.bookings.history') ? 'bg-white/20 font-semibold text-white' : 'text-sky-100 hover:bg-white/10 hover:text-white' }}">
                {{ __('Lịch sử') }}
            </a>
        @endif

        @if (auth()->user()->role->value === 'customer')
            <a href="{{ route('customer.bookings.index') }}"
                class="block rounded-lg px-3 py-2 {{ request()->routeIs('customer.bookings.index') ? 'bg-white/20 font-semibold text-white' : 'text-sky-100 hover:bg-white/10 hover:text-white' }}">
                {{ __('Đơn đặt của tôi') }}
            </a>
            <a href="{{ route('customer.bookings.cancellable') }}"
                class="block rounded-lg px-3 py-2 {{ request()->routeIs('customer.bookings.cancellable') ? 'bg-white/20 font-semibold text-white' : 'text-sky-100 hover:bg-white/10 hover:text-white' }}">
                {{ __('Có thể hủy') }}
            </a>
            <a href="{{ route('customer.bookings.rebook') }}"
                class="block rounded-lg px-3 py-2 {{ request()->routeIs('customer.bookings.rebook') ? 'bg-white/20 font-semibold text-white' : 'text-sky-100 hover:bg-white/10 hover:text-white' }}">
                {{ __('Đặt lại') }}
            </a>
        @endif
    </nav>
</aside>

