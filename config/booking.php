<?php

return [
    'dynamic_pricing' => [
        'enabled' => (bool) env('BOOKING_DYNAMIC_PRICING_ENABLED', true),
    ],

    'hold' => [
        'enabled' => (bool) env('BOOKING_HOLD_ENABLED', true),
        'minutes' => (int) env('BOOKING_HOLD_MINUTES', 20),
    ],

    'payments' => [
        'paypal' => [
            'enabled' => (bool) env('BOOKING_PAYPAL_ENABLED', false),
        ],
        'momo_webhook' => [
            'enabled' => (bool) env('BOOKING_MOMO_WEBHOOK_ENABLED', false),
        ],
    ],

    'reminders' => [
        'enabled' => (bool) env('BOOKING_REMINDERS_ENABLED', true),
    ],

    'audit' => [
        'enabled' => (bool) env('BOOKING_AUDIT_ENABLED', true),
    ],

    'idempotency' => [
        'enabled' => (bool) env('BOOKING_IDEMPOTENCY_ENABLED', true),
    ],

    'pending_sla' => [
        'enabled' => (bool) env('BOOKING_PENDING_SLA_ENABLED', true),
        'hours' => (int) env('BOOKING_PENDING_SLA_HOURS', 24),
    ],
];
