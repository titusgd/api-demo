<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\System\IpListController as IpList;
use App\Http\Controllers\System\ErrorReportController as ErrorReport;

Route::group(['middleware' => 'auth:sanctum', 'prefix' => '/system'], function () {
    Route::apiResource('/ip_list',IpList::class);
    Route::apiResource('/error_report',ErrorReport::class);
});
