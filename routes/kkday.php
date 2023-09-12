<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Kkday\ProductSearchController;
use App\Http\Controllers\Kkday\QueryProductController;
use App\Http\Controllers\Kkday\QueryPackageController;
use App\Http\Controllers\Kkday\QueryStateController;

// booking
use App\Http\Controllers\Kkday\Booking\QueryAmountController;

use App\Http\Controllers\Kkday\AirportTypeCodeController as KkdayAirportCode;
use App\Http\Controllers\Kkday\CatKey\SubKeyController as KkdayCatSubKey;
use App\Http\Controllers\Kkday\CatKey\MainController as KkdayCatMainKey;
use App\Http\Controllers\Kkday\HeightUnitController as KkdayHeightUnit;
use App\Http\Controllers\Kkday\WeightUnitController as KkdayWeightUnit;
use App\Http\Controllers\Kkday\ShoeSizeController as KkdayShoeSize;
use App\Http\Controllers\Kkday\FlightClassController as KkdayFlightClass;
use App\Http\Controllers\Kkday\GenderController as KkdayGender;
use App\Http\Controllers\Kkday\AppClassController as KkdayAppClass;
use App\Http\Controllers\Kkday\CurrencyCodeController as KkdayCurrencyCode;
use App\Http\Controllers\Kkday\LanguageCodeController as KkdayLanguageCode;
use App\Http\Controllers\Kkday\ProductClassController as KkdayProductClass;
use App\Http\Controllers\Kkday\CountryCodeController as KkdayCountryCode;
use App\Http\Controllers\Kkday\ImportProductController;
use App\Http\Controllers\Kkday\CityCodeController as KkdayCityCode;
use App\Http\Controllers\Kkday\Booking\BookingController;

Route::group(['middleware' => 'auth:sanctum'], function () {

    // kkday api
    Route::group(['prefix' => 'kkday'], function () {
        Route::get('/query_state/import_data', [QueryStateController::class, 'importData']);
        Route::get('/product_search', [ProductSearchController::class, 'index']);
        Route::get('/query_product', [QueryProductController::class, 'index']);
        Route::get('/query_package', [QueryPackageController::class, 'index']);
        Route::get('/query_state', [QueryStateController::class, 'index']);
        Route::get('/booking/query_amount', [QueryAmountController::class, 'index']);
        Route::get('/airport_type_code/import_data', [KkdayAirportCode::class, 'importData']);
        Route::get('/cat_sub_key/import_data', [KkdayCatSubKey::class, 'importData']);
        Route::get('/height_unit/import_data', [KkdayHeightUnit::class, 'importData']);
        Route::get('/weight_unit/import_data', [KkdayWeightUnit::class, 'importData']);
        Route::get('/shoe_size/import_data', [KkdayShoeSize::class, 'importData']);
        Route::get('/flight_class/import_data', [KkdayFlightClass::class, 'importData']);
        Route::get('/gender/import_data', [KkdayGender::class, 'importData']);
        Route::get('/app_class/import_data', [KkdayAppClass::class, 'importData']);        
        Route::get('/city_code/import_data', [KkdayCityCode::class, 'importData']);
        Route::post('/cat_main_key/set_sort', [KkdayCatMainKey::class, 'settingSort']);
        Route::post('/cat_main_key/use', [KkdayCatMainKey::class, 'settingStatus']);
        Route::post('/cat_sub_key/set_sort', [KkdayCatSubKey::class, 'settingSort']);
        Route::post('/cat_sub_key/use', [KkdayCatSubKey::class, 'settingStatus']);
        Route::get('/country_city_code', [KkdayCountryCode::class, 'getCountryCityCode']);
        Route::post('/country/use', [KkdayCountryCode::class, 'settingUse']);
        Route::post('/city/use', [KkdayCityCode::class, 'settingUse']);
        Route::post('/country/set_sort', [KkdayCountryCode::class, 'settingStore']);
        Route::post('/city/set_sort', [KkdayCityCode::class, 'settingStore']);
        Route::apiResource('/airport_type_code', KkdayAirportCode::class);
        Route::apiResource('/cat_sub_key', KkdayCatSubKey::class);
        Route::apiResource('/cat_main_key', KkdayCatMainKey::class);
        Route::apiResource('/height_unit', KkdayHeightUnit::class);
        Route::apiResource('/weight_unit', KkdayWeightUnit::class);
        Route::apiResource('/shoe_size', KkdayShoeSize::class);
        Route::apiResource('/flight_class', KkdayFlightClass::class);
        Route::apiResource('/gender', KkdayGender::class);
        Route::apiResource('/app_class', KkdayAppClass::class);
        Route::apiResource('/currency_code', KkdayCurrencyCode::class);
        Route::apiResource('/language_code', KkdayLanguageCode::class);
        Route::apiResource('/product_class', KkdayProductClass::class);
        Route::apiResource('/country_code', KkdayCountryCode::class);
        Route::apiResource('/city_code', KkdayCityCode::class);
        /* 請勿移除，匯入資料時會用到
        Route::get('/currency_code/import_data', [KkdayCurrencyCode::class, 'importData']);
        Route::get('/language_code/import_data', [KkdayLanguageCode::class, 'importData']);
        Route::get('/product_class/import_data', [KkdayProductClass::class, 'importData']);
        Route::get('/country_code/import_data', [KkdayCountryCode::class, 'importData']);
        */
    });
});

// kkday public
Route::group(['middleware' => 'tokenAuth'], function () {

    // kkday api
    Route::group(['prefix' => 'kkday-public'], function () {
        Route::get('/product_search', [ProductSearchController::class, 'index']);
        Route::get('/query_product', [QueryProductController::class, 'index']);
        Route::get('/query_package', [QueryPackageController::class, 'index']);
        Route::get('/query_state', [QueryStateController::class, 'index']);
        Route::get('/booking/query_amount', [QueryAmountController::class, 'index']);
        Route::get('/airport_type_code', [KkdayAirportCode::class, 'index']);
        Route::get('/cat_sub_key', [KkdayCatSubKey::class, 'index']);
        Route::get('/cat_main_key', [KkdayCatMainKey::class, 'index']);
        Route::get('/height_unit', [KkdayHeightUnit::class, 'index']);
        Route::get('/weight_unit', [KkdayWeightUnit::class, 'index']);
        Route::get('/shoe_size', [KkdayShoeSize::class, 'index']);
        Route::get('/flight_class', [KkdayFlightClass::class, 'index']);
        Route::get('/gender', [KkdayGender::class, 'index']);
        Route::get('/app_class', [KkdayAppClass::class, 'index']);
        Route::get('/currency_code', [KkdayCurrencyCode::class, 'index']);
        Route::get('/language_code', [KkdayLanguageCode::class, 'index']);
        Route::get('/product_class', [KkdayProductClass::class, 'index']);
        Route::get('/country_city_code', [KkdayCountryCode::class, 'getCountryCityCode']);
    });
});

Route::group(['prefix' => 'kkday-public'], function () {
    Route::post('/booking', [BookingController::class, 'index']);
    Route::post('/booking/order', [BookingController::class, 'order']);
    Route::post('/booking/parseCustom', [BookingController::class, 'parseCustom']);
});
