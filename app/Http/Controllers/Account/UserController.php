<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Account\User;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Hash;
use App\Services\Accounts\UserService;
use App\Services\Service;
use App\Models\Account\UserAccessSet;
use App\Models\Account\AccessUserGroup;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    private $service;

    function __construct()
    {
        $this->service = new UserService;
    }

    public function list(Request $request)
    {
        // $this->service->userOptionLogAdd($request->url(),'00');
        return $this->service->response('00', 'ok', $this->service->list());
    }

    public function add(Request $request)
    {
        $service = new UserService();
        $req = $request->all();
        $ss = new Service;

        $check_user = $this->service->checkUserData($req);
        if ($check_user) return $check_user;

        if ($req['pw'] === null || $req['pw'] === 0 || $req['pw'] === '' || $req['pw'] === '0') $req['pw'] = 'password';

        $data = [
            'name' => $req['name'],
            'password' => $req['pw'],
            'email' => (!empty($req['email'])) ? $req['email'] : '',
            'note' => (!empty($req['note'])) ? $req['note'] : '',
            'flag' => $req['use'],
            'code' => $req['code'],
            'phone' => '',
            'tel' => '',
            'address' => '',
            'state' => true,
            'pw' => $req['pw'],
        ];

        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        return $service->response('00', 'ok', $user->id);
    }

    public function update(Request $request)
    {
        $service = new UserService;
        $req = $request->all();
        $ss = new Service;
        $id = (!empty($request->id)) ? "," . $request->id : "";
        $rules = [
            'id' => 'required|integer|exists:users,id',
            'name' => 'required|string',
            'group' => 'integer|exists:user_groups,id',
            // 'email' => 'required|email',
            'use' => 'required|boolean',
            // 'store' => 'integer|max:999',
            'account' => 'required|string|unique:users,account' . $id . '|unique:users,code' . $id . '|max:16|min:3',
            'pin' => 'required|string|numeric'
        ];

        (!empty($req['pw'])) && $rules['pw'] = 'string|max:16|min:8';
        (!empty($req['note'])) && $rules['note'] = 'string';
        (!empty($req['email'])) && $rules['email'] = 'email';
        if (!empty($req['supportStore'])) {
            $rules['supportStore'] = 'array';
            $rules['supportStore.*'] = 'integer|exists:stores,id';
        }
        $emsg = [
            // 'code.required'=>'code 01',
            'id.required' => '01 id',
            'id.integer' => '01 id',
            'id.exists' => '02 id',
            'name.required' => '01 name',
            // 'group.required' => '01 group',
            // 'group.exists' => '02 group',
            'pw.required' => '01 pw',
            'pw.max' => "104 pw",
            'pw.min' => "104 pw",
            'email.required' => '01 email',
            'note.required' => '01 note',
            'use.required' => '01 use',
            // 'code.string'=>'code 01',
            'name.string' => '01 name',
            // 'group.string' => '01 group',
            'pw.string' => '01 pw',
            'email.string' => '01 email',
            'note.string' => '01 note',
            'use.string' => '01 use',
            // 'group.integer' => '01 group',
            'email.email' => "01 email",
            "use.boolean" => '01 use',
            // 'store.required' => '01 store',
            // 'store.integer' => '01 store',
            // 'store.max' => '01 store',
            // 'store.exists' => '02 store',
            'account.required' => '01 account',
            'account.unique' => '03 account',
            'account.string' => '01 account',
            'account.max' => '01 account',
            'account.min' => '01 account',
            // 'pin.required' => '01 pin',
            // 'pin.string' => '01 pin',
            // 'pin.numeric' => '01 pin',
        ];
        $vali = $ss->validatorAndResponse($req, $rules, $emsg);
        if ($vali) return $vali;
        // 更新資料
        $data = [
            'name' => $req['name'],
            'user_groups_id' => $req['group'],
            'email' => (!empty($req['email'])) ? $req['email'] : "",
            'note' => (!empty($req['note']) ? $req['note'] : ''),
            'flag' => ($req['use'] == true) ? true : false,
            'store_id' => $req['store'],
            'support_store_id' => json_encode($req['supportStore'])
        ];

        (!empty($req['account'])) && $data['account'] = $req['account'];
        (!empty($req['pin'])) && $data['pin'] = $req['pin'];

        $data['support_store_id'] = json_encode($req['supportStore']);

        // pw 選填判斷
        if (!empty($req['pw'])) {
            if (array_key_exists('pw', $req)) {
                $data['pw'] = $req['pw'];
                $data['password'] = Hash::make($req['pw']);
            }
        }

        $user = User::find($request->id);
        if ($user['user_groups_id'] != $req['group']) {

            UserAccessSet::where('user_id', '=', $req['id'])->delete();

            $user_group_access = AccessUserGroup::where('user_groups_id', '=', $req['group'])->get()->toArray();

            $temp_user_access_set = [];
            foreach ($user_group_access as $key => $val) {
                $temp_user_access_set[$key]['user_id'] = $req['id'];
                $temp_user_access_set[$key]['access_id'] = $val['accesses_id'];
                $temp_user_access_set[$key]['flag'] = $val['flag'];
            }
            DB::table('user_access_sets')->insert($temp_user_access_set);
        }

        $update = User::where('id', '=', $request->id)->update($data);

        $user = User::find($request->id);

        return $service->response('00', 'ok', $user->code);
    }

    public function set_access(Request $request)
    {
        $req = $request->all();
        $ss = new Service;
        $rules = [
            'id' => 'required|integer|exists:users,id',
            'access' => 'required|integer|exists:accesses,id',
            'use' => 'required|boolean'
        ];
        $message = [
            'id.required' => '01 id',
            'id.integer' => '01 id',
            'id.exists' => '02 id',
            'access.required' => '01 access',
            'access.integer' => '01 access',
            'access.exists' => '02 access',
            'use.required' => '01 use',
            'use.boolean' => '01 use'
        ];
        $vali = $ss->validatorAndResponse($req, $rules, $message);
        if ($vali) return $vali;

        // 4. 寫入user_access_set
        return ($this->service->createUserAccessSet($req, $req['id'])) ?
            $this->service->response('00', 'ok') : $this->service->response('999', '');
    }

    /**user_data
     *  讀取單筆使用者info
     */
    public function data(Request $request)
    {
        // content to array        
        $req = $request->all();

        // 檢查id 是否為 null 或是 ""
        if (empty($req['id'])) {
            // 如果為 "" 或是 null 則將登入者 id 寫入req['id']
            $id = $this->service->getUserId();
            $req['id'] = $id;
        } else {
            // 判斷 $req['id']是否為數字
            if (!is_numeric($req['id'])) return $this->service->response('01', 'id');
        }

        // 檢查資料是否存在，不存在處理
        if (!$this->service->checkUserId($req['id'])) return $this->service->response('02', 'id');

        // 使用者資料查詢、取得
        $user_data = $this->service->getUserData($req['id']);
        return $this->service->response('00', 'ok', $user_data);
    }
    // 使用者狀態變更
    public function status(Request $request)
    {
        $req = $request->all();
        $vali = $this->service->validatorAndResponse($req, [
            'id' => 'required|integer|exists:users,id',
            'use' => 'required|boolean'
        ], [
            'id.required' => '01 id',
            'id.integer' => '01 id',
            'id.exists' => '02 id',
            'use.required' => '01 use',
            'use.boolean' => '01 use',
        ]);

        if ($vali) return $vali;

        $user = User::find($req['id']);
        $user->flag = $req['use'];
        $user->save();

        return $this->service->response('00', 'ok');
    }

    public function staff(Request $request)
    {
        $valid = $this->service->validStaff($request);

        if ($valid) return $valid;

        $list = $this->service->getUserList($request->id);
        return $list;
    }
}
