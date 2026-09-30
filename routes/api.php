<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BootstrapController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\MiscController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PasswordResetRequestController;
use App\Http\Controllers\Api\PromoController;
use App\Http\Controllers\Api\StatisticController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Smart Kantin Kampus
|--------------------------------------------------------------------------
| Prefix otomatis: /api  (dicek di D:\uas_mobile_update\docs\api_contract.md)
| Token bersifat opsional; semua endpoint tetap bisa diakses tanpa header
| Authorization supaya Flutter tidak perlu sesi login untuk operasi baca.
*/

Route::post('bootstrap', BootstrapController::class)->name('api.bootstrap');

// ---------- Auth & Users ----------
Route::post('auth/login', [AuthController::class, 'login'])->name('api.auth.login');

Route::get('users', [UserController::class, 'index'])->name('api.users.index');
Route::post('users', [UserController::class, 'store'])->name('api.users.store');
Route::get('users/{id}', [UserController::class, 'show'])->whereNumber('id')->name('api.users.show');
Route::put('users/{id}', [UserController::class, 'update'])->whereNumber('id')->name('api.users.update');
Route::put('users/{id}/password', [UserController::class, 'updatePassword'])->whereNumber('id')->name('api.users.password');

// ---------- Password Reset Requests ----------
Route::post('password-reset-requests', [PasswordResetRequestController::class, 'store'])->name('api.password-reset.store');
Route::get('password-reset-requests', [PasswordResetRequestController::class, 'index'])->name('api.password-reset.index');
Route::put('password-reset-requests/{id}', [PasswordResetRequestController::class, 'resolve'])->whereNumber('id')->name('api.password-reset.resolve');

// ---------- Menus ----------
Route::get('menus', [MenuController::class, 'index'])->name('api.menus.index');
Route::post('menus', [MenuController::class, 'store'])->name('api.menus.store');
Route::put('menus/{id}', [MenuController::class, 'update'])->whereNumber('id')->name('api.menus.update');
Route::delete('menus/{id}', [MenuController::class, 'destroy'])->whereNumber('id')->name('api.menus.destroy');
Route::patch('menus/{id}/availability', [MenuController::class, 'availability'])->whereNumber('id')->name('api.menus.availability');
Route::post('menus/{id}/recalculate-rating', [MenuController::class, 'recalculateRating'])->whereNumber('id')->name('api.menus.recalculate-rating');

// ---------- Promos ----------
// Route statis HARUS didaftarkan sebelum /promos/{id} agar tidak tertangkap.
Route::get('promos/best-active', [PromoController::class, 'bestActive'])->name('api.promos.best-active');
Route::post('promos/sync-notifications', [PromoController::class, 'syncNotifications'])->name('api.promos.sync-notifications');

Route::get('promos', [PromoController::class, 'index'])->name('api.promos.index');
Route::post('promos', [PromoController::class, 'store'])->name('api.promos.store');
Route::put('promos/{id}', [PromoController::class, 'update'])->whereNumber('id')->name('api.promos.update');
Route::delete('promos/{id}', [PromoController::class, 'destroy'])->whereNumber('id')->name('api.promos.destroy');

// ---------- Orders ----------
Route::get('orders', [OrderController::class, 'index'])->name('api.orders.index');
Route::post('orders', [OrderController::class, 'store'])->name('api.orders.store');
Route::get('orders/{id}', [OrderController::class, 'show'])->whereNumber('id')->name('api.orders.show');
Route::put('orders/{id}/status', [OrderController::class, 'updateStatus'])->whereNumber('id')->name('api.orders.status');
Route::post('orders/{id}/confirm-cash-payment', [OrderController::class, 'confirmCashPayment'])->whereNumber('id')->name('api.orders.confirm-cash');
Route::post('orders/{id}/mark-virtual-paid', [OrderController::class, 'markVirtualPaymentPaid'])->whereNumber('id')->name('api.orders.mark-virtual-paid');

// ---------- Notifications ----------
// Route statis sebelum /notifications/{id}.
Route::put('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('api.notifications.read-all');
Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('api.notifications.unread-count');

Route::get('notifications', [NotificationController::class, 'index'])->name('api.notifications.index');
Route::post('notifications', [NotificationController::class, 'store'])->name('api.notifications.store');
Route::delete('notifications', [NotificationController::class, 'destroyAll'])->name('api.notifications.destroy-all');
Route::delete('notifications/{id}', [NotificationController::class, 'destroy'])->whereNumber('id')->name('api.notifications.destroy');

// ---------- Topup, Rating, Favorites, Customer Sync ----------
Route::post('topups', [MiscController::class, 'storeTopup'])->name('api.topups.store');
Route::post('ratings', [MiscController::class, 'storeRating'])->name('api.ratings.store');

Route::get('favorites', [FavoriteController::class, 'index'])->name('api.favorites.index');
Route::post('favorites', [FavoriteController::class, 'store'])->name('api.favorites.store');
Route::delete('favorites', [FavoriteController::class, 'destroy'])->name('api.favorites.destroy');

Route::post('customers/{userId}/sync-notifications', [MiscController::class, 'syncCustomerNotifications'])
    ->whereNumber('userId')
    ->name('api.customers.sync-notifications');

// ---------- Statistik ----------
Route::get('statistics/users', [StatisticController::class, 'userStatistics'])->name('api.statistics.users');
Route::get('statistics/ratings-summary', [StatisticController::class, 'ratingsSummary'])->name('api.statistics.ratings-summary');
Route::get('statistics/daily-sales', [StatisticController::class, 'dailySales'])->name('api.statistics.daily-sales');
Route::get('statistics/monthly-sales', [StatisticController::class, 'monthlySales'])->name('api.statistics.monthly-sales');
Route::get('statistics/menu-category-sales', [StatisticController::class, 'menuCategorySales'])->name('api.statistics.menu-category-sales');
