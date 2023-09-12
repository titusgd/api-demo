<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Account\Access;
use App\Models\Account\AccessGroup;
use Illuminate\Http\Request;
use App\Services\Accounts\AccessService;


class AccessController extends Controller
{
    public function list(Request $request)
    {
        $service = new AccessService();
        $access_groups = AccessGroup::select('id', 'name')->get();
        for ($i = 0; $i < count($access_groups); $i++) {
            $access = Access::select('id', 'name', 'flag as use')
                ->where('access_group_id', '=', $access_groups[$i]['id'])
                ->get();

            for ($j = 0; $j < count($access); $j++) {
                $access[$j]['use'] = (bool)$access[$j]['use'];
                // $access[$j]['use'] = ($access[$j]['use'] ==0)? false:true;
            }

            $access_groups[$i]['access'] = $access;
        }
        return $service->response('00', 'ok', $access_groups);
        return $service->response("999", "");
    }
    /** store
     *  新增資源
     * 
     */
    public function add(Request $request)
    {
        // exit();
        $service = new AccessService();
        // --------------------------------json to array--------------------------------
        $req = $request->all();
        // --------------------------------請求資料格式檢查--------------------------------
        $validator = $service->validator($req);
        // 格式錯誤輸出
        if ($validator->fails()) {
            $message = explode(" ", $validator->errors()->first());
            return $service->response($message[1], $message[0]);
        }

        // -------------------------------- 檢查群組資源是否存在--------------------------------
        if (!$service->isAccessGroup($req['group'])) return $service->response("02", "group");

        // -------------------------------- 檢查輸入名稱是否重複--------------------------------
        if ($service->isName($req['name'])) return $service->response("03", "name");

        //  -------------------------------- 新增資源 --------------------------------
        if ($service->create($req)) return $service->response("00", "ok");

        // ------------------------------ 預期外錯誤 --------------------------------
        return $service->response("99", "");
        return $service->response("999", "");
    }

    /** update
     *  修改資源
     */
    public function update(Request $request, Access $access)
    {
        // return Auth::user()->id;
        $service = new AccessService();
        // 資料轉換陣列
        $req = $request->all();
        $vali = $service->validatorAndResponse($req,[
            'id'=>'required|integer|exists:accesses,id',
            'use'=>'required|boolean'
        ],[
            'id.required'=>'01 id',
            'id.integer'=>'01 id',
            'id.exists'=>'02 id',
            'use.required'=>'01 use',
            'use.boolean'=>'01 use',
        ]);
        if($vali) return $vali;

        // ------------------------------ 檢查資源是否存在 ------------------------------
        // 資源不存在，錯誤處理
        // if (!$service->checkResource($req['id'])) return $service->response("02", "id");
        // if ($req['use'] !== true && $req['use'] !== false) return $service->response("01", "use");

        // -------------------------------- 更新`accesses`資料 -----------------------------
        if ($service->updateAccess($req)) return $service->response("00", "ok");

        return $service->response("999", "");
    }

    
}
