<?php

namespace App\Services\Administrations;

use App\Services\Service;
use App\Models\Administration\Shedule;
use App\Models\Administration\SheduleDatetime;
use Illuminate\Support\Facades\DB;
use App\Traits\DateTrait;
use App\Models\Account\User;
use App\Models\store as Store;
use Illuminate\Support\Collection;

class SheduleService extends Service
{
    use DateTrait;
    const rules = [
        'store' => 'required|integer|exists:stores,id',
        'year' => 'required|integer|min:1911',
        'month' => 'required|integer|max:12|min:1',
    ];
    const err_msg = [
        // 分店
        "store.required" => '01 store',
        "store.integer" => "01 store",
        "store.exists" => '02 store',
        // 年
        "year.required" => "01 year",
        "year.min" => "01 year",
        "year.integer" => "01 year",
        // 月
        "month.required" => "01 month",
        "month.integer" => "01 month",
        "month.max" => "01 month",
        "month.min" => "01 month",
        // "hours.max.integer"=>'01 hours',

    ];
    public function validList($request)
    {
        $valid = Service::validatorAndResponse($request->all(), self::rules, self::err_msg);
        if ($valid) return $valid;
    }

    public function validUpdate($request)
    {
        $rules = array_merge(self::rules, [
            // "hours.max" => "required|integer",
            // "hours.min" => "required|integer",
            "shift" => "required|array",
            "shift.*" => "required|array",
            "shift.*.*" => "required|string",
            "period.*.*.time" => "required|array|min:2",
            "period.*.*.time.*" => "required|string",
            "period.*.*.staff" => "array",
            "period.*.*.staff.*" => "integer"
        ]);
        $error_message = array_merge(self::err_msg, [
            // 小時
            // "hours.max.required" => "01 max",
            // "hours.max.integer" => "01 max",
            "hours.min.required" => "01 min",
            "hours.min.integer" => "01 min",
            // 預設時段
            "shift.array" => "01 shift",
            "shift.required" => "01 shift",
            "shift.*.required" => "01 shift",
            "shift.*.array" => "01 shift",
            "shift.*.min" => "01 shift",
            "shift.*.*.required" => "01 shift",
            "shift.*.*.string" => "01 shift",
            // 班表
            "period.*.*.time.required" => "01 time",
            "period.*.*.time.min" => "01 time",
            "period.*.*.time.array" => "01 time",
            "period.*.*.time.*.required" => "01 time",
            "period.*.*.time.*.date_format" => "01 time",
            "period.*.*.time.*.string" => "01 time",
            "period.*.*.staff.required" => "01 staff",
            "period.*.*.staff.array" => "01 staff",
            "period.*.*.staff.*.integer" => "01 staff",
        ]);

        // 輸入資料基本驗證
        $valid = Service::validatorAndResponse($request, $rules, $error_message);
        if ($valid) return $valid;
        // 月份檢查 月份天數是否符合
        $check = $this->checkInputDay($request);
        if ($check) return $check;
        // // 檢查人員是否存在
        // $check_user = $this->checkInputUser($request);
        // if ($check_user) return $check_user;

        // 時間重複會回傳true
        $check_time_in_range = function (array $time1, array $time2) {
            $time_1 = new \DateTime($time1['start']);
            $time_2 = new \DateTime($time1['end']);

            $time_3 = new \DateTime($time2['start']);
            $time_4 = new \DateTime($time2['end']);
            // 時間一致，跳過此次判斷
            if (($time_1 == $time_3) && ($time_2 == $time_4)) return false;
            // 檢查開始與結束時間，是否在區間內
            if ($time_3 > $time_1 && $time_3 < $time_2) return true;
            if ($time_4 > $time_1 && $time_4 < $time_2) return true;
        };
        // 初始化
        $time_repeat = false;
        $err_data = [];
        // 檢查人員當天排班時間是否有重疊
        foreach ($request['period'] as $key => $val) {

            // 1. 先檢查同一天內，人員是否有重複。
            $staff = [];
            foreach ($val as $key2 => $val2) {
                $staff = array_merge($staff, $val2['staff']);
            }
            // 取得差集並移除重複的id
            $staff_u = array_unique($staff);
            $staff = array_diff_assoc($staff, $staff_u);
            $staff = array_unique($staff);
            // 如果有查出id有重複，則檢查時間有沒有重疊
            if (!empty($staff)) {

                // 1.取出時間陣列
                $date = $request['year'] . '-' . str_pad($request['month'], 2, '0', STR_PAD_LEFT) . '-' . str_pad($key, 2, '0', STR_PAD_LEFT);
                $time_arr = [];
                foreach ($val as $key2 => $val2) {
                    foreach ($staff as $val3) {
                        if (in_array($val3, $val2['staff'])) {
                            $time_arr[] = [
                                'start' => $date . ' ' . $val2['time'][0] . ':00',
                                'end' => $date . ' ' . $val2['time'][1] . ':00',
                                'staff' => $val3
                            ];
                        }
                    }
                }
                // 字串分割為陣列，使用 " "
                $explode_blank = function ($str) {
                    return explode(' ', $str);
                };
                // 字串分割為陣列，使用 ":"
                $explode_colon = function ($str) {
                    return explode(':', $str);
                };

                // 檢查 時間的區段是否有重複
                foreach ($time_arr as $val2) {
                    if ($time_repeat) break;

                    foreach ($time_arr as $val3) {
                        $check = $check_time_in_range($val2, $val3);
                        if ($check) {
                            $store_data = Store::select('store as store_name')->where('id', '=', $request['store'])->first();
                            $date_pluse = str_replace('-', '/', $date);

                            $start = $explode_blank($val2['start']);
                            $start = $explode_colon($start[1]);
                            $end = $explode_blank($val2['end']);
                            $end = $explode_colon($end[1]);

                            // $start = explode(' ',$val2['start']);
                            // $start = explode(':',$start[1]);
                            // $end = explode(' ',$val2['end']);
                            // $end = explode(':',$end[1]);

                            $err_data = [
                                "store" => $store_data->store_name,
                                'date' => $date_pluse,
                                'time' => [
                                    $start[0] . ':' . $start[1],
                                    $end[0] . ':' . $end[1],
                                ]
                            ];
                            $time_repeat = true;
                            break;
                        }
                    }
                }
                if ($time_repeat) break;
            }
            // 時間重疊 跳出
            if ($time_repeat) break;
        }
        if ($time_repeat) return Service::response('203', 'time', $err_data);

        // 資料庫權表查詢，時間區段是否有使用中
        // 取得名單，查詢資料庫，指定日期
        $time_repeat = false;
        $err_data = [];
        foreach ($request['period'] as $key => $val) {
            foreach ($val as $key2 => $val2) {
                if (empty($val2['staff'])) continue;

                $date = $request['year'] . '-' . str_pad($request['month'], 2, '0', STR_PAD_LEFT) . '-' . str_pad($key, 2, '0', STR_PAD_LEFT);
                $staff = json_encode($val2['staff']);
                $sd = SheduleDatetime::select(
                    'id',
                    'date',
                    'time',
                    'user_id',
                    DB::raw('(select store from stores where stores.id = store_id)as store_name')
                )
                    ->where('date', '=', $date);
                $sd = $sd->whereRaw("JSON_CONTAINS(user_id, '" . $staff . "' )");
                $sd = $sd->where('store_id', '!=', $request['store']);
                $sd = $sd->get()->toArray();
                // 組合獲取的上下班時段
                foreach ($sd as $key3 => $val3) {

                    $temp_dt = json_decode($val3['time'], true);
                    $temp_work_time[] = [
                        'start' => $val3['date'] . ' ' . $temp_dt['0'] . ':00',
                        'end' => $val3['date'] . ' ' . $temp_dt['1'] . ':00'
                    ];
                    $date_1 = new \DateTime($val3['date'] . ' ' . $temp_dt['0'] . ':00'); // 資料表時間-s
                    $date_2 = new \DateTime($val3['date'] . ' ' . $temp_dt['1'] . ':00'); // 資料表時間-e

                    $input_date_1 = new \DateTime($date . ' ' . $val2['time'][0] . ':00');
                    $input_date_2 = new \DateTime($date . ' ' . $val2['time'][1] . ':00');

                    (($date_1 == $input_date_1) || ($date_2 == $input_date_2)) && $time_repeat = true;
                    ($input_date_1 > $date_1 && $input_date_1 < $date_2) && $time_repeat = true;
                    ($input_date_2 > $date_1 && $input_date_2 < $date_2) && $time_repeat = true;

                    if ($time_repeat) {
                        $t_time = json_decode($val3['time']);
                        $t_date = str_replace("/", "", $val3['date']);
                        $err_data = [
                            "store" => $val3['store_name'],
                            'date' => $t_date,
                            'time' => [
                                $t_time[0], $t_time[1]
                            ]
                        ];
                        break;
                    }
                }
                if ($time_repeat) break;
            }
            if ($time_repeat) break;
        }

        if ($time_repeat) return Service::response('203', 'time', $err_data);
    }

