<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __invoke(): View
    {
        $settings = [
            'BOOKING_DYNAMIC_PRICING_ENABLED' => config('booking.dynamic_pricing.enabled'),
            'BOOKING_HOLD_ENABLED' => config('booking.hold.enabled'),
            'BOOKING_HOLD_MINUTES' => config('booking.hold.minutes'),
            'BOOKING_PAYPAL_ENABLED' => config('booking.payments.paypal.enabled'),
            'BOOKING_MOMO_WEBHOOK_ENABLED' => config('booking.payments.momo_webhook.enabled'),
            'BOOKING_REMINDERS_ENABLED' => config('booking.reminders.enabled'),
            'BOOKING_AUDIT_ENABLED' => config('booking.audit.enabled'),
            'BOOKING_IDEMPOTENCY_ENABLED' => config('booking.idempotency.enabled'),
            'BOOKING_PENDING_SLA_ENABLED' => config('booking.pending_sla.enabled'),
            'BOOKING_PENDING_SLA_HOURS' => config('booking.pending_sla.hours'),
        ];

        return view('admin.settings', compact('settings'));
    }
}
