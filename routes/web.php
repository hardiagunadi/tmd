<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PenarikanController;
use App\Http\Controllers\PendapatanLainController;
use App\Http\Controllers\TagihanController;
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

    Route::prefix('tagihan')->name('tagihan.')->group(function () {
        Route::get('/', [TagihanController::class, 'index'])->name('index');

        Route::get('/import', [TagihanController::class, 'importForm'])->name('import.form');
        Route::post('/import/preview', [TagihanController::class, 'importPreview'])->name('import.preview');
        Route::post('/import/store', [TagihanController::class, 'importStore'])->name('import.store');

        Route::post('/print-batch', [TagihanController::class, 'printBatch'])->name('print.batch');
        Route::get('/{tagihan}/print', [TagihanController::class, 'print'])->name('print');
    });

    Route::get('/rekap-penarikan', [PenarikanController::class, 'index'])->name('penarikan.index');
    Route::post('/rekap-penarikan', [PenarikanController::class, 'store'])->name('penarikan.store');
    Route::patch('/rekap-penarikan/{penarikan}', [PenarikanController::class, 'update'])->name('penarikan.update');
    Route::delete('/rekap-penarikan/{penarikan}', [PenarikanController::class, 'destroy'])->name('penarikan.destroy');

    Route::get('/pendapatan-lain', [PendapatanLainController::class, 'index'])->name('pendapatan-lain.index');
    Route::post('/pendapatan-lain', [PendapatanLainController::class, 'store'])->name('pendapatan-lain.store');
    Route::delete('/pendapatan-lain/{pendapatanLain}', [PendapatanLainController::class, 'destroy'])->name('pendapatan-lain.destroy');
});
