<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MomoController;

Route::prefix('momo')->group(function () {
    Route::post('/create', [MomoController::class, 'createPayment'])->name('api.momo.create-payment');
    Route::get('/return', [MomoController::class, 'return'])->name('api.momo.return');
    Route::post('/notify', [MomoController::class, 'ipn'])->name('api.momo.notify');
});
