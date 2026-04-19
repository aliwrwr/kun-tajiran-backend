<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\HomeController;

/*
|--------------------------------------------------------------------------
| API Routes - كن تاجرا
|--------------------------------------------------------------------------
*/

// Public routes
Route::prefix('v1')->group(function () {

    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('register',   [AuthController::class, 'register']);
        Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
        Route::post('login',      [AuthController::class, 'login']);
        Route::post('resend-otp', [AuthController::class, 'resendOtp']);
    });

    // Products (public - anyone can browse)
    Route::prefix('products')->group(function () {
        Route::get('/',           [ProductController::class, 'index']);
        Route::get('featured',    [ProductController::class, 'featured']);
        Route::get('categories',  [ProductController::class, 'categories']);
        Route::get('{id}',        [ProductController::class, 'show']);
    });

    // Iraqi cities list
    Route::get('cities', [OrderController::class, 'cities']);

    // Admin – create user from dashboard (no auth needed for admin panel)
    Route::post('admin/users', [\App\Http\Controllers\Admin\UserController::class, 'storeApi']);

    // Delivery zones with fees (public — used by Flutter for city picker with fee preview)
    Route::get('delivery-zones', [\App\Http\Controllers\Admin\DeliveryZoneController::class, 'zonesJson']);

    // Home screen data (banners + featured + categories + products + settings)
    Route::get('home', [HomeController::class, 'index'])->middleware('auth:api');

    // Protected routes (Reseller only)
    Route::middleware(['auth:api', 'reseller.active'])->group(function () {

        // Profile
        Route::prefix('auth')->group(function () {
            Route::get('profile',          [AuthController::class, 'profile']);
            Route::put('profile',          [AuthController::class, 'updateProfile']);
            Route::put('change-password',  [AuthController::class, 'changePassword']);
            Route::post('logout',          [AuthController::class, 'logout']);
            Route::post('fcm-token',       [AuthController::class, 'updateFcmToken']);
            Route::post('avatar',          [AuthController::class, 'uploadAvatar']);
        });

        // Orders
        Route::prefix('orders')->group(function () {
            Route::get('/',    [OrderController::class, 'index']);
            Route::post('/',   [OrderController::class, 'store']);
            Route::get('{id}', [OrderController::class, 'show']);
        });

        // Wallet
        Route::prefix('wallet')->group(function () {
            Route::get('/',                  [WalletController::class, 'index']);
            Route::post('withdraw',          [WalletController::class, 'requestWithdrawal']);
            Route::get('withdrawals',        [WalletController::class, 'withdrawalHistory']);
        });
    });
});
