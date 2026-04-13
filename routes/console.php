<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('bookings:send-reminders', function () {
    $sentCount = app(\App\Services\BookingNotificationService::class)->sendUpcomingCheckInReminders();
    $this->info("Sent {$sentCount} booking reminder emails.");
})->purpose('Send check-in reminder emails for confirmed bookings');

Artisan::command('bookings:send-follow-ups', function () {
    $sentCount = app(\App\Services\BookingNotificationService::class)->sendCompletionFollowUps();
    $this->info("Sent {$sentCount} booking follow-up emails.");
})->purpose('Send post-stay follow-up emails for completed bookings');

Schedule::command('bookings:send-reminders')->dailyAt('09:00');
Schedule::command('bookings:send-follow-ups')->dailyAt('18:00');
