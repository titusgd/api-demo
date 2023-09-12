<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Accounting\AccountingSubjectController;
use App\Http\Controllers\Accounting\PettyCashController;
use App\Http\Controllers\Accounting\PaymentController;
use App\Http\Controllers\Accounting\ReceiptController;
use App\Http\Controllers\Accounting\TransferVoucherController;
use App\Http\Controllers\Accounting\DayStatementController;

Route::middleware('auth:sanctum')->prefix('/accounting')->name('accounting.')->group(function () {

    // -------------------------------- 零用金 ---------------------------------
    // Route::middleware('checkjsontype')->prefix("pettycash")->group(function () {
    //     // 35.變更狀態
    //     Route::post("update", [PettyCashController::class, 'update'])->name("update");
    //     // 33.申請紀錄
    //     Route::post("apply", [PettyCashController::class, 'apply'])->name("apply");
    //     // 34.交易明細 紀錄
    //     Route::post("detail", [PettyCashController::class, 'detail'])->name("detail");
    // });
    
    // 會計科目
    Route::prefix("subject")->name('subject.')->group(function () {
        Route::post("add", [AccountingSubjectController::class, 'add'])->name("add");
        Route::post("update", [AccountingSubjectController::class, 'update'])->name("update");
        Route::post("list", [AccountingSubjectController::class, 'list'])->name("list");
        Route::post("class", [AccountingSubjectController::class, 'class'])->name("class");
        Route::post("del", [AccountingSubjectController::class, 'del'])->name("del");
    });

    // 付款單
    Route::prefix("payment")->name('payment.')->group(function () {
        Route::post("add", [PaymentController::class, 'add'])->name("add");
        Route::post("update", [PaymentController::class, 'update'])->name("update");
        Route::post("list", [PaymentController::class, 'list'])->name("list");
        Route::post("detail", [PaymentController::class, 'detail'])->name("detail");
        Route::post("pay", [PaymentController::class, 'pay'])->name("pay");
        Route::post("del", [PaymentController::class, 'del'])->name("del");
        Route::post("audit", [PaymentController::class, 'audit'])->name("audit");
        Route::post("auditCancel", [PaymentController::class, 'auditCancel'])->name("auditCancel");
    });

    // 收款單
    Route::prefix("receive")->name('receive.')->group(function () {
        Route::post("update", [ReceiptController::class, 'update'])->name("update");
        Route::post("list", [ReceiptController::class, 'list'])->name("list");
        Route::post("detail", [ReceiptController::class, 'detail'])->name("detail");
        Route::post("audit", [ReceiptController::class, 'audit'])->name("audit");
        Route::post("auditCancel", [ReceiptController::class, 'auditCancel'])->name("auditCancel");
        Route::post("del", [ReceiptController::class, 'del'])->name("del");
    });

    // 應收帳款
    // Route::prefix("receivable")->name('receivable.')->group(function(){
    //     Route::post("list",[App\Http\Controllers\Accounting\ReceivableController::class,'list'])   ->name("list");
    // });

    // 應付帳款
    // Route::prefix("payable")->name('payable.')->group(function(){
    //     Route::post("list",[App\Http\Controllers\Accounting\PayableController::class,'list'])   ->name("list");
    //     Route::post("pay" ,[App\Http\Controllers\Accounting\PayableController::class,'pay'])    ->name("pay");
    // });

    // // 轉帳傳票
    // Route::prefix("transferVoucher")->name('transferVoucher.')->group(function () {
    //     Route::post("list", [TransferVoucherController::class, 'list'])->name("list");
    //     Route::post("detail", [TransferVoucherController::class, 'detail'])->name("detail");
    //     Route::post("add", [TransferVoucherController::class, 'add'])->name("add");
    //     Route::post("update", [TransferVoucherController::class, 'update'])->name("update");
    //     Route::post("audit", [TransferVoucherController::class, 'audit'])->name("audit");
    //     Route::post("del", [TransferVoucherController::class, 'del'])->name("del");
    // });
    // 日結
    Route::group(['prefix' => '/dayStatement', 'as' => 'day_statement.'], function () {
        // 日結單列表
        Route::get('/', [DayStatementController::class, 'index'])->name('index');
        // 取消日結
        Route::delete('/{id}', [DayStatementController::class, 'destroy'])->name('destroy');
        // 日結單 日結
        Route::post('/', [DayStatementController::class, 'store'])->name('store');
        // // 日結單 明細
        // Route::get('/detail', [DayStatementController::class, 'detail'])->name('detail');
        // 日結審核

    });
});