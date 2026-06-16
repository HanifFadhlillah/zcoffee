<?php

use App\Http\Controllers\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\Customer\PaymentController as CustomerPaymentController;
use App\Http\Controllers\Cashier\DashboardController as CashierDashboardController;
use App\Http\Controllers\Cashier\PosController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

// ── Root redirect ──────────────────────────────────────────────────────────
Route::get('/', fn () => redirect()->route('login'));

// ── Customer routes (no auth) ──────────────────────────────────────────────
Route::prefix('order')->name('order.')->group(function () {
    Route::get('/{table}', [CustomerOrderController::class, 'index'])
        ->where('table', '[0-9]+')
        ->name('menu');

    Route::post('/', [CustomerOrderController::class, 'store'])
        ->name('store');

    Route::get('/check/{orderNumber}', [CustomerOrderController::class, 'checkPayment'])
        ->name('check');

    // ── Midtrans ────────────────────────────────────────────────────────
    Route::post('/payment/create/{order}', [CustomerPaymentController::class, 'create'])
        ->name('payment.create');
});

// Midtrans webhook — tidak perlu auth, tapi diverifikasi via signature key
Route::post('/payment/notification', [CustomerPaymentController::class, 'notification'])
    ->name('payment.notification');

// ── Auth routes (Laravel Breeze) ───────────────────────────────────────────
require __DIR__ . '/auth.php';

// ── Authenticated routes ───────────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {

    // Redirect setelah login berdasarkan role
    Route::get('/dashboard', function () {
        return auth()->user()->isAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('cashier.dashboard');
    })->name('dashboard');

    // ── Kasir routes ────────────────────────────────────────────────────
    Route::prefix('cashier')->name('cashier.')->middleware('role:admin,cashier')->group(function () {
        Route::get('/dashboard', [CashierDashboardController::class, 'index'])
            ->name('dashboard');

        Route::patch('/orders/{order}/status', [CashierDashboardController::class, 'updateStatus'])
            ->name('orders.status');

        Route::get('/orders/history', [CashierDashboardController::class, 'history'])
            ->name('orders.history');

        Route::get('/orders/{order}/receipt', [CashierDashboardController::class, 'receipt'])
            ->name('orders.receipt');

        // POS Kasir
        Route::get('/pos', [PosController::class, 'index'])->name('pos');
        Route::post('/pos', [PosController::class, 'store'])->name('pos.store');
    });

    // ── Manajemen Bersama (Admin & Kasir) ────────────────────────────────
    Route::prefix('manage')->name('manage.')->middleware('role:admin,cashier')->group(function () {
        Route::resource('menus', MenuController::class)->except('show');
        Route::patch('/menus/{menu}/toggle', [MenuController::class, 'toggleActive'])
            ->name('menus.toggle');
    });

    // ── Admin routes ─────────────────────────────────────────────────────
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        // Dashboard
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');
        Route::get('/stats/chart', [AdminDashboardController::class, 'chartData'])
            ->name('stats.chart');

        // Reports
        Route::get('/reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])
            ->name('reports.index');
        Route::get('/reports/export', [\App\Http\Controllers\Admin\ReportController::class, 'export'])
            ->name('reports.export');

        // User management
        Route::get('/users', [UserController::class, 'index'])
            ->name('users.index');
        Route::post('/users', [UserController::class, 'store'])
            ->name('users.store');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])
            ->name('users.destroy');
        Route::patch('/users/{user}/reset-password', [UserController::class, 'resetPassword'])
            ->name('users.reset-password');
    });
});
