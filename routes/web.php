<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DeliveryController;
use App\Http\Controllers\Admin\WithdrawalController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\ProfitReportController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\HomeSettingsController;
use App\Http\Controllers\Admin\DeliveryZoneController;

/*
|--------------------------------------------------------------------------
| Admin Web Routes - كن تاجرا
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => redirect()->route('admin.login'));
Route::get('/admin', fn () => redirect()->route('admin.dashboard'));

// Named alias so Laravel's auth middleware redirect works
Route::get('/login', fn () => redirect()->route('admin.login'))->name('login');

// Admin login
Route::get('/admin/login', function () {
    return view('auth.login');
})->name('admin.login');

Route::post('/admin/login', function (\Illuminate\Http\Request $request) {
    $credentials = $request->validate([
        'phone'    => 'required',
        'password' => 'required',
    ]);

    // Only admins can log into the web panel
    $user = \App\Models\User::where('phone', $credentials['phone'])
        ->where('role', 'admin')
        ->first();

    if (!$user || !\Illuminate\Support\Facades\Hash::check($credentials['password'], $user->password)) {
        return back()->withErrors(['phone' => 'بيانات الدخول غير صحيحة'])->withInput();
    }

    auth()->login($user);
    return redirect()->route('admin.dashboard');
});

Route::post('/admin/logout', function () {
    auth()->logout();
    return redirect()->route('admin.login');
})->name('admin.logout');

// Protected admin routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {

    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Products
    Route::resource('products', ProductController::class);
    Route::post('products/{product}/stock', [ProductController::class, 'adjustStock'])->name('products.stock');

    // Orders
    Route::get('orders',                           [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}',                   [OrderController::class, 'show'])->name('orders.show');
    Route::post('orders/{order}/status',           [OrderController::class, 'updateStatus'])->name('orders.status');
    Route::post('orders/{order}/assign-delivery',  [OrderController::class, 'assignDelivery'])->name('orders.assign');
    Route::delete('orders/{order}',                [OrderController::class, 'destroy'])->name('orders.destroy');
    Route::get('orders/{order}/print',             [OrderController::class, 'printReceipt'])->name('orders.print');

    // Users / Resellers
    Route::get('users',                          [UserController::class, 'index'])->name('users.index');
    Route::get('users/{user}',                   [UserController::class, 'show'])->name('users.show');
    Route::post('users/{user}/toggle-status',    [UserController::class, 'toggleStatus'])->name('users.toggle');
    Route::post('users/{user}/adjust-balance',   [UserController::class, 'adjustBalance'])->name('users.balance');
    Route::delete('users/{user}',                [UserController::class, 'destroy'])->name('users.destroy');

    // Delivery Agents
    Route::get('delivery',                       [DeliveryController::class, 'index'])->name('delivery.index');
    Route::get('delivery/create',                [DeliveryController::class, 'create'])->name('delivery.create');
    Route::post('delivery',                      [DeliveryController::class, 'store'])->name('delivery.store');
    Route::get('delivery/{deliveryAgent}',       [DeliveryController::class, 'show'])->name('delivery.show');
    Route::post('delivery/{deliveryAgent}/status',[DeliveryController::class, 'updateStatus'])->name('delivery.status');

    // Withdrawals
    Route::get('withdrawals',                            [WithdrawalController::class, 'index'])->name('withdrawals.index');
    Route::post('withdrawals/{withdrawal}/process',      [WithdrawalController::class, 'processWithdrawal'])->name('withdrawals.process');

    // Categories
    Route::resource('categories', CategoryController::class);
    Route::post('categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggle-status');

    // Notifications
    Route::get('notifications',   [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications',  [NotificationController::class, 'send'])->name('notifications.send');

    // Profit Reports
    Route::get('profits', [ProfitReportController::class, 'index'])->name('profits.index');

    // Banners
    Route::resource('banners', BannerController::class)->except(['show']);
    Route::post('banners/{banner}/toggle', [BannerController::class, 'toggleStatus'])->name('banners.toggle');

    // Home Settings
    Route::get('home-settings',  [HomeSettingsController::class, 'index'])->name('home-settings.index');
    Route::put('home-settings',  [HomeSettingsController::class, 'update'])->name('home-settings.update');
    Route::get('home-settings/categories-json', [HomeSettingsController::class, 'categoriesJson'])->name('home-settings.categories-json');

    // Delivery Zones & Offers — static segments MUST come before {zone} wildcard
    Route::get('delivery-zones',                              [DeliveryZoneController::class, 'index'])->name('delivery-zones.index');
    Route::post('delivery-zones',                             [DeliveryZoneController::class, 'storeZone'])->name('delivery-zones.store');
    Route::post('delivery-zones/seed',                        [DeliveryZoneController::class, 'seedProvinces'])->name('delivery-zones.seed');
    Route::post('delivery-zones/bulk-update',                 [DeliveryZoneController::class, 'bulkUpdateZones'])->name('delivery-zones.bulk-update');
    Route::put('delivery-zones/{zone}',                       [DeliveryZoneController::class, 'updateZone'])->name('delivery-zones.update');
    Route::delete('delivery-zones/{zone}',                    [DeliveryZoneController::class, 'destroyZone'])->name('delivery-zones.destroy');
    Route::post('delivery-offers',                            [DeliveryZoneController::class, 'storeOffer'])->name('delivery-offers.store');
    Route::put('delivery-offers/{offer}',                     [DeliveryZoneController::class, 'updateOffer'])->name('delivery-offers.update');
    Route::delete('delivery-offers/{offer}',                  [DeliveryZoneController::class, 'destroyOffer'])->name('delivery-offers.destroy');
    Route::post('delivery-offers/{offer}/toggle',             [DeliveryZoneController::class, 'toggleOffer'])->name('delivery-offers.toggle');
});
