<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-bcom-navy">{{ __('Tổng quan hệ thống') }}</h2></x-slot>
    <div class="py-8 px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-6">
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-950">
                {{ __('Đơn chờ xử lý') }}: <strong>{{ $pendingBookings }}</strong>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="font-semibold text-bcom-navy">{{ __('Đơn theo trạng thái') }}</h3>
                    <ul class="mt-3 space-y-1 text-sm">
                        @foreach ($bookingsByStatus as $status => $count)
                            <li>{{ \App\Enums\BookingStatus::from($status)->labelVi() }}: <strong>{{ $count }}</strong></li>
                        @endforeach
                    </ul>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="font-semibold text-bcom-navy">{{ __('Người dùng theo vai trò') }}</h3>
                    <ul class="mt-3 space-y-1 text-sm">
                        @foreach ($usersByRole as $role => $count)
                            <li>{{ \App\Enums\UserRole::from($role)->shortLabelVi() }}: <strong>{{ $count }}</strong></li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="font-semibold text-bcom-navy">{{ __('Đơn gần đây') }}</h3>
                <ul class="mt-3 divide-y text-sm">
                    @foreach ($recentBookings as $b)
                        <li class="py-2">{{ $b->booking_code }} — {{ $b->customer?->name }} — {{ $b->hotel?->name }}</li>
                    @endforeach
                </ul>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="font-semibold text-bcom-navy">{{ __('Top khách sạn (số đơn)') }}</h3>
                <ol class="mt-3 list-decimal pl-5 text-sm">
                    @foreach ($topHotels as $h)
                        <li>{{ $h->name }} ({{ $h->city }}) — {{ $h->bookings_count }} {{ __('đơn') }}</li>
                    @endforeach
                </ol>
            </div>
        </div>
    </div>
</x-app-layout>
