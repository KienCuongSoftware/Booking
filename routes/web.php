<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\SystemOverviewController as AdminSystemOverviewController;
use App\Http\Controllers\Customer\BookingController as CustomerBookingController;
use App\Http\Controllers\Customer\BookingPassController as CustomerBookingPassController;
use App\Http\Controllers\Customer\BookingPaymentController as CustomerBookingPaymentController;
use App\Http\Controllers\Customer\ReviewController as CustomerReviewController;
use App\Http\Controllers\Customer\WaitlistController as CustomerWaitlistController;
use App\Http\Controllers\Host\AvailabilityController as HostAvailabilityController;
use App\Http\Controllers\Host\BookingCheckInController as HostBookingCheckInController;
use App\Http\Controllers\Host\BookingController as HostBookingController;
use App\Http\Controllers\Host\CancellationPolicyController as HostCancellationPolicyController;
use App\Http\Controllers\Host\DashboardController as HostDashboardController;
use App\Http\Controllers\Host\HotelController as HostHotelController;
use App\Http\Controllers\Host\HotelEmailTemplateController as HostHotelEmailTemplateController;
use App\Http\Controllers\Host\ReportsController as HostReportsController;
use App\Http\Controllers\Host\RoomTypeController as HostRoomTypeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\HotelCatalogController;
use App\Http\Controllers\Staff\BookingController as StaffBookingController;
use App\Http\Controllers\Staff\DashboardController as StaffDashboardController;
use App\Http\Controllers\Webhooks\MoMoWebhookController;
use App\Http\Controllers\Webhooks\PayPalWebhookController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', [HotelCatalogController::class, 'index'])->name('home');
Route::get('/hotels/{hotel:slug}', [HotelCatalogController::class, 'show'])->name('public.hotels.show');

Route::post('/webhooks/paypal', PayPalWebhookController::class)->name('webhooks.paypal');
Route::post('/webhooks/momo', MoMoWebhookController::class)->name('webhooks.momo');

Route::get('/dashboard', function () {
    return redirect()->route(Auth::user()->role->redirectRouteAfterAuthentication());
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->get('/check-in', [HostBookingCheckInController::class, 'entry'])->name('check-in.entry');

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
    Route::get('/overview', AdminSystemOverviewController::class)->name('overview');
});

Route::middleware(['auth', 'verified', 'role:host'])->prefix('host')->name('host.')->group(function () {
    Route::get('/dashboard', HostDashboardController::class)->name('dashboard');
    Route::get('/email-templates', [HostHotelEmailTemplateController::class, 'index'])->name('email-templates.index');
    Route::resource('hotels', HostHotelController::class);
    Route::get('/hotels/{hotel}/email-templates', [HostHotelEmailTemplateController::class, 'edit'])->name('hotels.email-templates.edit');
    Route::put('/hotels/{hotel}/email-templates', [HostHotelEmailTemplateController::class, 'update'])->name('hotels.email-templates.update');
    Route::get('/rooms', [HostRoomTypeController::class, 'index'])->name('rooms.index');
    Route::get('/hotels/{hotel}/room-types/create', [HostRoomTypeController::class, 'create'])->name('hotels.room-types.create');
    Route::post('/hotels/{hotel}/room-types', [HostRoomTypeController::class, 'store'])->name('hotels.room-types.store');
    Route::get('/room-types/{roomType}/edit', [HostRoomTypeController::class, 'edit'])->name('room-types.edit');
    Route::put('/room-types/{roomType}', [HostRoomTypeController::class, 'update'])->name('room-types.update');
    Route::delete('/room-types/{roomType}', [HostRoomTypeController::class, 'destroy'])->name('room-types.destroy');
    Route::get('/bookings', [HostBookingController::class, 'index'])->name('bookings.index');
    Route::patch('/bookings/{booking}/status', [HostBookingController::class, 'updateStatus'])->name('bookings.update-status');
    Route::patch('/bookings/{booking}/refunds/{transaction}', [HostBookingController::class, 'updateRefundStatus'])->name('bookings.update-refund-status');
    Route::get('/availability', [HostAvailabilityController::class, 'index'])->name('availability.index');
    Route::get('/cancellation-policy', [HostCancellationPolicyController::class, 'edit'])->name('cancellation-policy.edit');
    Route::put('/cancellation-policy', [HostCancellationPolicyController::class, 'update'])->name('cancellation-policy.update');
    Route::get('/reports', HostReportsController::class)->name('reports.index');
    Route::get('/check-in', [HostBookingCheckInController::class, 'preview'])->name('bookings.check-in.preview');
    Route::post('/check-in/confirm', [HostBookingCheckInController::class, 'confirm'])->name('bookings.check-in.confirm');
    Route::post('/bookings/{booking}/check-in', [HostBookingCheckInController::class, 'store'])->name('bookings.check-in');
});

Route::middleware(['auth', 'verified', 'role:staff'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard', StaffDashboardController::class)->name('dashboard');
    Route::get('/bookings', [StaffBookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/pending', [StaffBookingController::class, 'pending'])->name('bookings.pending');
    Route::get('/bookings/history', [StaffBookingController::class, 'history'])->name('bookings.history');
});

Route::middleware(['auth', 'verified', 'role:customer'])->prefix('customer')->name('customer.')->group(function () {
    Route::redirect('/', '/customer/bookings')->name('home');
    Route::get('/hotels/{hotel}/availability', [CustomerBookingController::class, 'availability'])->name('hotels.availability');
    Route::get('/waitlist', [CustomerWaitlistController::class, 'index'])->name('waitlist.index');
    Route::get('/hotels/{hotel}/waitlist/create', [CustomerWaitlistController::class, 'create'])->name('waitlist.create');
    Route::post('/hotels/{hotel}/waitlist', [CustomerWaitlistController::class, 'store'])->name('waitlist.store');
    Route::post('/hotels/{hotel}/bookings', [CustomerBookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings', [CustomerBookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/cancellable', [CustomerBookingController::class, 'cancellable'])->name('bookings.cancellable');
    Route::patch('/bookings/{booking}/cancel', [CustomerBookingController::class, 'cancel'])->name('bookings.cancel');
    Route::get('/bookings/rebook', [CustomerBookingController::class, 'rebook'])->name('bookings.rebook');
    Route::get('/bookings/pay/paypal/return', [CustomerBookingPaymentController::class, 'paypalReturn'])->name('bookings.pay.paypal.return');
    Route::get('/bookings/pay/cancel/{booking}', [CustomerBookingPaymentController::class, 'cancel'])->name('bookings.pay.cancel');
    Route::get('/bookings/{booking}', [CustomerBookingController::class, 'show'])->name('bookings.show');
    Route::get('/bookings/{booking}/pay/paypal', [CustomerBookingPaymentController::class, 'paypalResume'])->name('bookings.pay.paypal.resume');
    Route::patch('/bookings/{booking}/payment-reference', [CustomerBookingPaymentController::class, 'updatePaymentReference'])->name('bookings.payment-reference.update');
    Route::get('/bookings/{booking}/pass', [CustomerBookingPassController::class, 'show'])->name('bookings.pass');
    Route::get('/bookings/{booking}/review', [CustomerReviewController::class, 'create'])->name('bookings.review.create');
    Route::post('/bookings/{booking}/review', [CustomerReviewController::class, 'store'])->name('bookings.review.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
