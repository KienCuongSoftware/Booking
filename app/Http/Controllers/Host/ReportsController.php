<?php

namespace App\Http\Controllers\Host;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\RoomType;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportsController extends Controller
{
    public function index(Request $request): View
    {
        $hostId = $request->user()->id;

        $hotelIds = DB::table('hotels')->where('host_id', $hostId)->pluck('id');

        if ($hotelIds->isEmpty()) {
            return view('host.reports.index', array_merge($this->emptyPayload(), [
                'showCompare' => $request->boolean('compare'),
            ]));
        }

        $revenue = (float) Booking::query()
            ->whereIn('hotel_id', $hotelIds)
            ->whereIn('status', [BookingStatus::Confirmed->value, BookingStatus::Completed->value])
            ->sum('total_price');

        $totalBookings = (int) Booking::query()->whereIn('hotel_id', $hotelIds)->count();
        $cancelled = (int) Booking::query()->whereIn('hotel_id', $hotelIds)->where('status', BookingStatus::Cancelled->value)->count();
        $noShow = (int) Booking::query()->whereIn('hotel_id', $hotelIds)->where('status', BookingStatus::NoShow->value)->count();

        $cancelRate = $totalBookings > 0 ? round(($cancelled / $totalBookings) * 100, 1) : 0.0;
        $noShowRate = $totalBookings > 0 ? round(($noShow / $totalBookings) * 100, 1) : 0.0;

        $monthsCount = 6;
        $start = now()->copy()->subMonths($monthsCount - 1)->startOfMonth();
        $end = now()->copy()->endOfMonth();

        $current = $this->buildMonthlyChart($hotelIds, $start, $monthsCount);

        $showCompare = $request->boolean('compare');
        $compare = null;
        if ($showCompare) {
            $prevStart = $start->copy()->subMonths($monthsCount);
            $compare = $this->buildMonthlyChart($hotelIds, $prevStart, $monthsCount);
        }

        $topRoomTypes = RoomType::query()
            ->whereIn('hotel_id', $hotelIds)
            ->withCount(['bookings as bookings_count' => function ($q): void {
                $q->whereIn('status', [BookingStatus::Confirmed->value, BookingStatus::Completed->value, BookingStatus::Pending->value]);
            }])
            ->orderByDesc('bookings_count')
            ->limit(5)
            ->get(['id', 'hotel_id', 'name']);

        return view('host.reports.index', array_merge(
            compact(
                'revenue',
                'cancelRate',
                'noShowRate',
                'topRoomTypes',
                'totalBookings',
                'showCompare',
            ),
            $current,
            [
                'compareChartLabels' => $compare['chartLabels'] ?? [],
                'compareChartRevenueSeries' => $compare['chartRevenueSeries'] ?? [],
                'compareChartCancelRateSeries' => $compare['chartCancelRateSeries'] ?? [],
                'compareChartNoShowRateSeries' => $compare['chartNoShowRateSeries'] ?? [],
            ],
        ));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $hostId = $request->user()->id;
        $hotelIds = DB::table('hotels')->where('host_id', $hostId)->pluck('id');

        $monthsCount = 6;
        $start = now()->copy()->subMonths($monthsCount - 1)->startOfMonth();
        $end = now()->copy()->endOfMonth();
        $chart = $this->buildMonthlyChart($hotelIds, $start, $monthsCount);

        $filename = 'bao-cao-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($hotelIds, $chart, $start, $end): void {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Tháng', 'Doanh thu (VND)', 'Tỉ lệ hủy (%)', 'Tỉ lệ no-show (%)']);

            foreach ($chart['chartLabels'] as $i => $label) {
                fputcsv($out, [
                    $label,
                    (string) ($chart['chartRevenueSeries'][$i] ?? 0),
                    (string) ($chart['chartCancelRateSeries'][$i] ?? 0),
                    (string) ($chart['chartNoShowRateSeries'][$i] ?? 0),
                ]);
            }

            fputcsv($out, []);
            fputcsv($out, ['Chi tiết đơn', 'Từ', $start->toDateString(), 'Đến', $end->toDateString()]);

            $bookings = Booking::query()
                ->whereIn('hotel_id', $hotelIds)
                ->whereBetween('created_at', [$start, $end])
                ->with(['customer:id,name,email', 'hotel:id,name', 'roomType:id,name'])
                ->orderBy('id')
                ->get();

            fputcsv($out, ['Mã đơn', 'Ngày tạo', 'Khách sạn', 'Phòng', 'Khách', 'Email', 'Trạng thái', 'Tổng tiền']);

            foreach ($bookings as $b) {
                fputcsv($out, [
                    $b->booking_code,
                    $b->created_at?->format('Y-m-d H:i'),
                    $b->hotel?->name,
                    $b->roomType?->name,
                    $b->customer?->name,
                    $b->customer?->email,
                    $b->status instanceof BookingStatus ? $b->status->value : (string) $b->status,
                    (string) $b->total_price,
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportPdf(Request $request): Response
    {
        $hostId = $request->user()->id;
        $hotelIds = DB::table('hotels')->where('host_id', $hostId)->pluck('id');

        $monthsCount = 6;
        $start = now()->copy()->subMonths($monthsCount - 1)->startOfMonth();
        $end = now()->copy()->endOfMonth();
        $chart = $this->buildMonthlyChart($hotelIds, $start, $monthsCount);

        $bookings = Booking::query()
            ->whereIn('hotel_id', $hotelIds)
            ->whereBetween('created_at', [$start, $end])
            ->with(['hotel:id,name', 'roomType:id,name'])
            ->orderBy('id')
            ->get();

        $pdf = Pdf::loadView('host.reports.pdf', [
            'chartLabels' => $chart['chartLabels'],
            'chartRevenueSeries' => $chart['chartRevenueSeries'],
            'chartCancelRateSeries' => $chart['chartCancelRateSeries'],
            'chartNoShowRateSeries' => $chart['chartNoShowRateSeries'],
            'bookings' => $bookings,
            'generatedAt' => now()->timezone(config('app.timezone'))->format('d/m/Y H:i'),
        ]);

        $filename = 'bao-cao-'.now()->format('Y-m-d').'.pdf';

        return $pdf->download($filename);
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyPayload(): array
    {
        return [
            'revenue' => 0.0,
            'cancelRate' => 0.0,
            'noShowRate' => 0.0,
            'topRoomTypes' => collect(),
            'totalBookings' => 0,
            'chartLabels' => [],
            'chartRevenueSeries' => [],
            'chartCancelRateSeries' => [],
            'chartNoShowRateSeries' => [],
            'showCompare' => false,
            'compareChartLabels' => [],
            'compareChartRevenueSeries' => [],
            'compareChartCancelRateSeries' => [],
            'compareChartNoShowRateSeries' => [],
            'showCompare' => false,
        ];
    }

    /**
     * @param  Collection<int, mixed>|array<int, mixed>  $hotelIds
     * @return array{chartLabels: array<int, string>, chartRevenueSeries: array<int, float>, chartCancelRateSeries: array<int, float>, chartNoShowRateSeries: array<int, float>}
     */
    private function buildMonthlyChart(Collection|array $hotelIds, Carbon $periodStart, int $monthsCount): array
    {
        $hotelIds = $hotelIds instanceof Collection ? $hotelIds : collect($hotelIds);
        if ($hotelIds->isEmpty()) {
            return [
                'chartLabels' => [],
                'chartRevenueSeries' => [],
                'chartCancelRateSeries' => [],
                'chartNoShowRateSeries' => [],
            ];
        }

        $start = $periodStart->copy()->startOfMonth();
        $end = $periodStart->copy()->addMonths($monthsCount - 1)->endOfMonth();

        $monthKeys = [];
        $chartLabels = [];
        $bucket = [];

        for ($i = 0; $i < $monthsCount; $i++) {
            $m = $start->copy()->addMonths($i);
            $key = $m->format('Y-m');
            $monthKeys[] = $key;
            $chartLabels[] = $m->format('M/Y');
            $bucket[$key] = [
                'revenue' => 0.0,
                'cancelled' => 0,
                'no_show' => 0,
                'total' => 0,
            ];
        }

        $bookingsForChart = Booking::query()
            ->whereIn('hotel_id', $hotelIds)
            ->whereBetween('created_at', [$start, $end])
            ->get(['created_at', 'status', 'total_price']);

        foreach ($bookingsForChart as $b) {
            $key = Carbon::parse($b->created_at)->format('Y-m');
            if (! isset($bucket[$key])) {
                continue;
            }

            $status = $b->status instanceof BookingStatus
                ? $b->status
                : BookingStatus::tryFrom((string) $b->status) ?? BookingStatus::Pending;

            $bucket[$key]['total']++;

            if ($status === BookingStatus::Cancelled) {
                $bucket[$key]['cancelled']++;
            }

            if ($status === BookingStatus::NoShow) {
                $bucket[$key]['no_show']++;
            }

            if (in_array($status, [BookingStatus::Confirmed, BookingStatus::Completed], true)) {
                $bucket[$key]['revenue'] += (float) $b->total_price;
            }
        }

        $chartRevenueSeries = [];
        $chartCancelRateSeries = [];
        $chartNoShowRateSeries = [];
        foreach ($monthKeys as $key) {
            $chartRevenueSeries[] = (float) $bucket[$key]['revenue'];

            $total = (int) $bucket[$key]['total'];
            $chartCancelRateSeries[] = $total > 0 ? round(($bucket[$key]['cancelled'] / $total) * 100, 1) : 0.0;
            $chartNoShowRateSeries[] = $total > 0 ? round(($bucket[$key]['no_show'] / $total) * 100, 1) : 0.0;
        }

        return compact('chartLabels', 'chartRevenueSeries', 'chartCancelRateSeries', 'chartNoShowRateSeries');
    }
}
