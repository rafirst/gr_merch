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
use App\Http\Controllers\TransferBarangController;
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
    Route::get('/dashboard/stock/{item}', [DashboardController::class, 'showStock'])
        ->whereNumber('item')->name('dashboard.stock.show');

    // Master Data Items: staff hanya dapat melihat item cabangnya.
    Route::get('/items', [ItemController::class, 'index'])->name('items.index');
    Route::get('/items-export', [ExportImportController::class, 'exportItems'])->name('items.export');

    Route::middleware('role:admin_ho')->group(function () {
        Route::resource('items', ItemController::class)->except(['index', 'show']);
        Route::get('/items-import', [ExportImportController::class, 'showImportForm'])->name('items.import.form');
        Route::get('/items-import/template', [ExportImportController::class, 'exportItemsImportTemplate'])->name('items.import.template');
        Route::post('/items-import', [ExportImportController::class, 'importItems'])->name('items.import');
    });

    Route::get('/items/{item}', [ItemController::class, 'show'])
        ->whereNumber('item')
        ->name('items.show');

    Route::get('/transfers', [TransferBarangController::class, 'index'])->name('transfers.index');
    Route::post('/transfers/{transferBarang}/receive', [TransferBarangController::class, 'receive'])
        ->middleware('role:staff_cabang')->name('transfers.receive');

    // Barang Keluar
    Route::get('/stock-out', [StockOutController::class, 'index'])->name('stockout.index');
    Route::get('/stock-out/create', [StockOutController::class, 'create'])->name('stockout.create');
    Route::get('/stock-out/{stockOut}/invoice/download', [StockOutController::class, 'downloadInvoice'])->name('stockout.invoice.download');
    Route::get('/stock-out/{stockOut}/invoice', [StockOutController::class, 'invoice'])->name('stockout.invoice');
    Route::get('/stock-out/{stockOut}/edit', [StockOutController::class, 'edit'])->name('stockout.edit');
    Route::get('/stock-out/{stockOut}', [StockOutController::class, 'show'])->name('stockout.show');
    Route::post('/stock-out', [StockOutController::class, 'store'])->name('stockout.store');
    Route::put('/stock-out/{stockOut}', [StockOutController::class, 'update'])->name('stockout.update');
    Route::post('/stock-out/{stockOut}/bukti-pembayaran', [StockOutController::class, 'updatePaymentProof'])->name('stockout.bukti-pembayaran.update');

    // Histori
    Route::get('/history', [HistoryController::class, 'index'])->name('history.index');
    Route::get('/history/show/{type}/{id}', [HistoryController::class, 'show'])->name('history.show');
    Route::get('/history-export', [ExportImportController::class, 'exportHistory'])->name('history.export');
    Route::get('/history-export/excel', [ExportImportController::class, 'exportHistoryExcel'])
        ->middleware('role:admin_ho')->name('history.export.excel');

    // ===== Khusus Admin Pusat =====
    Route::middleware('role:admin_ho')->group(function () {
        Route::get('/transfers/create', [TransferBarangController::class, 'create'])->name('transfers.create');
        Route::post('/transfers', [TransferBarangController::class, 'store'])->name('transfers.store');
        Route::get('/transfers/{transferBarang}/edit', [TransferBarangController::class, 'edit'])->name('transfers.edit');
        Route::put('/transfers/{transferBarang}', [TransferBarangController::class, 'update'])->name('transfers.update');
        Route::delete('/transfers/{transferBarang}', [TransferBarangController::class, 'destroy'])->name('transfers.destroy');

        Route::get('/stock-in', [StockInController::class, 'index'])->name('stockin.index');
        Route::get('/stock-in/export', [ExportImportController::class, 'exportStockIn'])->name('stockin.export');
        Route::get('/stock-in/import', [ExportImportController::class, 'showStockInImportForm'])->name('stockin.import.form');
        Route::get('/stock-in/import/template', [ExportImportController::class, 'exportStockInImportTemplate'])->name('stockin.import.template');
        Route::post('/stock-in/import', [ExportImportController::class, 'importStockIn'])->name('stockin.import');
        Route::get('/stock-in/create', [StockInController::class, 'create'])->name('stockin.create');
        Route::get('/stock-in/{stockIn}/edit', [StockInController::class, 'edit'])->name('stockin.edit');
        Route::get('/stock-in/{stockIn}', [StockInController::class, 'show'])->name('stockin.show');
        Route::post('/stock-in', [StockInController::class, 'store'])->name('stockin.store');
        Route::put('/stock-in/{stockIn}', [StockInController::class, 'update'])->name('stockin.update');
        Route::delete('/stock-in/{stockIn}', [StockInController::class, 'destroy'])->name('stockin.destroy');

        Route::resource('cabang', CabangController::class);
        Route::resource('users', UserController::class);

        Route::get('/approval', [ApprovalController::class, 'index'])->name('approval.index');
        Route::get('/approval/stock-out/{stockOut}', [ApprovalController::class, 'showStockOut'])->name('approval.show.stockout');
        Route::get('/approval/stock-in-edit/{editRequest}', [ApprovalController::class, 'showStockInEdit'])->name('approval.show.stockin.edit');
        Route::post('/approval/{stockOut}/approve', [ApprovalController::class, 'approve'])->name('approval.approve');
        Route::post('/approval/{stockOut}/reject', [ApprovalController::class, 'reject'])->name('approval.reject');
        Route::post('/approval/stock-in-edit/{editRequest}/approve', [ApprovalController::class, 'approveStockInEdit'])->name('approval.stockin.edit.approve');
        Route::post('/approval/stock-in-edit/{editRequest}/reject', [ApprovalController::class, 'rejectStockInEdit'])->name('approval.stockin.edit.reject');
    });

    Route::get('/transfers/{transferBarang}', [TransferBarangController::class, 'show'])->name('transfers.show');
});
