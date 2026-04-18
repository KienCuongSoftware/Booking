<?php

use App\Http\Controllers\Api\V1\AuthTokenController;
use App\Http\Controllers\Api\V1\CustomerBookingApiController;
use App\Http\Controllers\Api\V1\HotelCatalogApiController;
use App\Http\Controllers\Api\V1\MeController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', fn () => response()->json(['ok' => true]))->name('api.v1.health');

    Route::get('/hotels', [HotelCatalogApiController::class, 'index'])->name('api.v1.hotels.index');
    Route::get('/hotels/{hotel:slug}', [HotelCatalogApiController::class, 'show'])->name('api.v1.hotels.show');

    Route::post('/auth/token', [AuthTokenController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('api.v1.auth.token');

    Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function (): void {
        Route::get('/me', [MeController::class, 'show'])->name('api.v1.me');
        Route::get('/my-bookings', [CustomerBookingApiController::class, 'index'])->name('api.v1.my-bookings');
    });
});
