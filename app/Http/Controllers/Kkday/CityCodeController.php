<?php

namespace App\Http\Controllers\Kkday;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Kkday\CityService;
class CityCodeController extends Controller
{
    
    public function index(Request $request){
        return (new CityService($request))
        ->cityList()
        ->getResponse();
    }

    public function importData(Request $request){
        return (new CityService($request))
        ->runImport()
        ->getResponse();
    }

    public function settingUse(Request $request){
        return (new CityService($request))
        ->runValidate('settingUse')
        ->settingUse()
        ->getResponse();
    }

    public function settingStore(Request $request){
        return (new CityService($request))
        ->settingSort()
        ->getResponse();
    }
}
