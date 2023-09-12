<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Hr\HRController;
use App\Http\Controllers\Hr\HRAborigineController;
use App\Http\Controllers\Hr\HREducationController;
use App\Http\Controllers\Hr\HREmergencyContactController;
use App\Http\Controllers\Hr\HRCertificateController;
use App\Http\Controllers\Hr\HRDetailController;
use App\Http\Controllers\Hr\HRExperienceController;
use App\Http\Controllers\Hr\HRForeignPhoneController;
use App\Http\Controllers\Hr\HRIndividualController;
use App\Http\Controllers\Hr\HRInsuranceController;
use App\Http\Controllers\Hr\HROfficeController;
use App\Http\Controllers\Hr\HROtherController;
use App\Http\Controllers\Hr\HRTestController;
use App\Http\Controllers\Hr\OrganizeController;
use App\Http\Controllers\Hr\PositionController;

Route::group(['middleware' => 'auth:sanctum'], function () {

         // 職稱
         Route::apiResource('/position', PositionController::class);

         // 組織
         Route::put('/organize/sort/{id}', [OrganizeController::class, 'sort']);
         Route::patch('/organize/use/{id}', [OrganizeController::class, 'use']);
         Route::apiResource('/organize', OrganizeController::class);

         Route::group(['prefix' => 'HR'], function(){
             // hr relation detail
             Route::apiResource('/detail', HRDetailController::class);
             Route::apiResource('/aborigines', HRAborigineController::class);
             Route::apiResource('/education', HREducationController::class);
             Route::apiResource('/certificate', HRCertificateController::class);
             Route::apiResource('/emergencyContact', HREmergencyContactController::class);
             Route::apiResource('/experience', HRExperienceController::class);
             Route::apiResource('/foreignPhone', HRForeignPhoneController::class);
             Route::apiResource('/individual', HRIndividualController::class);
             Route::apiResource('/insurance', HRInsuranceController::class);
             Route::apiResource('/office', HROfficeController::class);
             Route::apiResource('/other', HROtherController::class);
             Route::apiResource('/DISC', HRTestController::class);
             Route::put('/position/{id}', [HRController::class, 'update_position']);
             Route::apiResource('/detail', HRDetailController::class);
         });
         Route::apiResource('/HR', HRController::class);

});
