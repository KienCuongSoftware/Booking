<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-bcom-navy leading-tight">{{ __('Bảng điều khiển') }} — {{ __('Quản trị') }}</h2>
    </x-slot>
    <div class="py-10 px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-6">
            <x-flash-status />
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="h-full rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-gray-500">{{ __('Người dùng') }}</p>
                    <p class="mt-2 text-3xl font-bold text-bcom-navy">{{ $stats['users'] }}</p>
                    <a href="{{ route('admin.users.index') }}" class="mt-2 inline-block text-sm font-semibold text-bcom-blue hover:underline">{{ __('Quản lý') }}</a>
                </div>
                <div class="h-full rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-gray-500">{{ __('Khách sạn') }}</p>
                    <p class="mt-2 text-3xl font-bold text-bcom-navy">{{ $stats['hotels'] }}</p>
                    <a href="{{ route('admin.hotels.index') }}" class="mt-2 inline-block text-sm font-semibold text-bcom-blue hover:underline">{{ __('Xem') }}</a>
                </div>
                <div class="h-full rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-gray-500">{{ __('Đơn đặt') }}</p>
                    <p class="mt-2 text-3xl font-bold text-bcom-navy">{{ $stats['bookings'] }}</p>
                    <a href="{{ route('admin.bookings.index') }}" class="mt-2 inline-block text-sm font-semibold text-bcom-blue hover:underline">{{ __('Xem') }}</a>
                </div>
                <div class="h-full rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-gray-500">{{ __('Hiệu suất 6 tháng') }}</p>
                    <p class="mt-2 text-3xl font-bold text-emerald-700">{{ $stats['completion_rate_6m'] }}%</p>
                    <p class="mt-2 text-sm text-gray-500">{{ __('Đơn hoàn tất / tổng đơn trong 6 tháng.') }}</p>
                    <p class="mt-1 text-xs font-medium text-gray-600">{{ __('Đơn mới 30 ngày') }}: {{ number_format((int) $stats['bookings_30d']) }}</p>
                </div>
            </div>

            <div class="grid gap-4 xl:grid-cols-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-2">
                    <div class="mb-4">
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">{{ __('Xu hướng 6 tháng') }}</h3>
                        <p class="mt-1 text-xs text-gray-500">{{ __('Số đơn đặt mới và người dùng mới theo tháng.') }}</p>
                    </div>
                    <div class="h-72">
                        <canvas id="adminTrendsChart"></canvas>
                    </div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="mb-4">
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">{{ __('Cơ cấu trạng thái đơn') }}</h3>
                        <p class="mt-1 text-xs text-gray-500">{{ __('Trong 6 tháng gần nhất.') }}</p>
                    </div>
                    <div class="h-72">
                        <canvas id="adminStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const chartLabels = @json($chartLabels);
        const bookingSeries = @json($bookingSeries);
        const userSeries = @json($userSeries);
        const statusLabels = @json($statusLabels);
        const statusSeries = @json($statusSeries);

        const trendCtx = document.getElementById('adminTrendsChart');
        if (trendCtx) {
            new Chart(trendCtx, {
                type: 'bar',
                data: {
                    labels: chartLabels,
                    datasets: [
                        {
                            label: 'Đơn đặt mới',
                            data: bookingSeries,
                            borderRadius: 8,
                            borderSkipped: false,
                            backgroundColor: 'rgba(0,108,228,0.85)',
                            maxBarThickness: 34,
                            yAxisID: 'yBookings',
                        },
                        {
                            label: 'Người dùng mới',
                            data: userSeries,
                            borderColor: '#16a34a',
                            backgroundColor: 'rgba(22,163,74,0.14)',
                            tension: 0.35,
                            fill: true,
                            pointRadius: 3.5,
                            pointHoverRadius: 5,
                            borderWidth: 2.5,
                            type: 'line',
                            yAxisID: 'yUsers',
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { position: 'bottom', labels: { usePointStyle: true } },
                    },
                    scales: {
                        yBookings: {
                            beginAtZero: true,
                            position: 'left',
                            grid: { color: 'rgba(15,23,42,0.08)' },
                            ticks: { precision: 0 },
                            title: { display: true, text: 'Đơn đặt' },
                        },
                        yUsers: {
                            beginAtZero: true,
                            position: 'right',
                            grid: { drawOnChartArea: false },
                            ticks: { precision: 0 },
                            title: { display: true, text: 'Người dùng mới' },
                        },
                    },
                },
            });
        }

        const statusCtx = document.getElementById('adminStatusChart');
        if (statusCtx) {
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: statusLabels,
                    datasets: [{
                        data: statusSeries,
                        backgroundColor: ['#f59e0b', '#0ea5e9', '#10b981', '#ef4444', '#8b5cf6'],
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
    </script>
</x-app-layout>
