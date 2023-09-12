<?php

namespace App\Services\Accounts;

use App\Services\Service;
use Illuminate\Support\Facades\Validator;
use App\Models\Account\UserGroup;
use App\Models\Account\User;
use App\Models\Account\Access;
use App\Models\Account\UserAccessSet;
use App\Models\Account\AccessGroup;
use App\Models\Account\AccessUserGroup;
use App\Models\Administration\Punchtimecard;
use Illuminate\Support\Facades\DB;
use App\Models\store as Store;
use Illuminate\Support\Facades\Auth;

class UserService extends Service
{

    private $user_group_list;
    function __construct()
    {
        $this->user_group_list = $this->userGroupList();
    }

    // 取得user_group 列表
    public function userGroupList()
    {
        $user_group_list = UserGroup::get();
        return $user_group_list;
    }

    // 查看列表
    public function getUserGroupList()
    {
        return $this->user_group_list;
    }
    /** checkUserId
     *  確認使用者id，存在回傳true
     *  @param string|integer $id
     *  @return boolean
     */
    public function checkUserId($id)
    {
        $user = User::find($id);
        return ($user) ? true : false;
    }
    public function checkAccessId($req)
    {
        $access = Access::where('id', '=', $req['access'])->first();
        return ($access) ? true : false;
    }

    // 新增 使用者權限 至 user_access_sets
    public function createUserAccessSet($req, $user_id)
    {
        // 查詢使用者權限
        $uas = UserAccessSet::where('user_id', '=', $user_id)->where('access_id', '=', $req['access'])->first();

        if ($uas) {
            // 存在 true 更新使用者權限狀態
            $uas->flag =  $req['use'];
            $uas->save;
        } else {
            //不存在 false 新增使用者權限至`user_access_sets`
            UserAccessSet::create([
                'user_id' => $user_id,              // 使用者id
                'access_id' => $req['access'],      // 權限id
                'flag' =>  $req['use']              // 權限使用狀態
            ]);
        }
        return true;
    }
    /** checkUserGroupId
     *  確認id 是否存在`user_group`
     *  @param string|integer $id 
     *  @return boolean true 存在，false 不存在
     */
    public function checkUserGroupId($id)
    {
        $user_group = UserGroup::where('id', '=', $id)->first();
        return ($user_group) ? true : false;
    }
    /** list() 
     *  使用者列表
     */
    public function list()
    {
        $user_group = (auth()->user()->user_groups_id == 2) ? UserGroup::where('id', '!=', '0')->get()->toArray() : UserGroup::get()->toArray();

        $access_group = AccessGroup::get();
        $data = [];

        for ($i = 0; $i < count($user_group); $i++) {
            $data[$i]['id'] = $user_group[$i]['id'];
            $data[$i]["name"] = $user_group[$i]['name'];
            $data[$i]["data"] = [];
            $data[$i]["access"] = [];
            // ------------------------------------- 使用者帳號、使用狀態...等(使用者資訊) -------------------------------
            $users = User::select(
                'id',
                'name',
                'flag',
                'account',
                'store_id',
                DB::raw('(select store from stores where stores.id = store_id )as store_name')
            )->where("user_groups_id", "=", $user_group[$i]['id']);
            // 如果為店長，則查詢與店長相同分店的人員
            (auth()->user()->user_groups_id == 2) && $users = $users->where('store_id', '=', auth()->user()->store_id);

            $users = $users->get();

            for ($j = 0; $j < count($users); $j++) {
                $data[$i]["data"][$j]["id"] = $users[$j]["id"];
                $data[$i]["data"][$j]["name"] = $users[$j]["name"];
                $data[$i]["data"][$j]["use"] = (bool)$users[$j]["flag"];
                $data[$i]["data"][$j]["account"] = (!empty($users[$j]["account"])) ? $users[$j]["account"] : "";
                $data[$i]["data"][$j]["store"] = $users[$j]["store_id"];
                $data[$i]["data"][$j]["storeName"] = $users[$j]["store_name"];
                $data[$i]['data'][$j]['supportStore'] = (!empty($users[$j]['support_store_id']) & $users[$j]['support_store_id'] != 'null') ? json_decode($users[$j]['support_store_id']) : [];
            }
            // ------------------------------------------------------------------------------------
            // 權限群組
            for ($j = 0; $j < count($access_group); $j++) {
                // 權限群組 id、名稱
                $data[$i]["access"][$j]['id'] = $access_group[$j]['id'];
                $data[$i]["access"][$j]['name'] = $access_group[$j]['name'];
                // 群組的權限
                $accesses = Access::select(
                    "id",
                    "name",
                    DB::raw("case when exists(select id from `access_user_groups` where accesses_id = `accesses`.id and `flag` = 1 and `user_groups_id` = {$user_group[$i]['id']}) then 1 else 0 end as flag")
                )
                    ->where("flag", "!=", "0")
                    ->where("access_group_id", "=", $access_group[$j]['id'])
                    ->get();
                $data[$i]["access"][$j]['access'] = [];
                for ($k = 0; $k < count($accesses); $k++) {
                    $data[$i]["access"][$j]['access'][$k]["id"] = $accesses[$k]["id"];
                    $data[$i]["access"][$j]['access'][$k]["name"] = $accesses[$k]["name"];
                    $data[$i]["access"][$j]['access'][$k]["use"] = (bool)$accesses[$k]["flag"];
                }
            }
        }
        return $data;
    }

