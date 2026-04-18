<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Hotel;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $stats = [
            'users' => User::query()->count(),
            'hotels' => Hotel::query()->count(),
            'bookings' => Booking::query()->count(),
            'audit_logs_7d' => AuditLog::query()->where('created_at', '>=', now()->subDays(7))->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