    public function validPerson($request)
    {
        $valid = Service::validatorAndResponse($request->all(), [
            'year' => 'required|date_format:Y',
            'month' => 'required|min:1|max:12',
        ], [
            'year.required' => '01 year',
            'year.date_format' => '01 year',
            'month.required' => '01 month',
            'month.max' => '01 month',
            'month.min' => '01 month',
        ]);

        if ($valid) return $valid;
    }

    public function getList($request)
    {
        $res_data = [];
        // 查詢條件組合
        $addWhere = function ($query, $where) {
            foreach ($where as $key => $val) {
                switch ($key) {
                    case 'where':
                        $query = $query->where($val);
                        break;
                    case 'orderBy':
                        $query = $query->orderBy($val[0], $val[1]);
                        break;
                }
            }
            return $query;
        };
        // 主表查詢
        $selectShedule = function ($where, $first = true) use ($addWhere) {
            $shedule = Shedule::select('id', 'hour_max', 'hour_min', 'shift');
            $shedule = $addWhere($shedule, $where);
            $shedule = ($first) ? $shedule->first() : $shedule->get();
            return $shedule;
        };
        // 次表查詢
        $selectSheduleItem = function ($where) use ($addWhere) {
            $shedule_item = SheduleDatetime::select('*');
            $shedule_item = $addWhere($shedule_item, $where);
            return $shedule_item->get();
        };
        //使用者列表查詢 
        $selectUser = function ($store_id) {
            $users = User::select('id', 'code', 'name')
                ->where('store_id', '=', $store_id)
                ->orWhereRaw("JSON_CONTAINS(support_store_id, '{$store_id}' )")
                ->get()
                ->toArray();
            foreach ($users as $key => $val) {
                $users[$key]['time'] = 0;
                $users[$key]['enough'] = (bool)false;
                $users[$key]['over'] = (bool)false;
            }
            return $users;
        };
        // 基本資料設定
        $setBaseData = function ($shedule_data) use (&$res_data) {
            $res_data['hours']['max'] = $shedule_data['hour_max'];
            $res_data['hours']['min'] = $shedule_data['hour_min'];
            $res_data['shift'] = json_decode($shedule_data['shift']);
        };
        // 排班表設定 + 格式化
        $setPeriod = function ($list) use (&$res_data, $request) {
            $res_data['period'] = [];
            foreach ($list as $key => $val) {
                $day = explode("-", $val['date']);
                $day = (int)$day[2];
                $res_data['period'][$day][] = ['time' => json_decode($val['time']), 'staff' => json_decode($val['user_id'])];
            }
            $ymd = self::getDay($request->year . '-' . $request->month . '-01');

            $period_list_count = count($res_data['period']);
            $temp = [];
            if ($period_list_count != $ymd) {
                for ($i = 0; $i < $ymd; $i++) {
                    $key = $i + 1;
                    if (!empty($res_data['period'][$key])) {
                        $temp[$key][] = [$res_data['period'][$key]];
                    } else {
                        foreach ($res_data['shift'] as $val2) {
                            $temp[$key][] = ['time' => $val2, 'staff' => []];
                        }
                    }
                }
            } else {
                $temp = $res_data['period'];
            }
            $res_data['period'] = $temp;
        };
        // 設定班人員資料
        $setStaff = function ($store_id, $shedule_item_list) use (&$res_data, $selectUser) {
            $user_list = $selectUser($store_id);
            $temp = [];
            foreach ($shedule_item_list as $key => $val) {
                $temp_user = json_decode($val['user_id']);
                foreach ($temp_user as $val2) {
                    $temp[$val2] = (!empty($temp[$val2])) ? $temp[$val2] + $val['time_total'] : $temp[$val2] = $val['time_total'];
                }
            }
            foreach ($temp as $key => $val) {
                $temp[$key] = (float)number_format(($val / 60), 2);
            }
            foreach ($user_list as $key => $val) {
                if (!empty($temp[$val['id']])) {
                    $user_list[$key]['time'] = $temp[$val['id']];
                    $user_list[$key]['enough'] = ($temp[$val['id']] < $res_data['hours']['min']) ? (bool)false : (bool)true;
                    $user_list[$key]['over'] = ($temp[$val['id']] < $res_data['hours']['max']) ? (bool)false : (bool)true;
                    
                }
            }
            $res_data['staff'] = $user_list;
        };

        $shedule = $selectShedule([
            'where' => [
                ['store_id', '=', $request->store],
                ['year', '=', $request->year],
                ['month', '=', $request->month],
            ]
        ]);

        if ($shedule) {
            //主表有資料
            $setBaseData($shedule);
            $shedule_item_list = $selectSheduleItem(['where' => [['shedule_id', '=', $shedule->id]]])->toArray();
            $setPeriod($shedule_item_list);
            $setStaff($request->store, $shedule_item_list);
            return Service::response('00', 'ok', $res_data);
        } else {
            // 主表無資料
            $res_data['hours'] = ["max" => 0, "min" => 0];
            $setStaff($request->store, []);
            $res_data['shift'] = [];
            $res_data['period'] = (object)[];
            return Service::response('00', 'ok', $res_data);
        }
    }

