<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TagihanController;

// Arahkan halaman utama ke /tagihan
Route::get('/', function () {
    return redirect()->route('tagihan.index');
});


// GROUP ROUTE TAGIHAN
Route::prefix('tagihan')->name('tagihan.')->group(function () {
    Route::get('/', [TagihanController::class, 'index'])->name('index');

    Route::get('/import', [TagihanController::class, 'importForm'])->name('import.form');
    Route::post('/import/preview', [TagihanController::class, 'importPreview'])->name('import.preview');
    Route::post('/import/store', [TagihanController::class, 'importStore'])->name('import.store');

    Route::post('/print-batch', [TagihanController::class, 'printBatch'])->name('print.batch');
    Route::get('/{tagihan}/print', [TagihanController::class, 'print'])->name('print');
});
