<?php
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Invoice\IssueController;
use App\Http\Controllers\Invoice\InvalidController;
use App\Http\Controllers\Invoice\TouchIssueController;
use App\Http\Controllers\Invoice\InvalidTouchIssueController;
use App\Http\Controllers\Invoice\EditController;

use App\Http\Controllers\Invoice\AllowanceIssueController;
use App\Http\Controllers\Invoice\AllowanceTouchIssueController;
use App\Http\Controllers\Invoice\SearchController as InvoiceSearchAll;
use App\Http\Controllers\Invoice\SearchInvoiceController;

Route::group(['middleware' => 'tokenAuth'], function () {
    // 電子收據
    Route::group(['prefix' => 'invoice'], function () {
        Route::post('/issue', [IssueController::class, 'issue']);
        Route::post('/invalid', [InvalidController::class, 'invalid']);
        Route::post('/edit', [EditController::class, 'edit']);
        Route::post('/touch_issue', [TouchIssueController::class, 'touchIssue']);
        Route::post('/invalid_touch_issue', [InvalidTouchIssueController::class, 'invalidTouchIssue']);
        Route::post('/allowance_issue', [AllowanceIssueController::class, 'allowanceIssue']);
        Route::post('/allowance_touch_issue', [AllowanceTouchIssueController::class, 'allowanceTouchIssue']);
        Route::post('/search_all', [InvoiceSearchAll::class, 'searchAll']);
        Route::post('/search_all_invalid', [InvoiceSearchAll::class, 'searchAllInvalid']);
        Route::post('/search_all_allowance', [InvoiceSearchAll::class, 'searchAllAllowance']);
        Route::post('/search_invalid', [InvoiceSearchAll::class, 'invalid']);
        Route::post('/search_allowance', [InvoiceSearchAll::class, 'allowance']);
        Route::post('/search_invoice', [SearchInvoiceController::class, 'searchInvoice']);
    });
});

Route::post('/invoice/touch_invoice_issue', [IssueController::class, 'touchInvoiceIssue']);
