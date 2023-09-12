<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Administrations\PunchTimeCardService;

class PunchTimeCardController extends Controller
{
    public function punchtimecard(Request $request)
    {
        $service = new PunchTimeCardService();
        if (!$service->checkLogin()) return $service->response("105", "logout");

        $req = $request->all();
        // 資料驗證 檢查欄位及格式
        $validate = $service->validate($req);

        // 錯誤訊息
        if ($validate->fails()) {
            $message = explode(" ", $validate->errors()->first());
            return $service->response($message[0], $message[1]);
        }
        // 檢查ip
        $check_ip = $service->checkIp($request->ip());
        if ($check_ip) return $check_ip;

        return ($service->checkType($req['type'])) ? $service->punchtimecard($request) : $service->response("01", "type");
    }

    public function list(Request $request)
    {
        $service = new PunchTimeCardService();
        $valid = $service->validateList($request);
        if ($valid) return $valid;

        return $service->list($request);
    }
}
