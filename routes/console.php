<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('bookings:send-reminders', function () {
    $result = app(\App\Services\BookingNotificationService::class)->sendAllScheduledReminders();
    $this->info('Sent booking reminders: D-3='.$result['d3'].' | D-1='.$result['d1'].' | H-6='.$result['h6']);
})->purpose('Send check-in reminder emails for confirmed bookings');

Artisan::command('bookings:send-follow-ups', function () {
    $sentCount = app(\App\Services\BookingNotificationService::class)->sendCompletionFollowUps();
    $this->info("Sent {$sentCount} booking follow-up emails.");
})->purpose('Send post-stay follow-up emails for completed bookings');

Artisan::command('bookings:mark-no-show', function () {
    $count = app(\App\Services\BookingNoShowService::class)->markOverdueAsNoShow();
    $this->info("Marked {$count} bookings as no-show.");
})->purpose('Automatically mark overdue confirmed bookings as no-show');

Schedule::command('bookings:send-reminders')->hourly();
Schedule::command('bookings:send-follow-ups')->dailyAt('18:00');
Schedule::command('bookings:mark-no-show')->dailyAt('02:00');
