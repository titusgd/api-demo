<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Apidoc\ApidocController;
use App\Http\Controllers\Apidoc\ApidocProjectController;
use App\Http\Controllers\Apidoc\ApidocApiController;

Route::group(['middleware' => 'auth:sanctum'], function () {
    // Api Document
    Route::apiResource('/apidoc/project', ApidocProjectController::class);
    Route::apiResource('/apidoc/api', ApidocApiController::class);
    Route::put('/apidoc/sort', [ApidocController::class, 'sort']);
    Route::apiResource('/apidoc', ApidocController::class);

});