    /** checkUserData()
     *  @param array $req request data.
     *  @return json 回傳錯誤格式json.
     */
    public function checkUserData($req, $user_id = null)
    {
        $service = new UserService();
        $rules = [
            'name' => 'required|string',
            'group' => 'integer',
            // 'email' => 'required|email',
            'use' => 'required|boolean',
            'code' => 'required|string|unique:users,code|max:16|min:3',
        ];
        (!empty($req['pw'])) && $rules['pw'] = 'string|max:16|min:6';
        (!empty($req['note'])) && $rules['note'] = 'string';
        (!empty($req['email'])) && $rules['email'] = 'email';

        $validator = Validator::make(
            $req,
            $rules,
            [
                'name.required' => 'name 01',
                'group.required' => 'group 01',
                'pw.required' => 'pw 01',
                'pw.max' => "pw 104",
                'pw.min' => "pw 104",
                'email.required' => 'email 01',
                'note.required' => 'note 01',
                'use.required' => 'use 01',
                // 'code.string'=>'code 01',
                'name.string' => 'name 01',
                'group.string' => 'group 01',
                'pw.string' => 'pw 01',
                'email.string' => 'email 01',
                'note.string' => 'note 01',
                'use.string' => 'use 01',
                'group.integer' => 'group 01',
                'email.email' => "email 01",
                "use.boolean" => 'use 01',
                'store.required' => 'store 01',
                'store.integer' => 'store 01',
                'store.max' => 'store 01',
                'code.required' => 'code 01',
                'code.unique' => 'code 03',
                'code.string' => 'code 01',
                'code.max' => 'code 01',
                'code.min' => 'code 01',
            ]
            // 'account'=>'required|nuique:users,account|unique:users,code',
            // 'pin'=>'required|string|numeric'
        );

        if ($validator->fails()) {
            // // 回傳laravel預設錯誤訊息格式
            $message = $validator->errors()->first();
            $message = explode(' ', $message);
            // 自訂義格式
            $res = [
                "code" => $message[1],
                "msg" => $message[0],
                "data" => []
            ];

            // 回傳錯誤訊息
            return response()->json($res, 200);
        }

        // 檢查是否輸入為true或false字串
        if ($req['use'] !== true & $req['use'] !== false) return $service->response("01", "use");
    }


    public function getUserData($user_id)
    {
        $user = User::select(
            "id",
            "code",
            "name",
            DB::raw("'' as division")
        )->where('id', '=', $user_id)->first();

        return $user;
    }


    public function setUserAccess($user_group_id, $user_id)
    {

        // 查詢使用者群組權限
        $user_group_access = AccessUserGroup::where("user_groups_id", "=", $user_group_id)->get();

        // // 將權限寫入至使用者權限資料表
        foreach ($user_group_access as $val) {
            UserAccessSet::create([
                'user_id' => $user_id,
                'access_id' => $val['accesses_id'],
                'flag' => $val['flag']
            ]);
        }
    }

    public function checkUserSetInput($req)
    {
        $validator = Validator::make($req, [
            "id" => "required|integer",
            "access" => "required|integer",
            "use" => "required|boolean"
        ], [
            "id.required" => "id 01",
            "id.integer" => "id 01",
            "access.required" => "access 01",
            "access.integer" => "access 01",
            "use.required" => "use 01",
            "use.boolean" => "use 01"
        ]);
        return $validator;
    }

    public function getUserId()
    {
        return Auth::user()->id;
    }

    public function validStaff($request)
    {
        $rules = [];
        (!empty($request->id)) && $rules['id'] = 'exists:stores,id';

        $valid = Service::validatorAndResponse($request->all(), $rules, [
            'id.exists' => '02 id'
        ]);
        if ($valid) return $valid;
    }

    public function getUserList($store_id)
    {

        $query = User::select('id', 'name');

        (!empty($store_id)) && $query = $query->where('store_id', '=', $store_id);
        $result = $query->get()->toArray();

        return Service::response('00', 'ok', $result);
    }
}
