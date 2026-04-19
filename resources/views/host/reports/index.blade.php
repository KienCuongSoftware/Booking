<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Báo cáo vận hành') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl space-y-6 sm:px-6 lg:px-8">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Doanh thu (đơn đã xác nhận/hoàn tất)') }}</p>
                    <p class="mt-2 text-2xl font-bold text-bcom-navy">{{ number_format($revenue, 0, ',', '.') }} VND</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Tỉ lệ hủy') }}</p>
                    <p class="mt-2 text-2xl font-bold text-amber-800">{{ $cancelRate }}%</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Tỉ lệ no-show') }}</p>
                    <p class="mt-2 text-2xl font-bold text-rose-800">{{ $noShowRate }}%</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Tổng đơn') }}</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ $totalBookings }}</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm">
                <a href="{{ route('host.reports.export.csv') }}" class="font-semibold text-bcom-blue hover:underline">{{ __('Xuất CSV (6 tháng)') }}</a>
                <span class="text-gray-300">|</span>
                @if ($showCompare ?? false)
                    <a href="{{ route('host.reports.index') }}" class="font-semibold text-gray-600 hover:underline">{{ __('Tắt so sánh kỳ') }}</a>
                @else
                    <a href="{{ route('host.reports.index', ['compare' => 1]) }}" class="font-semibold text-bcom-blue hover:underline">{{ __('So sánh với 6 tháng trước') }}</a>
                @endif
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 bg-sky-50/60 px-6 py-4">
                        <h3 class="text-lg font-semibold text-bcom-navy">{{ __('Doanh thu theo tháng') }}</h3>
                        <p class="mt-1 text-sm text-gray-600">{{ __('Đơn xác nhận/hoàn tất') }}</p>
                    </div>
                    <div class="p-6">
                        <div class="relative h-72 w-full">
                            <canvas id="revenueBar" class="block w-full"></canvas>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 bg-sky-50/60 px-6 py-4">
                        <h3 class="text-lg font-semibold text-bcom-navy">{{ __('Tỉ lệ hủy & no-show theo tháng') }}</h3>
                        <p class="mt-1 text-sm text-gray-600">{{ __('Tính theo tổng đơn tạo trong tháng') }}</p>
                    </div>
                    <div class="p-6">
                        <div class="relative h-72 w-full">
                            <canvas id="ratesLine" class="block w-full"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                const chartLabels = @json($chartLabels ?? []);
                const revenueSeries = @json($chartRevenueSeries ?? []);
                const cancelRateSeries = @json($chartCancelRateSeries ?? []);
                const noShowRateSeries = @json($chartNoShowRateSeries ?? []);

                const formatVnd = (v) => {
                    try {
                        return new Intl.NumberFormat('vi-VN').format(v) + ' VND';
                    } catch (e) {
                        return v + ' VND';
                    }
                };

                // Bar: revenue
                const revenueCtx = document.getElementById('revenueBar');
                if (revenueCtx) {
                    new Chart(revenueCtx, {
                        type: 'bar',
                        data: {
                            labels: chartLabels,
                            datasets: [{
                                label: 'Doanh thu (VND)',
                                data: revenueSeries,
                                backgroundColor: 'rgba(14, 165, 233, 0.35)',
                                borderColor: 'rgb(14, 165, 233)',
                                borderWidth: 1.5,
                                borderRadius: 10,
                                barPercentage: 0.7,
                                categoryPercentage: 0.75
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            animation: { duration: 400 },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: { color: 'rgba(15, 23, 42, 0.08)' },
                                    ticks: {
                                        font: { size: 12 },
                                        callback: (value) => formatVnd(Number(value) || 0).replace(' VND','')
                                    }
                                },
                                x: {
                                    grid: { display: false },
                                    ticks: {
                                        font: { size: 12 },
                                        maxTicksLimit: 6,
                                        autoSkip: true
                                    }
                                }
                            },
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    mode: 'index',
                                    intersect: false,
                                    callbacks: {
                                        label: (ctx) => formatVnd(ctx.parsed.y ?? 0)
                                    }
                                }
                            }
                        }
                    });
                }

                // Line: cancellation & no-show rates
                const ratesCtx = document.getElementById('ratesLine');
                if (ratesCtx) {
                    new Chart(ratesCtx, {
                        type: 'line',
                        data: {
                            labels: chartLabels,
                            datasets: [
                                {
                                    label: 'Tỉ lệ hủy (%)',
                                    data: cancelRateSeries,
                                    borderColor: 'rgb(245, 158, 11)',
                                    backgroundColor: 'rgba(245, 158, 11, 0.18)',
                                    tension: 0.35,
                                    pointRadius: 4,
                                    pointHoverRadius: 5,
                                    fill: true
                                },
                                {
                                    label: 'Tỉ lệ no-show (%)',
                                    data: noShowRateSeries,
                                    borderColor: 'rgb(244, 63, 94)',
                                    backgroundColor: 'rgba(244, 63, 94, 0.15)',
                                    tension: 0.35,
                                    pointRadius: 4,
                                    pointHoverRadius: 5,
                                    fill: true
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            animation: { duration: 400 },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    max: 100,
                                    grid: { color: 'rgba(15, 23, 42, 0.08)' },
                                    ticks: {
                                        font: { size: 12 },
                                        callback: (value) => value + '%'
                                    }
                                },
                                x: {
                                    grid: { display: false },
                                    ticks: {
                                        font: { size: 12 },
                                        maxTicksLimit: 6,
                                        autoSkip: true
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    display: true,
                                    labels: { boxWidth: 12, boxHeight: 12, font: { size: 12 } }
                                }
                            }
                        }
                    });
                }
            </script>

            @if (! empty($showCompare))
                <p class="text-sm font-semibold text-bcom-navy">{{ __('So sánh: 6 tháng trước') }}</p>
                <div class="grid gap-4 lg:grid-cols-2">
                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-200 bg-slate-50 px-6 py-3 text-sm font-semibold text-gray-800">{{ __('Doanh thu (kỳ trước)') }}</div>
                        <div class="p-6"><div class="relative h-56 w-full"><canvas id="compareRevenueBar"></canvas></div></div>
                    </div>
                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-200 bg-slate-50 px-6 py-3 text-sm font-semibold text-gray-800">{{ __('Tỉ lệ hủy & no-show (kỳ trước)') }}</div>
                        <div class="p-6"><div class="relative h-56 w-full"><canvas id="compareRatesLine"></canvas></div></div>
                    </div>
                </div>
                <script>
                    (function () {
                        const cLabels = @json($compareChartLabels ?? []);
                        const cRev = @json($compareChartRevenueSeries ?? []);
                        const cCan = @json($compareChartCancelRateSeries ?? []);
                        const cNs = @json($compareChartNoShowRateSeries ?? []);
                        const el1 = document.getElementById('compareRevenueBar');
                        if (el1 && typeof Chart !== 'undefined') {
                            new Chart(el1, {
                                type: 'bar',
                                data: { labels: cLabels, datasets: [{ label: 'VND', data: cRev, backgroundColor: 'rgba(100,116,139,0.35)', borderColor: 'rgb(100,116,139)', borderWidth: 1 }] },
                                options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
                            });
                        }
                        const el2 = document.getElementById('compareRatesLine');
                        if (el2 && typeof Chart !== 'undefined') {
                            new Chart(el2, {
                                type: 'line',
                                data: {
                                    labels: cLabels,
                                    datasets: [
                                        { label: 'Hủy %', data: cCan, borderColor: 'rgb(245,158,11)', tension: 0.3, fill: false },
                                        { label: 'No-show %', data: cNs, borderColor: 'rgb(244,63,94)', tension: 0.3, fill: false }
                                    ]
                                },
                                options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, max: 100 } } }
                            });
                        }
                    })();
                </script>
            @endif

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 bg-sky-50/60 px-6 py-4">
                    <h3 class="text-lg font-semibold text-bcom-navy">{{ __('Top loại phòng (theo số đơn)') }}</h3>
                </div>
                <div class="p-6">
                    @if ($topRoomTypes->isEmpty())
                        <p class="text-sm text-gray-600">{{ __('Chưa có dữ liệu.') }}</p>
                    @else
                        <ol class="list-decimal space-y-2 pl-5 text-sm text-gray-800">
                            @foreach ($topRoomTypes as $rt)
                                <li>
                                    <span class="font-medium">{{ $rt->name }}</span>
                                    <span class="text-gray-500">— {{ $rt->bookings_count }} {{ __('đơn') }}</span>
                                </li>
                            @endforeach
                        </ol>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
