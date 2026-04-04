<aside class="w-72 text-red-50 min-h-screen flex flex-col shrink-0" style="background: linear-gradient(180deg, #b91c1c 0%, #991b1b 100%);">
    <div class="px-6 py-5 border-b border-red-500/60">
        <a href="{{ route(auth()->user()->role->dashboardRouteName()) }}" class="flex items-center gap-3">
            <x-application-logo variant="light" class="h-8 w-8" />
            <div>
                <p class="font-semibold text-white">{{ config('app.name', 'Booking') }}</p>
                <p class="text-xs text-red-100">{{ auth()->user()->role->shortLabelVi() }}</p>
            </div>
        </a>
    </div>

    <nav class="flex-1 px-4 py-5 space-y-2 text-sm">
        <a href="{{ route(auth()->user()->role->dashboardRouteName()) }}"
            class="block rounded-lg px-3 py-2 {{ request()->routeIs('*.dashboard') ? 'bg-white/20 text-white font-semibold' : 'hover:bg-white/10 text-red-100' }}">
            {{ __('Bảng điều khiển') }}
        </a>

        @if (auth()->user()->role->value === 'admin')
            <a href="{{ route('admin.overview') }}"
                class="block rounded-lg px-3 py-2 {{ request()->routeIs('admin.overview') ? 'bg-white/20 text-white font-semibold' : 'hover:bg-white/10 text-red-100' }}">
                {{ __('Tổng quan hệ thống') }}
            </a>
        @endif

        @if (auth()->user()->role->value === 'host')
            <a href="{{ route('host.hotels.index') }}"
                class="block rounded-lg px-3 py-2 {{ request()->routeIs('host.hotels.index', 'host.hotels.create', 'host.hotels.edit', 'host.hotels.show') ? 'bg-white/20 text-white font-semibold' : 'hover:bg-white/10 text-red-100' }}">
                {{ __('Khách sạn') }}
            </a>
            <a href="{{ route('host.rooms.index') }}"
                class="block rounded-lg px-3 py-2 {{ request()->routeIs('host.rooms.*') ? 'bg-white/20 text-white font-semibold' : 'hover:bg-white/10 text-red-100' }}">
                {{ __('Phòng') }}
            </a>
            <a href="{{ route('host.bookings.index') }}"
                class="block rounded-lg px-3 py-2 {{ request()->routeIs('host.bookings.*') ? 'bg-white/20 text-white font-semibold' : 'hover:bg-white/10 text-red-100' }}">
                {{ __('Đơn đặt') }}
            </a>
        @endif

        @if (auth()->user()->role->value === 'staff')
            <a href="{{ route('staff.bookings.pending') }}"
                class="block rounded-lg px-3 py-2 {{ request()->routeIs('staff.bookings.pending') ? 'bg-white/20 text-white font-semibold' : 'hover:bg-white/10 text-red-100' }}">
                {{ __('Chờ xử lý') }}
            </a>
            <a href="{{ route('staff.bookings.index') }}"
                class="block rounded-lg px-3 py-2 {{ request()->routeIs('staff.bookings.index') ? 'bg-white/20 text-white font-semibold' : 'hover:bg-white/10 text-red-100' }}">
                {{ __('Tất cả đơn đặt') }}
            </a>
            <a href="{{ route('staff.bookings.history') }}"
                class="block rounded-lg px-3 py-2 {{ request()->routeIs('staff.bookings.history') ? 'bg-white/20 text-white font-semibold' : 'hover:bg-white/10 text-red-100' }}">
                {{ __('Lịch sử') }}
            </a>
        @endif

        @if (auth()->user()->role->value === 'customer')
            <a href="{{ route('customer.bookings.index') }}"
                class="block rounded-lg px-3 py-2 {{ request()->routeIs('customer.bookings.index') ? 'bg-white/20 text-white font-semibold' : 'hover:bg-white/10 text-red-100' }}">
                {{ __('Đơn đặt của tôi') }}
            </a>
            <a href="{{ route('customer.bookings.cancellable') }}"
                class="block rounded-lg px-3 py-2 {{ request()->routeIs('customer.bookings.cancellable') ? 'bg-white/20 text-white font-semibold' : 'hover:bg-white/10 text-red-100' }}">
                {{ __('Có thể hủy') }}
            </a>
            <a href="{{ route('customer.bookings.rebook') }}"
                class="block rounded-lg px-3 py-2 {{ request()->routeIs('customer.bookings.rebook') ? 'bg-white/20 text-white font-semibold' : 'hover:bg-white/10 text-red-100' }}">
                {{ __('Đặt lại') }}
            </a>
        @endif
    </nav>
</aside>

