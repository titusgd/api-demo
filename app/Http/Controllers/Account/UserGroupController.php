<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Account\UserGroup;
use Illuminate\Http\Request;
use App\Services\Accounts\UserGroupService;
use App\Services\Service;

class UserGroupController extends Controller
{
    private $service;

    function __construct()
    {
        $this->service = new UserGroupService();
    }

    public function add(Request $request)
    {
        $ss=new Service();
        $req = $request->all();
        $rules = [
            "name"=>"required|string|unique:user_groups,name",
            "access"=>"array"
        ];
        $emsg = [
            "name.required"=>"01 name",
            "name.string"=>"01 name",
            "name.unique"=>"03 name",
            "access.array"=>"01 access",
            'access.*.id.required' => '01 id',
            'access.*.id.integer' => '01 id',
            'access.*.id.exists' => '01 id',
            'access.*.use.boolean' => '01 use',
        ];

        if(!empty($req['access'][0]['id'])){
            $rules['access.*.id'] = "integer|exists:accesses";
            $rules['access.*.use'] = "boolean";
        }

        if(!empty($req['access'][0]['use'])){
            $rules['access.*.id'] = "required|integer|exists:accesses";
            $rules['access.*.use'] = "boolean";
        }
        
        $vali = $ss->validatorAndResponse($req,$rules,$emsg);

        if($vali) return $vali;

        foreach($req['access'] as $val){
            if($val['id']===0) return $ss->response("02",'id');
        }


        
        // 新增群組
        $user_group_info = $this->service->create($req);
        if ($user_group_info) {

            if (array_key_exists("access", $req)) {
                if (!empty($req['access'])) {
                    if ($this->service->createAccessToUserGroup($req['access'][0], $user_group_info['id'])) {
                        return $this->service->response("00", "ok");
                    }
                }
            }
            // 新增 權限、使用者群組 關聯寫入到access_user_group 
            return $this->service->response("00", "ok");
        } else {
            return $this->service->response("999", "");
        };
    }

    public function update(Request $request, UserGroup $userGroup)
    {
        $req = $this->service->requestToArray($request);
        // 1. 取得群組id
        $user_group_id = $req['id'];

        $ss = new Service;
        if($req['id']===null) {
            return $ss->response("01", "id");
        }else{
            $group_id  = UserGroup::select('id')->where('id','=',$req['id'])->first();
            if(!$group_id){
                return $ss->response("01", "id");
            }
        };
                
        $rules =[
            "id"=>"integer",
            "name"=>'required|string|unique:user_groups,name,'.$req['id'],
        ];
        $emsg = [
            "id.required"=>"01 id",
            "id.integer"=>"01 id",
            "id.exists"=>"02 id",
            "name.required"=>"01 name",
            "name.string"=>"01 name",
            "name.unique"=>"03 name",
            "access.array"=>"01 access",
            "access.required"=>"01 access",
            'access.*.id.required' => '01 id',
            'access.*.id.integer' => '01 id',
            'access.*.id.exists' => '01 id',
            'access.*.use.boolean' => '01 use',
        ];
        if(!empty($req['access'][0]['id'])){
            $rules['access.*.id'] = "integer|exists:accesses";
            $rules['access.*.use'] = "boolean";
        }
        if(!empty($req['access'][0]['use'])){
            $rules['access.*.id'] = "required|integer|exists:accesses";
            $rules['access.*.use'] = "boolean";
        }
        
        $vali = $ss->validatorAndResponse($req,$rules,$emsg);
        if($vali) return $vali;
        
        foreach($req['access'] as $val){
            if($val['id']===0) return $ss->response("02",'id');
        }

        // 7. 更新群組名稱
        $this->service->updateUserGroup($req, $user_group_id);
        
        // 8. 更新access_user_groups
        if (array_key_exists('access', $req)) {
            
            // 陣列有資料
            if(!empty($req['access'])) $this->service->updateAccessUserGroup($req['access'], $user_group_id);

            return $this->service->response('00', 'ok');
        }
        
        // 9. 回傳修改成功訊息
        return $this->service->response('00', 'ok');
    }
    // git 測試

}
