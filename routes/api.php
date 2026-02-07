<?php

use Illuminate\Support\Facades\Route;

Route::prefix('momo')->group(function () {
    Route::post('/create-payment', 'MomoPaymentController@createPayment');
    Route::post('/callback-notification', 'MomoPaymentController@callbackNotification');
});

?>