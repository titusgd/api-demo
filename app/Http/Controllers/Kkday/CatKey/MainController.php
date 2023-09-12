<?php

namespace App\Http\Controllers\Kkday\CatKey;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Kkday\CatKey\MainService;

class MainController extends Controller
{
    public function index(Request $request)
    {
        $arr = ['cat_main_key_state' => null, 'sub_key_state' => null];
        if (!empty($request['data'])) {
            $request = json_decode($request['data'], true);
            isset($request['cat_main_key_state']) && $arr['cat_main_key_state'] = $request['cat_main_key_state'];
            isset($request['sub_key_state']) && $arr['sub_key_state'] = $request['sub_key_state'];
        }
        // return (new CountryCodeService($arr))
        return (new MainService($arr))
            ->list()
            ->getResponse();
    }
    public function store(Request $request)
    {
        return (new MainService($request))
            ->runValidate('store')
            ->store()
            ->getResponse();
    }
    public function update(Request $request, $dataId)
    {
        return (new MainService($request, $dataId))
            ->runValidate('update')
            ->update()
            ->getResponse();
    }

    public function settingSort(Request $request)
    {
        return (new MainService($request))
            ->runValidate('settingSort')
            ->settingSort()
            ->getResponse();
    }

    public function settingStatus(Request $request)
    {
        return (new MainService($request))
            ->runValidate('settingStatus')
            ->settingStatus()
            ->getResponse();
    }
}