    public function addData($request)
    {
        $shedule_data = $this->addDBShedule($request);
        $shedule_datetime = $this->addDBSheduleDatetime($request, $shedule_data->id);
        return Service::response('00', 'ok');
    }

    public function updateData($request, $shedule_id)
    {
        // Shedule
        $this->updateDBShedule($request, $shedule_id);

        // 1. 刪除 舊資料
        // 2. 新增
        // update SheduleDatetime
        $this->updateDBSheduleDatetime($request, $shedule_id);

        return Service::response('00', 'ok');
    }

    public function addDBSheduleDatetime($req, $shedule_id)
    {
        $temp = [];
        foreach ($req->period as $key => $val) {
            foreach ($val as $val2) {
                $time = 0;
                (!empty($val2['time'])) && $time = $this->countTime($val2['time'][0], $val2['time'][1]);

                array_push($temp, [
                    'shedule_id' => $shedule_id,
                    'date' => $req->year . '-' . str_pad($req->month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($key, 2, '0', STR_PAD_LEFT),
                    'time' => json_encode($val2['time']),
                    'time_total' => $time,
                    'store_id' => $req->store,
                    'user_id' => json_encode($val2['staff'])
                ]);
            }
        }

        SheduleDatetime::insert($temp);
    }

    public function addDBShedule($req)
    {
        $shedule = new Shedule;
        $this->sheduleOrm($shedule, $req);
        $shedule->save();
        return $shedule;
    }

    public function updateDBShedule($req, $id)
    {
        $shedule = Shedule::find($id);
        $this->sheduleOrm($shedule, $req);
        $shedule->save();
    }

    public function sheduleOrm($shedule, $req)
    {
        // max and min default
        $max = (!empty($req->max)) ? $req->hours['min'] : 0;
        $min = (!empty($req->max)) ? $req->max : 0;

        $shedule->store_id = $req->store;
        $shedule->year = $req->year;
        $shedule->month = $req->month;
        $shedule->hour_max = $max;
        $shedule->hour_min = $min;
        $shedule->shift = json_encode($req->shift);
    }

    public function updateDBSheduleDatetime($req, $id)
    {
        SheduleDatetime::where('shedule_id', '=', $id)->delete();
        $this->addDBSheduleDatetime($req, $id);
    }

    /** checkInputUser()
     *  確認輸入使用者是否存在
     */
    public function checkInputUser($req)
    {
        $temp_user = [];
        foreach ($req['period'] as $val) {
            foreach ($val as $val2) {
                $temp_user = array_merge($val2['staff'], $temp_user);
            }
        }
        // user id to array
        $temp_user = array_values(array_unique($temp_user));

        $users = User::select('id')
            ->whereIn('id', $temp_user)
            ->get();

        if (count($users) != count($temp_user)) return Service::response('02', 'staff');
    }

    public function checkData($request)
    {
        $result = Shedule::select('id')
            ->where('store_id', '=', $request->store)
            ->where("year", '=', $request->year)
            ->where("month", '=', $request->month)
            ->first();
        return ($result) ? $result->id : false;
    }

    /** countTime() 
     *  時段區間內分鐘數。
     *  @param String $start 開始時間
     *  @param String $end 結束時間
     *  @return integer
     */
    public function countTime($start, $end)
    {
        return $this->timeToSecond($end) - $this->timeToSecond($start);
    }

    /** timeToSecond() 
     *  時間 時分( h:i) 轉 分(i)
     *  @param String $time 時間 "時:分" 
     *  @return float 回傳分鐘數
     */
    public function timeToSecond($time)
    {
        $time = explode(':', $time);
        $time = ($time[0] * 60) + $time[1];
        return $time;
    }
    /** checkInputDay($date)
     *  確認輸入隻指定日期的月份天數，是否正確。
     *  @param object $request
     *  @return void|object
     */
    public function checkInputDay($request)
    {
        $month_total_day = self::getDay("{$request['year']}-{$request['month']}-01");
        if (count($request['period']) != $month_total_day) return Service::response("01", "period");
    }

    public function getPersonList($year, $month)
    {
        $output_data = [];
        $output_data['hours'] = '';

        // 取得 所有store_id
        $temp_store_ids = [];

        $temp_store_ids[] = auth()->user()->store_id;

        $get_support_store_id = ((auth()->user()->support_store_id != 'null') && (auth()->user()->support_store_id != '[]')) ?
            json_decode(auth()->user()->support_store_id) :
            [];

        $temp_store_ids = array_merge($temp_store_ids, $get_support_store_id);
        $temp_store_ids = array_unique($temp_store_ids);
        $temp_store_ids2 = [];

        foreach ($temp_store_ids as $key => $val) $temp_store_ids2[] = $val;

        // 月份補數，補成2位
        $month = (strlen($month) < 2) ? str_pad($month, 2, '0', STR_PAD_LEFT) : $month;

        $shedule_datetime = SheduleDatetime::select(
            'date',
            'time',
            'store_id',
            DB::raw('(select store from stores where stores.id = store_id)as store_name')
        )
            ->where('date', 'like', $year . '-' . $month . '-%')
            ->whereIn('store_id', $temp_store_ids)
            ->whereRaw("JSON_CONTAINS(user_id, '" . auth()->user()->id . "' )")
            ->get()->toArray();
        $temp_data_arr = [];
        // 取得查詢月份天數
        $days = date('t', strtotime($year . '-' . $month . '-01'));
        // 依據日期天數建立一個陣列，裡面存放空物件
        for ($i = 0; $i < $days; $i++) {
            $temp_key = $i + 1;
            $temp_data_arr[$temp_key] = new \stdClass();
        }

        $temp_time_total = 0;
        // 依據日期，將取得的資料存放入指定的位置中
        foreach ($shedule_datetime as $key => $val) {
            // 取得日期日，並將日期作為索引
            $temp_day = explode('-', $val['date']);
            $temp_day = (int) $temp_day[2];
            $temp_time_arr = json_decode($val['time']);
            // 計算上班總時數
            $diff = $this->datetimeDiff(
                $val['date'] . " " . $temp_time_arr[0] . ":00",     // start
                $val['date'] . " " . $temp_time_arr[1] . ":00"      // end
            );

            $temp_time_total += $diff->h;

            $temp_data_arr[$temp_day] = [
                "time" => [$temp_time_arr[0], $temp_time_arr[1]],
                "store" => [
                    'id' => $val['store_id'],
                    'name' => $val['store_name']
                ]
            ];
        }

        $output_data['hours'] = $temp_time_total;
        $output_data['store_id'] = $temp_store_ids2;
        $output_data['period'] = $temp_data_arr;

        return Service::response('00', 'ok', $output_data);
    }

    // 時間差計算
    public function datetimeDiff(string $datetime1, string $datetime2): object
    {
        $temp_diff = date_diff(
            new \DateTime(date('Y-m-d H:i:s', strtotime($datetime1))),
            new \DateTime(date('Y-m-d H:i:s', strtotime($datetime2)))
        );
        return $temp_diff;
    }
}
