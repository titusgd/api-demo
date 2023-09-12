<?php
use Illuminate\Support\Facades\Route;

Route::post('/test',[App\Http\Controllers\Kkday\Booking\TestController::class,'traffic']);
