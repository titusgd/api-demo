<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Account\NoticeController;

Route::middleware('auth:sanctum')->prefix('/notify')->name('notify.')->group(function () {
    Route::middleware('checkjsontype')->group(function () {
        Route::post('/list', [NoticeController::class, 'list'])->name('list');          // 列表
        Route::post('/add', [NoticeController::class, 'add'])->name('add');             // 發送
        Route::post('/close', [NoticeController::class, 'close'])->name('close');       // 結案
        Route::post('/reply', [NoticeController::class, 'reply'])->name('reply');       // 回覆
        Route::post('/forward', [NoticeController::class, 'forward'])->name('forward'); // 轉發
        // // 主表關閉
        // Route::post('/batch_close',[App\Http\Controllers\Account\NoticeController::class,'batchClose'])->name('batch_close');   // 批次關閉

    });
    // 個人關閉
    Route::post('/clear', [NoticeController::class, 'batchCloseSelf'])->name('clear');   // 批次關閉
});
