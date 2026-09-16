<?php

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CabangController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportImportController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\StockInController;
use App\Http\Controllers\StockOutController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

// ===== Guest =====
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// ===== Authenticated =====
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Master Data Items (CRUD) - staff hanya cabangnya sendiri, admin semua
    Route::resource('items', ItemController::class);
    Route::get('/items-import', [ExportImportController::class, 'showImportForm'])->name('items.import.form');
    Route::post('/items-import', [ExportImportController::class, 'importItems'])->name('items.import');
    Route::get('/items-export', [ExportImportController::class, 'exportItems'])->name('items.export');

    // Barang Masuk
    Route::get('/stock-in', [StockInController::class, 'index'])->name('stockin.index');
    Route::get('/stock-in/create', [StockInController::class, 'create'])->name('stockin.create');
    Route::post('/stock-in', [StockInController::class, 'store'])->name('stockin.store');

    // Barang Keluar
    Route::get('/stock-out', [StockOutController::class, 'index'])->name('stockout.index');
    Route::get('/stock-out/create', [StockOutController::class, 'create'])->name('stockout.create');
    Route::post('/stock-out', [StockOutController::class, 'store'])->name('stockout.store');

    // Histori
    Route::get('/history', [HistoryController::class, 'index'])->name('history.index');
    Route::get('/history-export', [ExportImportController::class, 'exportHistory'])->name('history.export');

    // ===== Khusus Admin Pusat =====
    Route::middleware('role:admin_ho')->group(function () {
        Route::resource('cabang', CabangController::class)->except(['show']);
        Route::resource('users', UserController::class)->except(['show']);

        Route::get('/approval', [ApprovalController::class, 'index'])->name('approval.index');
        Route::post('/approval/{stockOut}/approve', [ApprovalController::class, 'approve'])->name('approval.approve');
        Route::post('/approval/{stockOut}/reject', [ApprovalController::class, 'reject'])->name('approval.reject');
    });
});
