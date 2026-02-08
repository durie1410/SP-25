<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MomoController;

Route::prefix('momo')->group(function () {
    Route::post('/create', [MomoController::class, 'createPayment'])->name('momo.create');
    Route::get('/return', [MomoController::class, 'return'])->name('momo.return');
    Route::post('/notify', [MomoController::class, 'ipn'])->name('momo.notify');
});
