<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\SystemOverviewController as AdminSystemOverviewController;
use App\Http\Controllers\Customer\BookingController as CustomerBookingController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Host\BookingController as HostBookingController;
use App\Http\Controllers\Host\DashboardController as HostDashboardController;
use App\Http\Controllers\Host\HotelController as HostHotelController;
use App\Http\Controllers\Host\RoomController as HostRoomController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Staff\BookingController as StaffBookingController;
use App\Http\Controllers\Staff\DashboardController as StaffDashboardController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route(Auth::user()->role->dashboardRouteName());
    }

    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return redirect()->route(Auth::user()->role->dashboardRouteName());
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
    Route::get('/overview', AdminSystemOverviewController::class)->name('overview');
});

Route::middleware(['auth', 'verified', 'role:host'])->prefix('host')->name('host.')->group(function () {
    Route::get('/dashboard', HostDashboardController::class)->name('dashboard');
    Route::get('/hotels', [HostHotelController::class, 'index'])->name('hotels.index');
    Route::get('/rooms', [HostRoomController::class, 'index'])->name('rooms.index');
    Route::get('/bookings', [HostBookingController::class, 'index'])->name('bookings.index');
});

Route::middleware(['auth', 'verified', 'role:staff'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard', StaffDashboardController::class)->name('dashboard');
    Route::get('/bookings', [StaffBookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/pending', [StaffBookingController::class, 'pending'])->name('bookings.pending');
    Route::get('/bookings/history', [StaffBookingController::class, 'history'])->name('bookings.history');
});

Route::middleware(['auth', 'verified', 'role:customer'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/dashboard', CustomerDashboardController::class)->name('dashboard');
    Route::get('/bookings', [CustomerBookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/cancellable', [CustomerBookingController::class, 'cancellable'])->name('bookings.cancellable');
    Route::get('/bookings/rebook', [CustomerBookingController::class, 'rebook'])->name('bookings.rebook');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
