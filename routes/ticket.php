<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Ticket\SearchCityController;
use App\Http\Controllers\Ticket\HotCityController;
use App\Http\Controllers\Ticket\SliderController;
use App\Http\Controllers\Ticket\HotTagController;
use App\Http\Controllers\Ticket\ThemeController;
use App\Http\Controllers\Ticket\ThemeProductController;

Route::group(['prefix' => '/ticket'], function () {
    Route::group(['middleware' => 'auth:sanctum'], function () {
        Route::prefix('/home')->group(function () {
            Route::post('/hot_city/image', [HotCityController::class, 'uploadImage']);
            Route::post('/hot_city/set_sort', [HotCityController::class, 'settingStor']);
            Route::post('/slider/use', [SliderController::class, 'settingUse']);
            Route::post('/slider/set_sort', [SliderController::class, 'settingSort']);
            Route::post('/hot_tag/image', [HotTagController::class, 'uploadImage']);
            Route::post('/hot_tag/set_sort', [HotTagController::class, 'settingSort']);
            Route::apiResource('/hot_city', HotCityController::class);
            Route::apiResource('/slider', SliderController::class);
            Route::apiResource('/hot_tag', HotTagController::class);

            Route::post('/theme/sort', [ThemeController::class, 'sort']);
            Route::post('/theme/use', [ThemeController::class, 'use']);
            Route::apiResource('/theme', ThemeController::class);
            Route::apiResource('/theme/prod', ThemeProductController::class);
        });

        Route::post('/search_city/set_sort', [SearchCityController::class, 'settingStor']);
        Route::apiResource('/search_city', SearchCityController::class);
    });

    Route::group(['prefix' => '/', 'middleware' => 'tokenAuth'], function () {
        Route::get('/slider', [SliderController::class, 'publicIndex']);
        Route::get('/hot_city', [HotCityController::class, 'publicIndex']);
        Route::get('/hot_tag', [HotTagController::class,'publicIndex']);
        Route::get('/search_hot_city', [SearchCityController::class,'publicIndex']);
        Route::get('/theme', [ThemeController::class, "index"]);
    });
});
