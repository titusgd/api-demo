<?php

namespace App\Http\Controllers\Kkday;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Kkday\CountryCodeService;

class CountryCodeController extends Controller
{
    //
    public function index(Request $request)
    {
        return (new CountryCodeService($request))
            ->countryCodeList()
            ->getResponse();
    }

    public function importData(Request $request)
    {
        return (new CountryCodeService($request))
            ->runImport()
            ->getResponse();
    }

    public function getCountryCityCode(Request $request)
    {
        $arr = ['country_state' => null, 'city_state' => null];
        if (!empty($request['data'])) {
            $request = json_decode($request['data'], true);
            isset($request['country_state'])&& $arr['country_state'] = $request['country_state'];
            isset($request['city_state'])&& $arr['city_state'] = $request['city_state'];
        }
        return (new CountryCodeService($arr))
            ->getCountryCityCode()
            ->getResponse();
    }

    public function settingUse(Request $request)
    {
        return (new CountryCodeService($request))
            ->runValidate('settingUse')
            ->settingUse()
            ->getResponse();
    }

    public function settingStore(Request $request){
        return (new CountryCodeService($request))
            ->settingSort()
            ->getResponse();
    }
}
