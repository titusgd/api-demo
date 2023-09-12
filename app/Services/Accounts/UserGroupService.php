<?php

namespace App\Services\Accounts;

use App\Services\Service;
use Illuminate\Support\Facades\Validator;
use App\Models\Account\UserGroup;
use App\Models\Account\AccessUserGroup;
use App\Models\Account\Access;
use Illuminate\Support\Facades\Auth;

class UserGroupService extends Service
{
    public function requestToArray($request)
    {
        $req = json_decode($request->getContent(), true);
        return $req;
    }

    public function  validator($req, $id = null)
    {
        $id = ($id == null || $id == "") ?  "" : ",{$id}";
        $validator = Validator::make($req, [
            "name" => "required|string|unique:user_groups,name" . $id,
        ], [
            "name.required" => "name 01", // 必填
            "name.string" => "name 01",   // 型態
            "name.unique" => "name 03",   // 除自己以外
        ]);
        return $validator;
    }
    public function create($req)
    {
        // 新增群組
        if (isset($req['flag'])) {
            $flag = ($req['flag'] == 'true') ? true : false;
        } else {
            $flag = false;
        }

        $data = [
            "name" => $req["name"],
            "flag" => $flag,
            "user_id" => Auth::user()->id,
        ];
        $user_group = UserGroup::create($data);
        return $user_group;
    }

    public function createAccessToUserGroup($accesses, $user_group_id)
    {
        if ($accesses['id'] !== null) {
            $accesses = AccessUserGroup::create([
                "accesses_id" => $accesses['id'],
                "flag" => $accesses['use'],
                "user_groups_id" => $user_group_id,
                "user_id" => Auth::user()->id,
            ]);
        }
        return ($accesses) ? true : false;

    }

    public function checkAccess($acc)
    {
        $access = Access::where('id', '=', $acc['id'])->first();
        return ($access) ? true : false;
    }
    
    public function checkUserGroupID($user_group_id)
    {
        $user_group = UserGroup::find($user_group_id);
        return $user_group;
    }

    public function updateUserGroup($req, $user_group_id)
    {
        $user_group = UserGroup::find($user_group_id);
        $user_group->name = $req['name'];
        $user_group->save();
    }

    public function updateAccessUserGroup($accesses, $user_groups_id)
    {
        foreach ($accesses as $val) {
            if ($val['id'] === null) continue;

            $access_user_group = AccessUserGroup::where('user_groups_id', '=', $user_groups_id)
                ->where('accesses_id', '=', $val['id'])->first();

            if (!$access_user_group) {
                AccessUserGroup::create([
                    "accesses_id" => $val['id'],
                    "flag" => $val['use'],
                    "user_groups_id" => $user_groups_id,
                    "user_id" => Auth::user()->id
                ]);
            } else {
                $access_user_group = AccessUserGroup::where('user_groups_id', '=', $user_groups_id)
                    ->where('accesses_id', '=', $val['id'])->update(["flag" => $val['use']]);
            }
        }
    }

    public function checkBoolean($accesses)
    {
        return ($accesses['use'] === true || $accesses['use'] === false) ? true : false;
    }
}
