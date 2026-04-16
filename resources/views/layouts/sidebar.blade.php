<aside class="flex w-72 shrink-0 flex-col bg-gradient-to-b from-bcom-navy to-bcom-navy-dark text-sky-100 min-h-screen">
    <div class="border-b border-white/15 px-6 py-5">
        <a href="{{ route(auth()->user()->role->dashboardRouteName()) }}" class="flex items-center gap-3">
            <x-application-logo variant="light" class="h-8 w-8" />
            <div>
                <p class="font-semibold text-white">{{ config('app.name', 'Booking') }}</p>
                <p class="text-xs text-sky-200/90">{{ auth()->user()->role->shortLabelVi() }}</p>
            </div>
        </a>
    </div>

    <nav class="flex-1 space-y-2 px-4 py-5 text-sm">
        <x-sidebar.nav-link :href="route(auth()->user()->role->dashboardRouteName())" :active="request()->routeIs('*.dashboard')">
            {{ __('Bảng điều khiển') }}
        </x-sidebar.nav-link>

        @if (auth()->user()->role->value === 'admin')
            <x-sidebar.nav-link :href="route('admin.overview')" :active="request()->routeIs('admin.overview')">
                {{ __('Tổng quan hệ thống') }}
            </x-sidebar.nav-link>
        @endif

        @if (auth()->user()->role->value === 'host')
            <x-sidebar.nav-link :href="route('host.hotels.index')"
                :active="request()->routeIs('host.hotels.index', 'host.hotels.create', 'host.hotels.edit', 'host.hotels.show')">
                {{ __('Khách sạn') }}
            </x-sidebar.nav-link>
            <x-sidebar.nav-link :href="route('host.rooms.index')" :active="request()->routeIs('host.rooms.*')">
                {{ __('Phòng') }}
            </x-sidebar.nav-link>
            <x-sidebar.nav-link :href="route('host.bookings.index')" :active="request()->routeIs('host.bookings.*')">
                {{ __('Đơn đặt') }}
            </x-sidebar.nav-link>
            <x-sidebar.nav-link :href="route('host.availability.index')" :active="request()->routeIs('host.availability.*')">
                {{ __('Lịch khả dụng') }}
            </x-sidebar.nav-link>
            <x-sidebar.nav-link :href="route('host.cancellation-policy.edit')" :active="request()->routeIs('host.cancellation-policy.*')">
                {{ __('Chính sách hủy') }}
            </x-sidebar.nav-link>
            <x-sidebar.nav-link :href="route('host.reports.index')" :active="request()->routeIs('host.reports.*')">
                {{ __('Báo cáo') }}
            </x-sidebar.nav-link>
            <x-sidebar.nav-link :href="route('host.email-templates.index')"
                :active="request()->routeIs('host.email-templates.*', 'host.hotels.email-templates.*')">
                {{ __('Mẫu email') }}
            </x-sidebar.nav-link>
        @endif

        @if (auth()->user()->role->value === 'staff')
            <x-sidebar.nav-link :href="route('staff.bookings.pending')" :active="request()->routeIs('staff.bookings.pending')">
                {{ __('Chờ xử lý') }}
            </x-sidebar.nav-link>
            <x-sidebar.nav-link :href="route('staff.bookings.index')" :active="request()->routeIs('staff.bookings.index')">
                {{ __('Tất cả đơn đặt') }}
            </x-sidebar.nav-link>
            <x-sidebar.nav-link :href="route('staff.bookings.history')" :active="request()->routeIs('staff.bookings.history')">
                {{ __('Lịch sử') }}
            </x-sidebar.nav-link>
        @endif
    </nav>

    <div class="mt-auto border-t border-white/15 px-4 py-4">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="block w-full rounded-lg px-3 py-2.5 text-start text-sm font-medium text-sky-100 transition hover:bg-white/10 hover:text-white">
                {{ __('Đăng xuất') }}
            </button>
        </form>
    </div>
</aside>
