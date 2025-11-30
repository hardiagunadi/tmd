<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FinancialRecapController;
use App\Http\Controllers\OtherTransactionController;
use App\Http\Controllers\TagihanController;
use App\Http\Controllers\TagihanPenarikanController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('tagihan.index');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    Route::get('/profil', [AuthController::class, 'edit'])->name('credentials.edit');
    Route::post('/profil', [AuthController::class, 'update'])->name('credentials.update');

    Route::get('/lainnya', [OtherTransactionController::class, 'index'])->name('lainnya.index');
    Route::post('/lainnya', [OtherTransactionController::class, 'store'])->name('lainnya.store');
    Route::get('/rekap-keuangan', [FinancialRecapController::class, 'index'])->name('rekap.index');

    Route::prefix('tagihan')->name('tagihan.')->group(function () {
        Route::get('/', [TagihanController::class, 'index'])->name('index');

        Route::get('/import', [TagihanController::class, 'importForm'])->name('import.form');
        Route::post('/import/preview', [TagihanController::class, 'importPreview'])->name('import.preview');
        Route::post('/import/store', [TagihanController::class, 'importStore'])->name('import.store');

        Route::post('/print-batch', [TagihanController::class, 'printBatch'])->name('print.batch');
        Route::get('/{tagihan}/print', [TagihanController::class, 'print'])->name('print');

        Route::get('/penarikan', [TagihanPenarikanController::class, 'index'])->name('penarikan.index');
        Route::post('/penarikan', [TagihanPenarikanController::class, 'store'])->name('penarikan.store');
        Route::put('/penarikan/{penarikan}', [TagihanPenarikanController::class, 'update'])->name('penarikan.update');
    });
});
