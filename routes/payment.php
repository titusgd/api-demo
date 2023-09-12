<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\LinePay\LinePayController;
use App\Http\Controllers\CTBC\CTBCController;

// ctbc 中國信託
Route::post('/payment/ctbc', [CTBCController::class, 'index']);

// line pay
Route::group(['prefix' => '/payment/line-pay'], function () {
    Route::get('/confirm', [LinePayController::class, 'confirm']);
    Route::get('/cancel', [LinePayController::class, 'cancel']);
    Route::post('/', [LinePayController::class, 'index']);
});
