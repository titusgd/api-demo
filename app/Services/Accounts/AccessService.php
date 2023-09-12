<?php

namespace App\Services\Accounts;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Account\Access;
use App\Services\Service;
use App\Models\Account\AccessGroup;
use App\Models\Account\AccessUserGroup;
use App\Models\Account\UserAccessSet;

class AccessService extends Service
{

    public function validator($req)
    {
        $validator = Validator::make($req, [
            "name" => "required|string",
            "group" => "required|integer"
        ], [
            "name.required" => "name 01",
            "name.string" => "name 01",
            "group.required" => "group 01",
            "group.integer" => "group 01",
        ]);
        return $validator;
    }
    /** isAccessGroup
     *  群組是否存在 true存在，false 不存在
     *  @param integer $id
     *  @return boolean
     */
    public function isAccessGroup($id)
    {
        $is_id = AccessGroup::where("id", "=", $id)->first();
        return ($is_id) ? true : false;
    }
    /** isName
     *  名稱是否存在，存在true
     *  @param string $name 權限名稱
     *  @param int $id 權限id
     *  @return boolean
     */
    public function isName($name, $id = null)
    {
        $query = Access::where("name", "=", $name);
        ($id) && $query->where("id", "!=", $id);
        $is_name = $query->first();
        return ($is_name) ? true : false;
    }
    /** create
     *  新增資料，新增成功回傳true
     *  @param array $req
     *  @return boolean
     */
    public function create($req)
    {
        // return $req;
        $access = Access::create([
            "name" => $req["name"],
            "access_group_id" => $req["group"],
            "flag" => 0,
            "user_id" => Auth::user()->id,
        ]);
        return ($access) ? true : false;
    }
    /** checkResource
     *  資源確認，存在true，不存在false。
     *  @param string $id 權限id
     *  @param boolean 
     */
    public function checkResource($id)
    {
        $access = Access::where("id", "=", $id)->first();
        return ($access) ? true : false;
    }

    public function update($req, $id)
    {
        $access = Access::where('id', "=", $id)->update([
            "name" => $req['name'],
            "access_group_id" => $req['group'],
        ]);
        return ($access) ? true : false;
    }
    // 更新權限
    public function updateAccess($req)
    {
        // --------------------------------更新權限狀態--------------------------------
        $access = Access::find($req['id']);
        $access->flag = $req['use'];
        $access->save();
        // ---------------------------------------------------------------------------
        // 如果關閉，則刪除所有相關資料
        // 關聯table:`access_user_groups`，`user_access_sets`

        if ($req['use'] === false) {
            AccessUserGroup::where('accesses_id', "=", $req['id'])->delete();
            UserAccessSet::where("access_id", "=", $req['id'])->delete();
        }

        return true;
    }
}
