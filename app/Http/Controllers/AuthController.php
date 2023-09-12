<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Auth\AuthenticationException;
// Auth認證;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Hash;

use App\Services\Auth\AuthService;
use Illuminate\Support\Facades\Validator;
use App\Models\PersonalAccessToken;
use App\Models\LoginLog;

class AuthController extends Controller
{

    public function user()
    {
        return Auth::user();
    }

    // 登入
    public function login(Request $request)
    {
        $service = new AuthService;
        $req = $request->all();

        $valid = $service->validLoginData($request);
        if ($valid) return  $valid;

        // exit();
        // 錯誤碼
        // 00：ok
        // 101：無此帳號 | 102：密碼錯誤 | 103：帳號停用 | 104：密碼長度過短、過長(8~16)

        $req = $request->all();
        // 欄位檢測
        $data = [
            "code" => $req["id"],
            "password" => $req["pw"],
        ];

        // 查詢資料庫是否有此使用者的code，查到後，取得第一筆資料。
        $user = User::where('code', $data['code'])->first();
        // 沒有帳號
        if (!$user) {
            $this->intoLog($request->id, $request->ip(), 'login', '101');
            return $service->response("101", "id");
        };

        // 帳號停用
        if ($user->flag === 0 || $user->flag === false) {
            $this->intoLog($request->id, $request->ip(), 'login', '103');
            return $service->response('103', "id");
        };

        // 密碼錯誤
        if (!$user || !Hash::check($data['password'], $user->password)) {
            $this->intoLog($request->id, $request->ip(), 'login', '102');
            return $service->response("102", 'pw');
        };
        PersonalAccessToken::where('tokenable_id', '=', $user->id)->delete();

        // 手動登入
        $attempt = Auth::attempt([
            'code' => $user['code'],
            'password' => $data['password']
        ]);
        // Hash::check(請求密碼,使用者資料庫密碼) 加密比對驗證
        (!$user || !Hash::check($data['password'], $user->password)) && throw new AuthenticationException();

        $token = explode('|', $user->createToken($user->name)->plainTextToken);
        $per_acc_tok = PersonalAccessToken::select('id', 'updated_at')->where('tokenable_id', '=', auth()->user()->id)->get()->last();
        PersonalAccessToken::where('id', '=', $per_acc_tok['id'])->update([
            'last_used_at' => $per_acc_tok['updated_at']
        ]);

        // 有效期限 env("SANCTUM_TTL")預設為分鐘
        $expiration_time = env("SANCTUM_TTL"); // minimum to seconds
        // 秒
        $expiration_time = $expiration_time * 60;
        $this->intoLog($request->id, $request->ip(), 'login', '00');
        return $service->response('00', "ok", ['token' => $token[1], 'maxAge' => $expiration_time]);
    }

    // 登出
    public function logout(Request $request)
    {
        // $request->user()->currentAccessToken()->delete();

        $service = new AuthService();
        // // 撤銷token
        Auth::user()->tokens()->delete();
        $this->intoLog($request->id, $request->ip(), 'login', '00');
        return $service->response('00', "ok");
    }

    // use Laravel ORM create data
    public function intoLog($client_id, $client_ip, $action, $status)
    {
        $login_log = new LoginLog;
        $login_log->input_id = $client_id;      // code or account.
        $login_log->client_action = $action;    // client action ex: login 、logout.
        $login_log->client_ip = $client_ip;     // client ip.
        $login_log->login_status = $status;     // status response code.
        $login_log->save();
    }
}
