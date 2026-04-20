<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Hotel;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $months = 6;
        $periodStart = now()->copy()->subMonths($months - 1)->startOfMonth();
        $periodEnd = now()->copy()->endOfMonth();
        $last30Days = now()->copy()->subDays(30);

        $bookingsInWindow = Booking::query()
            ->whereBetween('created_at', [$periodStart, $periodEnd]);
        $completedInWindow = (clone $bookingsInWindow)
            ->where('status', BookingStatus::Completed->value)
            ->count();
        $totalInWindow = (clone $bookingsInWindow)->count();

        $stats = [
            'users' => User::query()->count(),
            'hotels' => Hotel::query()->count(),
            'bookings' => Booking::query()->count(),
            'bookings_30d' => Booking::query()->where('created_at', '>=', $last30Days)->count(),
            'completion_rate_6m' => $totalInWindow > 0
                ? round(($completedInWindow / $totalInWindow) * 100, 1)
                : 0.0,
        ];

        $chartLabels = [];
        $bookingSeries = [];
        $userSeries = [];
        for ($i = 0; $i < $months; $i++) {
            $m = $periodStart->copy()->addMonths($i);
            $chartLabels[] = $m->format('m/Y');
            $bookingSeries[] = Booking::query()
                ->whereBetween('created_at', [$m->copy()->startOfMonth(), $m->copy()->endOfMonth()])
                ->count();
            $userSeries[] = User::query()
                ->whereBetween('created_at', [$m->copy()->startOfMonth(), $m->copy()->endOfMonth()])
                ->count();
        }

        $statusRaw = Booking::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $statusLabels = [
            BookingStatus::Pending->labelVi(),
            BookingStatus::Confirmed->labelVi(),
            BookingStatus::Completed->labelVi(),
            BookingStatus::Cancelled->labelVi(),
            BookingStatus::NoShow->labelVi(),
        ];
        $statusSeries = array_map(
            fn (string $status): int => (int) ($statusRaw[$status] ?? 0),
            [
                BookingStatus::Pending->value,
                BookingStatus::Confirmed->value,
                BookingStatus::Completed->value,
                BookingStatus::Cancelled->value,
                BookingStatus::NoShow->value,
            ],
        );

        return view('admin.dashboard', compact('stats', 'chartLabels', 'bookingSeries', 'userSeries', 'statusLabels', 'statusSeries'));
    }
}
