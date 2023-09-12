<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Sms\SmsMessageController;
use App\Http\Controllers\Kkday\ImportProductController;

use App\Http\Controllers\TestController;

// -------------------------------- auth 認證 --------------------------------
Route::group(['prefix' => 'auth'], function () {
    Route::middleware('checkjsontype')->post('login', [App\Http\Controllers\AuthController::class, 'login']);

    // api驗證路由，使用中繼驗證
    Route::middleware('auth:sanctum')->name('user.')->group(function () {
        Route::get('/user', [App\Http\Controllers\AuthController::class, 'user']);
        Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout']);
    });
});

// SMS 簡訊
Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::group(['prefix' => 'sms'], function () {
        Route::apiResource('/', SmsMessageController::class);
        Route::post('/point', [SmsMessageController::class, 'account_point']);
    });
});

Route::group(['prefix' => 'tour_feed'], function () {
    Route::get('/session',);
});


Route::group(['middleware' => 'auth:sanctum'], function () {

    // ------------------------------------------ 使用者類 ------------------------------------------------------
    Route::group(['prefix' => 'account'], function () {
        Route::group(['prefix' => 'access'], function () {
            Route::post('/list', [App\Http\Controllers\Account\AccessController::class, 'list']);
            Route::group(['middleware' => 'checkjsontype'], function () {
                Route::post('/add', [App\Http\Controllers\Account\AccessController::class, 'add']);
                Route::post('/update', [App\Http\Controllers\Account\AccessController::class, 'update']);
            });
        });

        // -------------------------------- 使用者 -----------------------------
        Route::group(['prefix' => 'user'], function () {
            Route::post('/list', [App\Http\Controllers\Account\UserController::class, 'list']);
            Route::group(['middleware' => 'checkjsontype'], function () {
                Route::post('/add', [App\Http\Controllers\Account\UserController::class, 'add']);
                Route::post('/update', [App\Http\Controllers\Account\UserController::class, 'update']);
                Route::post('/set_access', [App\Http\Controllers\Account\UserController::class, 'set_access']);
                Route::post('/data', [App\Http\Controllers\Account\UserController::class, 'data']);
                Route::post('/status', [App\Http\Controllers\Account\UserController::class, 'status']);

                // 個人化設定
                // Route::post('/web_setting', [App\Http\Controllers\Account\WebSettingController::class, 'update'])->name('web_setting');
            });

            // 個人化設定列表
            // Route::post('/web_setting_list', [App\Http\Controllers\Account\WebSettingController::class, 'list'])->name('web_setting_list');
        });
    });

    // 會計
    Route::group(['prefix' => 'accountant'], function () {
        Route::apiResource('/currencyExchange', App\Http\Controllers\Accountant\CurrencyExchangeController::class);
    });
});

Route::post('/get-token', [App\Http\Controllers\TokenController::class, 'get_token']);
Route::get('/importData', [ImportProductController::class, 'index']);

Route::group(['prefix' => 'test'], function () {
    // line api
    Route::get('/kkdayProductSearch', [TestController::class, 'KkdayProductSearch']);
});

Route::prefix("/image")->name('image.')->group(function () {
    // 回傳圖片
    Route::get("/get_image/{image_id}", [App\Http\Controllers\Image\ImageController::class, 'getImage'])->name("get_image");
    Route::get("/get_image_source/{image_id}", [App\Http\Controllers\Image\ImageController::class, 'getImageSource'])->name("get_image_source");
});
