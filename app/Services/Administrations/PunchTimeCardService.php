<?php

namespace App\Services\Administrations;

use App\Services\Service;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Services\Error;
use App\Models\Administration\Punchtimecard;
use App\Models\Account\User;
use App\Models\IpList;
// App\Services\Administrations\PunchTimeCardService
class PunchTimeCardService extends Service
{
    private $user_id;
    function __construct()
    {
        $this->user_id = Auth::user()->id;
    }

    public function validate($data)
    {
        $validate = Validator::make($data, [
            "type" => [
                "required",
                "string"
            ],
        ], Error::message());
        return $validate;
    }

    public function checkIp($ip)
    {
        $ip_data = IpList::where('ip', '=', $ip)->first();
        if (!$ip_data) return Service::response('209', 'ip');
    }

    public function punchtimecard($request)
    {
        $req = $request->all();
        $servic = new Service();
        $key = $servic->getStatusKey($req['type']);
        $now_time = date("Y-m-d H:i:s");
        $data = [
            "user_id" => $this->user_id,
            "date_time" => $now_time,
            "status" => (string)$key,
            "os" => "",
            "client_ip" => $request->ip(),
        ];

        $create_ptc = function ($status) use ($now_time, $servic, $data) {
            // 預設30分鐘內無法重複打卡
            // $before_time = date("Y-m-d H:i:s", strtotime('-30 second'));
            $before_time = date("Y-m-d H:i:s", strtotime('-30 minute'));
            $select = ["id", "date_time", "user_id", "status"];
            $punchTimeCard = PunchTimeCard::select($select)
                ->where([
                    ["user_id", "=", $this->user_id],
                    ["date_time", "<=", $now_time],
                    ["date_time", ">=", $before_time],
                    ["status", "=", $status]
                ])
                ->orderBy("date_time", "DESC")
                ->get();

            $res_dt = function ($code, $msg, $dt) use ($servic) {
                list($date, $time) = explode(" ", $dt);
                $date = str_replace("-", "/", $date);
                return $servic->response($code, $msg, ["date" => $date, "time" => $time]);
            };

            if (count($punchTimeCard) == 0) {
                $punchTimeCard = PunchTimeCard::create($data);
                return $res_dt('00', 'ok', $punchTimeCard['date_time']);
            } else {
                return $res_dt(($status == 5) ? "200" : "201", 'type', $punchTimeCard[0]['date_time']);
            }
        };

        return $create_ptc($key);
    }

    // 登入狀態
    public function checkLogin()
    {
        return ($this->user_id || ($this->user_id === 0)) ? true : false;
    }

    public function getUserId()
    {
        return $this->user_id;
    }

    public function checkType($type)
    {
        return (in_array($type, ["in", "out"])) ? true : false;
    }

    public function validateList($request)
    {
        $rules = [];
        $err_msg = [];
        $valid = Service::validatorAndResponse($request->all(), $rules, $err_msg);
        if ($valid) return $valid;
    }

    public function list($request)
    {
        $group_id = auth()->user()->user_groups_id;
        $data = ($group_id == 3 || $group_id == 5) ?
            $this->getList($request->year . '-' . $request->month, auth()->user()->id) :
            $this->getMonthList($request->year . '-' . $request->month);
        return Service::response('00', 'ok', $data);
    }
    /** getList()
     *  取得指定日期及user_id的打卡列表
     */
    public function getList(string $date, int $user_id)
    {
        $query = PunchTimeCard::select('*');
        $query->where([
            ['date_time', 'like', $date . '%'],
            ['user_id', '=', $user_id]
        ]);

        $data = $query->get()->toArray();

        return $data;
    }
    /** getMonthList()
     *  使用年月取得打卡列表
     */
    public function getMonthList(string $date, array $users = null)
    {
        $query = PunchTimeCard::select('*');
        $query->where('date_time', 'like', $date . '%');
        (!empty($users)) && $query->whereIn('user_id', $users);
        $data = $query->get()->toArray();
        return $data;
    }

    /** getUserFromStore()
     *  取得指定分店使用者id
     */
    public function getUserFromStore(int $store_id)
    {
        $users = User::select('id')->where('store_id', '=', $store_id)->get()->toArray();
        $users = array_column($users, 'id');
        return $users;
    }
}
