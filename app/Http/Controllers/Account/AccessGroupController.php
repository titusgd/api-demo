<?php

namespace App\Http\Controllers\Account;

use App\Models\Account\AccessGroup;
use App\Http\Controllers\Controller;
// use App\Models\Access\AccessGroup as AccessAccessGroup;
use Illuminate\Http\Request;
use App\Services\Accounts\AccessGroupService;

class AccessGroupController extends Controller
{
    public function add(Request $request)
    {
        $headers = array('Content-Type' => 'application/json');
        $service = new AccessGroupService();
        $req = $request->all();
        $validator = $service->validator($req);
        if ($validator->fails()) {
            $message = $validator->errors()->first();
            $message = explode(' ', $message);
            // 自訂義格式
            $res = [
                "code" => $message[1],
                "msg" => $message[0],
                "data" => []
            ];

            // 回傳錯誤訊息
            return response()->json($res, 200, $headers);
        }
        AccessGroup::create([
            "name" => $req['name']
        ]);
        return $service->response("00", "ok", $headers);
    }

    public function update(Request $request, AccessGroup $access_group)
    {
        $headers = array('Content-Type' => 'application/json');
        $service = new AccessGroupService();
        // 輸入資料轉換
        $req = $request->all();
        $access_group_id = $req['id'];
        // 檢查 id 是否存在
        if (!AccessGroup::where('id', "=", $access_group_id)->first()) return $service->response("02", "id", $headers);

        $validator = $service->validator($req, $access_group_id);
        if ($validator->fails()) {
            // 取得第一筆錯誤訊息，並分割錯誤碼
            $message = $validator->errors()->first();
            $message = explode(' ', $message);
            // 自訂義格式
            $res = [
                "code" => $message[1],
                "msg" => $message[0],
                "data" => []
            ];

            // 回傳錯誤訊息
            return response()->json($res, 200, $headers);
        }

        // 存入資料庫
        $access_group = AccessGroup::find($access_group_id);
        $access_group->name = $req["name"];
        $access_group->save();

        return $service->response("00", "ok", $headers);
    }
}
