<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Administration\PunchTimeCardController;
// use App\Http\Controllers\Administration\SheduleController;
use App\Http\Controllers\Administration\ApplicationController;
use App\Http\Controllers\Administration\PaymentVoucherController;
use App\Http\Controllers\Administration\LeaveTypeController;
use App\Http\Controllers\Administration\LeaveDayoffController;
use App\Http\Controllers\Administration\LeaveFormController;
use App\Http\Controllers\AbsenceReCord\AbsenceRecodeController;
// use App\Http\Controllers\Administration\ScheduleStartDateController;
// use App\Http\Controllers\Administration\ScheduleWorkController;

Route::middleware('auth:sanctum')->prefix("administration")->name("administration.")->group(function () {
    // Route::group(function () {
    // 上下班打卡
    Route::post("/punchtimecard", [PunchTimeCardController::class, "punchtimecard"])->name("punchtimecard");
    // // 排班表(舊)
    // Route::prefix('/schedule')->name('schedule.')->group(function () {
    //     Route::middleware('throttle:60,1')->post('/update', [SheduleController::class, "update"])->name('update');
    //     Route::post('/list', [SheduleController::class, "list"])->name('list');
    //     Route::post('/person', [SheduleController::class, "person"])->name('person');
    // });
    // // 變形工時，排班表
    // Route::get('/work_schedule/startDate/{store_id}', [ScheduleStartDateController::class, 'list'])->name('work_schedule.start_date.list');
    // Route::post('/work_schedule/startDate', [ScheduleStartDateController::class, 'store'])->name('work_schedule.start_date.store');
    // Route::put('/work_schedule/startDate/{id}', [ScheduleStartDateController::class, 'update'])->name('work_schedule.start_date.update');
    // Route::get('/work_schedule', [ScheduleWorkController::class, 'list'])->name('work_schedule.work.list');
    // Route::put('/work_schedule', [ScheduleWorkController::class, 'addScheduleItem'])->name('work_schedule.work.add_item');
    // 申議書
    Route::prefix('/proposal')->name('proposal.')->group(function () {
        Route::post('/list', [ApplicationController::class, 'list'])->name('list');
        Route::post('/add', [ApplicationController::class, 'add'])->name('add');
        Route::post('/audit', [ApplicationController::class, 'audit'])->name('audit');
    });
    // 支出憑單
    Route::prefix('/disbursement_voucher')->name('disbursement_voucher')->group(function () {
        Route::post('/list', [PaymentVoucherController::class, 'list'])->name('list');
        Route::post('/add', [PaymentVoucherController::class, 'add'])->name('add');
        Route::post('/audit', [PaymentVoucherController::class, 'audit'])->name('audit');
    });
    // 請假單類別管理
    Route::prefix('/leave_type')->name('leave_type.')->group(function () {
        Route::post('/add', [LeaveTypeController::class, 'add'])->name('add');
        Route::post('/update', [LeaveTypeController::class, 'update'])->name('update');
        Route::post('/sort', [LeaveTypeController::class, 'sort'])->name('sort');
        Route::post('/use', [LeaveTypeController::class, 'use'])->name('use');
    });
    // 請假單
    Route::prefix('/leave_dayoff')->name('leave_dayoff.')->group(function () {
        Route::post('/add', [LeaveDayoffController::class, 'add'])->name('add');
        Route::post('/list', [LeaveDayoffController::class, 'list'])->name('list');
        Route::post('/del', [LeaveDayoffController::class, 'del'])->name('del');
        Route::post('/update', [LeaveDayoffController::class, 'update'])->name('update');
    });
    // 假單管理
    Route::prefix('/leave_form')->name('leave_form.')->group(function () {
        Route::post('/list', [LeaveFormController::class, 'list'])->name('list');
        Route::post('/audit', [LeaveFormController::class, 'audit'])->name('audit');
        Route::post('/update', [LeaveFormController::class, 'update'])->name('update');
    });
    // });
    // 請假單列表
    Route::post('/leave_type/list', [LeaveTypeController::class, 'list'])->name('leave_type.list');

    // 出缺勤打卡資料
    Route::middleware('auth:sanctum')->prefix('/attendanceRecord')->name('/attendanceRecord')->group(function () {
        Route::middleware('checkjsontype')->group(function () {
            Route::post('/leaveRecord', [AbsenceRecodeController::class, 'leaveRecord'])->name('leaveRecord');
            Route::post('/punchTimeCard', [AbsenceRecodeController::class, 'punchTimeCard'])->name('punchTimeCard');
        });
    });
    Route::apiResources([
        // 應收帳款
        '/receivable' => App\Http\Controllers\Accounting\ReceivableController::class,
        // 應付帳款
        '/payable'    => App\Http\Controllers\Accounting\PayableController::class,
        // 對應科目
        '/correspond' => App\Http\Controllers\Accounting\CorrespondController::class,
        // 分類帳
        '/ledger'     => App\Http\Controllers\Accounting\LedgerController::class
    ]);
});
