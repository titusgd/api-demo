<?php

namespace App\Http\Controllers\Account;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Accounts\WebSettingService;

class WebSettingController extends Controller
{
    public function update(Request $request)
    {
        $req = $request->all();
        $service = new WebSettingService();
        $vali = $service->vali($req);
        if ($vali) return $vali;
        $userSetting = $service->userSetting($req);
        return $userSetting;
    }

    public function list(Request $request)
    {
        $service = new WebSettingService();
        return $service->getlist();
    }

}
