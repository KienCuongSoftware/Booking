<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-bcom-navy">{{ __('Tổng quan hệ thống') }}</h2>
    </x-slot>
    <div class="py-8 px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-6">
            <form method="GET" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Bộ lọc thời gian cho chart') }}</p>
                        <p class="mt-1 text-sm text-gray-600">{{ __('Dữ liệu biểu đồ hiện theo') }} <strong>{{ $timeWindowDays }} {{ __('ngày') }}</strong>.</p>
                    </div>
                    <div class="inline-flex overflow-hidden rounded-lg border border-slate-200 bg-slate-50 p-1">
                        @foreach ($timeWindowOptions as $opt)
                            <button
                                type="submit"
                                name="days"
                                value="{{ $opt }}"
                                class="rounded-md px-3 py-1.5 text-sm font-semibold {{ (int) $timeWindowDays === (int) $opt ? 'bg-bcom-blue text-white shadow-sm' : 'text-gray-700 hover:bg-white' }}"
                            >
                                {{ $opt }} {{ __('ngày') }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </form>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Tổng đơn') }}</p>
                    <p class="mt-2 text-3xl font-bold text-bcom-navy">{{ number_format($totals['bookings']) }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Đơn chờ xử lý') }}</p>
                    <p class="mt-2 text-3xl font-bold text-amber-700">{{ number_format($totals['pending']) }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Khách sạn') }}</p>
                    <p class="mt-2 text-3xl font-bold text-bcom-navy">{{ number_format($totals['hotels']) }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Người dùng') }}</p>
                    <p class="mt-2 text-3xl font-bold text-bcom-navy">{{ number_format($totals['users']) }}</p>
                </div>
            </div>

            <div class="grid gap-4 xl:grid-cols-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-2">
                    <div class="mb-4">
                        <h3 class="font-semibold text-bcom-navy">{{ __('Đơn theo trạng thái') }}</h3>
                        <p class="mt-1 text-xs text-gray-500">{{ __('Biểu đồ số đơn tạo trong') }} {{ $timeWindowDays }} {{ __('ngày gần nhất, theo trạng thái.') }}</p>
                    </div>
                    <div class="h-80">
                        <canvas id="overviewStatusChart"></canvas>
                    </div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="mb-4">
                        <h3 class="font-semibold text-bcom-navy">{{ __('Người dùng theo vai trò') }}</h3>
                        <p class="mt-1 text-xs text-gray-500">{{ __('Người dùng mới trong') }} {{ $timeWindowDays }} {{ __('ngày, theo vai trò.') }}</p>
                    </div>
                    <div class="h-80">
                        <canvas id="overviewRolesChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="grid gap-4 xl:grid-cols-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-2">
                    <div class="mb-4 flex items-center justify-between gap-2">
                        <h3 class="font-semibold text-bcom-navy">{{ __('Đơn gần đây') }}</h3>
                        <a href="{{ route('admin.bookings.index') }}" class="text-xs font-semibold text-bcom-blue hover:underline">{{ __('Xem tất cả') }}</a>
                    </div>
                    <div class="overflow-hidden rounded-xl border border-slate-100">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-sky-50/70 text-xs font-semibold uppercase tracking-wide text-bcom-navy">
                                <tr>
                                    <th class="px-3 py-2">{{ __('Mã đơn') }}</th>
                                    <th class="px-3 py-2">{{ __('Khách') }}</th>
                                    <th class="px-3 py-2">{{ __('Khách sạn') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($recentBookings as $b)
                                    <tr class="hover:bg-slate-50/70">
                                        <td class="px-3 py-2 font-medium text-bcom-navy">{{ $b->booking_code }}</td>
                                        <td class="px-3 py-2">{{ $b->customer?->name ?? '—' }}</td>
                                        <td class="px-3 py-2">{{ $b->hotel?->name ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-3 py-5 text-center text-sm text-gray-500">{{ __('Chưa có dữ liệu.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="mb-4">
                        <h3 class="font-semibold text-bcom-navy">{{ __('Nhóm đầu khách sạn theo số đơn') }}</h3>
                        <p class="mt-1 text-xs text-gray-500">{{ __('Nhóm 5 trong') }} {{ $timeWindowDays }} {{ __('ngày gần nhất.') }}</p>
                    </div>
                    <div class="h-80">
                        <canvas id="overviewTopHotelsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const statusLabels = @json($statusLabels);
        const statusSeries = @json($statusSeries);
        const roleLabels = @json($roleLabels);
        const roleSeries = @json($roleSeries);
        const topHotelLabels = @json($topHotelLabels);
        const topHotelSeries = @json($topHotelSeries);

        const statusCtx = document.getElementById('overviewStatusChart');
        if (statusCtx) {
            new Chart(statusCtx, {
                type: 'bar',
                data: {
                    labels: statusLabels,
                    datasets: [{
                        label: 'Số đơn',
                        data: statusSeries,
                        backgroundColor: ['#f59e0b', '#0ea5e9', '#10b981', '#ef4444', '#8b5cf6'],
                        borderRadius: 8,
                        borderSkipped: false,
                        maxBarThickness: 50,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0 },
                            grid: { color: 'rgba(15,23,42,0.08)' },
                        },
                    },
                },
            });
        }

        const rolesCtx = document.getElementById('overviewRolesChart');
        if (rolesCtx) {
            new Chart(rolesCtx, {
                type: 'doughnut',
                data: {
                    labels: roleLabels,
                    datasets: [{
                        data: roleSeries,
                        backgroundColor: ['#334155', '#2563eb', '#9333ea', '#14b8a6'],
                        borderWidth: 0,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: {
                        legend: { position: 'bottom', labels: { usePointStyle: true, padding: 16 } },
                    },
                },
            });
        }

        const hotelsCtx = document.getElementById('overviewTopHotelsChart');
        if (hotelsCtx) {
            new Chart(hotelsCtx, {
                type: 'bar',
                data: {
                    labels: topHotelLabels,
                    datasets: [{
                        label: 'Đơn',
                        data: topHotelSeries,
                        backgroundColor: 'rgba(0,108,228,0.85)',
                        borderRadius: 6,
                        borderSkipped: false,
                    }],
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: { precision: 0 },
                            grid: { color: 'rgba(15,23,42,0.08)' },
                        },
                    },
                },
            });
        }
    </script>
</x-app-layout>
