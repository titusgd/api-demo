<?php

namespace App\Services\AbsenceReCord;

use Illuminate\Support\Facades\DB;
use App\Models\Administration\LeaveType;
use App\Models\Administration\LeaveDayoff;
use App\Models\Administration\SheduleDatetime;
use App\Models\Account\User;
use App\Models\Account\Punchtimecard;
use App\Models\store as Store;
use App\Services\Service;
use App\Traits\ReviewTrait;

use App\Traits\DateTrait;

class AbsenceReCordService extends Service
{
    use ReviewTrait;
    use DateTrait;
    public function validLeaveRecord($request)
    {
        // 驗證設定
        $rules = [
            "year" => 'required|numeric|max:2100|min:1960',
            "month" => 'required|numeric|max:12|min:1',
            "all" => 'required|boolean'
        ];
        // 錯誤訊息設定
        $error_message = [
            "year.required" => "01 year",
            "year.number" => "01 year",
            "year.max" => "01 year",
            "year.min" => "01 year",
            "month.required" => "01 month",
            "month.number" => "01 month",
            "month.max" => "01 month",
            "month.min" => "01 month",
            "all.required" => "01 all",
            "all.boolean" => "01 all"
        ];

        $valid = Service::validatorAndResponse($request->all(), $rules, $error_message);
        if ($valid) return $valid;
    }

    public function validPunchTimeCard($request)
    {
        // 驗證設定
        $rules = [
            "year" => 'required|numeric|max:2100|min:1960',
            "month" => 'required|numeric|max:12|min:1',
            "all" => 'required|boolean'
        ];
        // 錯誤訊息設定
        $error_message = [
            "year.required" => "01 year",
            "year.number" => "01 year",
            "year.max" => "01 year",
            "year.min" => "01 year",
            "month.required" => "01 month",
            "month.number" => "01 month",
            "month.max" => "01 month",
            "month.min" => "01 month",
            "all.required" => "01 all",
            "all.boolean" => "01 all"
        ];

        $valid = Service::validatorAndResponse($request->all(), $rules, $error_message);
        if ($valid) return $valid;
    }

    public function getList($request)
    {
        // 小時數轉天數
        $fn_hour_to_day = function ($hour) {
            $one_day_hour = 8;  // 8小時為一天
            return [
                'd' => (int)$hour / $one_day_hour,
                'h' => $hour % $one_day_hour
            ];
        };

        $store_query = Store::select('id', 'store');
        ($request->all == false) && $store_query->where('id', '=', auth()->user()->store_id);
        $store_data = $store_query->get()->toArray();
        $data = [];
        foreach ($store_data as $key => $value) {
            $temp = [];
            $temp['store'] = ['id' => $value['id'], 'name' => $value['store']];

            $user_ids = User::where('store_id', '=', $value['id'])->get()->pluck('id')->toArray();
            ($request->all == false) && $user_ids = [auth()->user()->id];
            // list 初始化
            $temp['list'] = [];
            // 取得假別id
            $leave_type_ids = LeaveType::select('id', 'name')->get()->toArray();
            foreach ($leave_type_ids as $key => $value2) {
                // 假單查詢，取得假單id
                $leave_query = LeaveDayoff::select('id')
                    ->where('leave_type_id', '=', $value2['id'])
                    ->where(function ($q) use ($request) {
                        $str_date = $request->year . '-' . str_pad($request->month, 2, '0', STR_PAD_LEFT) . '%';
                        $q->where('start', 'like', $str_date)->orWhere('end', 'like', $str_date);
                    })
                    ->whereIn('user_id', $user_ids);
                $leave_data = $leave_query->get()->toArray();
                $leave_data_ids = array_column($leave_data, 'id');
                // 查詢審核列表，假單是否通過審核
                $review_data = self::getReviewModel()
                    ->select('fk_id')
                    ->where('type', '=', 'leaveDayoff')
                    ->where('status', '=', '3')
                    ->where('rank', '=', '1')
                    ->whereIn('fk_id', $leave_data_ids)
                    ->get()->toArray();
                $leave_data_ids = array_column($review_data, 'fk_id');
                $leave_dayoff_info = LeaveDayoff::select(
                    'id',
                    'user_id',
                    DB::raw('(select name from users where users.id = user_id) as user_name'),
                    'start',
                    'end',
                    'total_hour',
                    'leave_type_id'
                )
                    ->whereIn('id', $leave_data_ids)->get()->toArray();
                $input_date = $request->year . '-' . str_pad($request->month, 2, '0', STR_PAD_LEFT);
                // 整理假單資訊
                foreach ($leave_dayoff_info as $key2 => $val) {
                    if (substr($val['start'], 0, 7) != substr($val['end'], 0, 7)) {
                        // 分兩段時間 
                        $start = date('Y-m-d', strtotime($val['start']));
                        $end = date('Y-m-d', strtotime($val['end']));

                        // 查詢第一天跟最後一天的上下班時間
                        $shedule_datetime_data = SheduleDatetime::select('date', 'time')
                            ->whereRaw("JSON_CONTAINS(user_id, '" . $val['user_id'] . "' )")
                            ->where(function ($q) use ($start, $end) {
                                $q->where('date', '=', $start)->orWhere('date', '=', $end);
                            })->get()->toArray();

                        // 查無資料則跳出
                        if (empty($shedule_datetime_data)) continue;

                        // 上下班時間格式化
                        $temp2 = array();
                        foreach ($shedule_datetime_data as $key3 => $val2) {
                            $temp_time = json_decode($val2['time'], true);
                            $temp2[] = $val2['date'] . ' ' . $temp_time[0] . ':00';
                            $temp2[] = $val2['date'] . ' ' . $temp_time[1] . ':00';
                        }

                        if (strpos($start, $input_date) !== false) {
                            // 選到月底
                            $month_end = date('Y-m-t', strtotime($val['start']));
                            $days = date_diff(new \DateTime($start), new \DateTime($month_end));
                            $days = $days->d;
                            $first_day = date_diff(new \DateTime($val['start']), new \DateTime($temp2[1]));
                            $days += ($first_day->h * 0.125);
                        } else {
                            $month_end = date('Y-m-01', strtotime($val['end']));
                            $days = date_diff(new \DateTime($month_end), new \DateTime($end));
                            $days = $days->d;
                            $last_day = date_diff(new \DateTime($temp2[2]), new \DateTime($val['end']));
                            $days += ($last_day->h * 0.125);
                        }
                        $leave_dayoff_info[$key2]['total_hour'] = $days * 8; //用小時為單位
                    }
                }
                $temp_staff = [];
                foreach ($leave_dayoff_info as $key2 => $val2) {
                    if (!empty($temp_staff[$val2['user_id']])) {
                        $day = floor($val2['total_hour'] * 0.125);
                        $hour = $val2['total_hour'] % 8;
                        $temp_staff[$val2['user_id']]['day'] += $day;
                        $temp_staff[$val2['user_id']]['hour'] += $hour;
                        $temp_staff[$val2['user_id']]['minute'] += 0;
                    } else {
                        $temp_staff[$val2['user_id']]['id'] = $val2['user_id'];
                        $temp_staff[$val2['user_id']]['name'] = $val2['user_name'];
                        $day = floor($val2['total_hour'] * 0.125);
                        $hour = $val2['total_hour'] % 8;
                        $temp_staff[$val2['user_id']]['day'] = $day;
                        $temp_staff[$val2['user_id']]['hour'] = $hour;
                        $temp_staff[$val2['user_id']]['minute'] = 0;
                    }
                }

                $user_data = User::select('id', 'name')->whereIn('id', $user_ids)->get()->toArray();
                foreach ($user_data as $key2 => $val2) {
                    if (empty($temp_staff[$val2['id']])) {
                        $temp_staff[$val2['id']] = [
                            'id' => $val2['id'],
                            'name' => $val2['name'],
                            'day' => 0,
                            'hour' => 0,
                            'minute' => 0,
                        ];
                    }
                }

                $temp['list'][] = [
                    'leaveType' => [
                        'id' => $value2['id'],
                        'name' => $value2['name']
                    ],
                    'staff' => $this->keyValueToArray($temp_staff)
                ];
            }
            
            // ---------------------------------------------------------------------------------------------------------
            // 遲到
            $user_data = User::select('id', 'name')->whereIn('id', $user_ids)->get()->toArray();
            $temp_be_last_list = [];
            foreach ($user_data as $key2 => $val2) {
                // 查詢排班表，找出上班時間
                $shedule_date_time = SheduleDatetime::select('date', 'time')
                    ->where('date', 'like', $request->year . '-' . str_pad($request->month, 2, '0', STR_PAD_LEFT) . '%')
                    ->whereRaw("JSON_CONTAINS(user_id, '" . $val2['id'] . "' )")
                    ->get()->toArray();
                // 找尋打卡紀錄
                $day = 0;
                $hour = 0;
                $second = 0;
                foreach ($shedule_date_time as $key3 => $val3) {
                    $time_arr = json_decode($val3['time'], true);
                    $where_datetime = $val3['date'] . ' ' . $time_arr['0'] . ':00';

                    // 打卡記錄查詢
                    $punch_time_card_data = Punchtimecard::select('user_id', 'date_time', 'status')
                        ->where('user_id', '=', $val2['id'])
                        ->where('status', '=', '5')
                        ->where('date_time', 'like', $val3['date'] . '%')
                        ->where('date_time', '>', $where_datetime)
                        ->get()->toArray();

                    if (!$punch_time_card_data) continue;

                    // 有遲到，檢查是否有請假
                    $leave_data = LeaveDayoff::select(
                        'id',
                        DB::raw(
                            '(select status from reviews where reviews.type = "leaveDayoff" and reviews.fk_id = leave_dayoffs.id and reviews.status = 3 )as review_status'
                        )
                    )
                        ->where('start', '<', $punch_time_card_data['0']['date_time'])
                        ->where('end', '>', $punch_time_card_data['0']['date_time'])
                        ->where('user_id', '=', $val2['id'])
                        ->get()->toArray();
                    if ($leave_data) continue;
                    // 計算遲到時間
                    $last_time = date_diff(new \DateTime($where_datetime), new \DateTime($punch_time_card_data['0']['date_time']));
                    $day += $last_time->d;
                    $hour += $last_time->h;
                    $second += $last_time->i;
                }
                $temp_data = [];
                $temp_data['id'] = $val2['id'];
                $temp_data['name'] = $val2['name'];
                $temp_data['day'] = $day;
                $temp_data['hour'] = $hour;
                $temp_data['minute'] = $second;
                $temp_be_last_list[] = $temp_data;
            }
            $type_last = end($temp['list']);
            $temp['list'][] = [
                'leaveType' => [
                    'id' => (int)$type_last['leaveType']['id'] + 1,
                    'name' => '遲到'
                ],
                'staff' => $this->keyValueToArray($temp_be_last_list)
            ];
            $data[] = $temp;
        }
        return Service::response('00', 'ok', $data);
    }

    public function punchTimeCardList($year, $month, $all = false)
    {

        // 取得所有分店
        $store_query = Store::select('id', 'store');
        ($all == false) && $store_query->where('id', '=', auth()->user()->store_id);
        $store_data = $store_query->get()->toArray();

        $data = [];
        foreach ($store_data as $key => $value) {
            $temp = [];
            $temp['store']['id'] = $value['id'];
            $temp['store']['name'] = $value['store'];
            $temp['list'] = [];
            $user_ids = User::where('store_id', '=', $value['id'])->get()->pluck('id')->toArray();
            ($all == false) && $user_ids = [auth()->user()->id];
            // 查詢使用者打卡列表
            $temp2 = [];
            $month_days = date('t', strtotime($year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT)));

            for ($i = 0; $i < $month_days; $i++) {
                $datetime = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . str_pad(($i + 1), 2, '0', STR_PAD_LEFT);
                $Punchtimecard_data = Punchtimecard::select(
                    'date_time',
                    'user_id',
                    DB::Raw('(select store_id from users where users.id = user_id)as user_store_id'),
                    DB::Raw('(select name from users where users.id = user_id)as user_name'),
                    'status'
                )
                    ->where('date_time', 'like', $datetime . ' %')
                    ->whereIn('user_id', $user_ids)->get()->toArray();

                $temp3 = [];
                foreach ($Punchtimecard_data as $key2 => $val2) {
                    $temp3[$val2['user_id']]['id'] = $val2['user_id'];
                    $temp3[$val2['user_id']]['name'] = $val2['user_name'];
                    $date_time_format = self::dateFormat($val2['date_time']);
                    ($val2['status'] == '5') && $temp3[$val2['user_id']]['in'] = $date_time_format['1'];
                    ($val2['status'] == '6') && $temp3[$val2['user_id']]['out'] = $date_time_format['1'];
                }
                $temp_key = $i + 1;
                $temp2[$temp_key] = $temp3;
            }

            foreach ($temp2 as $key => $val2) {
                $day = $key;
                $temp_staff = [];

                foreach ($val2 as $val3) array_push($temp_staff, $val3);

                $temp['list'][] = ['day' => $day, 'staff' => $temp_staff];
            }
            $data[] = $temp;
        }
        return Service::response('00', 'ok', $data);
    }

    /** keyValueToArray()
     *  keyValue 轉 陣列 
     */
    public function keyValueToArray($key_value)
    {
        $temp = [];
        foreach ($key_value as $key => $val)  array_push($temp, $val);
        return $temp;
    }

    public function punchTimeCardList2($year, $month, $all = false)
    {
        // 取得所有分店
        $store_query = Store::select('id', 'store');
        ($all == false) && $store_query->where('id', '=', auth()->user()->store_id);
        $store_data = $store_query->get()->toArray();

        $data = [];
        foreach ($store_data as $key => $value) {
            // echo "store_id:{$value['id']}" . PHP_EOL;
            $temp = [];
            // 分店id 、名稱設定
            $temp['store']['id'] = $value['id'];
            $temp['store']['name'] = $value['store'];
            $temp['list'] = [];

            // 取得分店排班表
            $query_shedule_date_time = SheduleDatetime::select('id', 'date', "time", "user_id", "store_id")
                ->where('store_id', '=', $value['id'])
                ->where('date', 'like', $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '%');
            // (!$all) && $query_shedule_date_time->whereRaw("JSON_CONTAINS(user_id, '" . auth()->user()->id . "' )");
            $shedule_date_time = $query_shedule_date_time->get()->toArray();
            if (!$shedule_date_time) {
                // if ($all == false) {
                //     $user_data = User::select('id', 'name')->where('id', '=', auth()->user()->id)->first();
                //     $temp['list'] = [
                //         'day' => '',
                //         'staff' => [
                //             'id' => $user_data->id,
                //             'name' => $user_data->name,
                //             'in' => '',
                //             'out' => ''
                //         ]
                //     ];
                // } else {
                    $temp['list'] = [
                        'day' => '',
                        'staff' => [
                            'id' => '',
                            'name' => '',
                            'in' => '',
                            'out' => ''
                        ]
                    ];
                // }
                continue;
            }
            foreach ($shedule_date_time as $key2 => $value2) {
                if ($all == false) {
                    $user_ids = [0=>auth()->user()->id];
                } else {
                    $user_ids = json_decode($value2['user_id']);
                }
                $Punchtimecard_data = Punchtimecard::select(
                    'date_time',
                    'user_id',
                    DB::Raw('(select store_id from users where users.id = user_id)as user_store_id'),
                    DB::Raw('(select name from users where users.id = user_id)as user_name'),
                    'status'
                )
                    ->where('date_time', 'like', $value2['date'] . '%')
                    ->whereIn('user_id', $user_ids)->get()->toArray();

                $temp_list = [];
                $day_arr = explode('-', $value2['date']);
                $temp_list['day'] = (int)$day_arr['2'];
                $temp_list['staff'] = [];
                $temp_arr = [];
                foreach ($Punchtimecard_data as $key3 => $val3) {
                    $temp_arr[$val3['user_id']]['id'] = $val3['user_id'];
                    $temp_arr[$val3['user_id']]['name'] = $val3['user_name'];
                    $date_time_format = self::dateFormat($val3['date_time']);
                    ($val3['status'] == '5') && $temp_arr[$val3['user_id']]['in'] = $date_time_format['1'];
                    ($val3['status'] == '6') && $temp_arr[$val3['user_id']]['out'] = $date_time_format['1'];
                }
                // 取得所有名單
                $user_id = [];
                // 查所有值班表id
                $sd_user_id = User::select('id', 'name')->where('store_id', '=', $value['id'])->get()->pluck('id')->toArray();
                $sd_user_id2 = User::select('id', 'name')->whereRaw("JSON_CONTAINS(support_store_id, '" . $value['id'] . "' )")->get()->pluck('id')->toArray();
                $user_id = array_merge($sd_user_id, $sd_user_id2);
                $user_id = array_unique($user_id);
                $user_ids = array_merge($user_ids, $user_id);
                $user_ids = array_unique($user_ids);
                $user_data = User::select('id', 'name')->whereIn('id', $user_ids)->get()->toArray();
                
                foreach ($user_ids as $key3 => $val3) {
                    // 如果為自己
                    if ($all == false) {
                        if ($val3 !== auth()->user()->id) continue;
                    }
                    if (empty($temp_arr[$val3])) {
                        $user_data = User::select('id', 'name')->where('id', '=', $val3)->first();
                        if (!$user_data) continue;
                        $temp_arr[$user_data->id]['id'] = $user_data->id;
                        $temp_arr[$user_data->id]['name'] = $user_data->name;
                        $temp_arr[$user_data->id]['in'] = '';
                        $temp_arr[$user_data->id]['out'] = '';
                    }
                }
                // 都無資料寫入一筆為空值
                $temp_list['staff'] = $this->keyValueToArray($temp_arr);
                if (empty($temp_list['staff']['0'])) {
                    $temp_list['staff']['0'] = [
                        'id' => '',
                        'name' => '',
                        'in' => '',
                        'out' => ''
                    ];
                }

                $temp['list'][] = $temp_list;
            }

            $data[] = $temp;
        }
        // 合併重複天數的staff資料
        foreach ($data as $key => $val) {
            $temp_list = [];
            foreach ($val['list'] as $key2 => $val2) {
                if (!empty($temp_list[$val2['day']])) {
                    foreach($val2['staff'] as $key3 => $val3){
                        if(!empty($temp_list[$val2['day']]['staff'][$val3['id']])){
                            if($val3['in']==''&& $val3['out']=='') continue;
                            if(empty($temp_list[$val2['day']]['staff'][$val3['id']]['in'])){
                                $temp_list[$val2['day']]['staff'][$val3['id']]['in'] = $val3['in'];
                            }

                            if(empty($temp_list[$val2['day']]['staff'][$val3['id']]['out']) && isset($val3['out']) ){
                                $temp_list[$val2['day']]['staff'][$val3['id']]['out'] = $val3['out'];
                            }
                        }else{
                            $temp_list[$val2['day']]['staff'][$val3['id']] = $val3;
                        }
                    }
                    
                } else {
                    $temp_list[$val2['day']]['day'] = $val2['day'];
                    if ($val2['staff']['0']['id'] != '') {
                        foreach ($val2['staff'] as $key3 => $val3) {
                            $temp_list[$val2['day']]['staff'][$val3['id']] = $val3;
                        }
                    } else {
                        $temp_list[$val2['day']]['staff'] = [];
                    }
                    
                }
                
            }
            
            $data[$key]['list'] = $this->keyValueToArray($temp_list);
        }
        foreach($data as $key=>$val){
            foreach($val['list']as $key2=>$val2){
                $data[$key]['list'][$key2]['staff'] = $this->keyValueToArray($val2['staff']);
            }
        }
        return Service::response('00', 'ok', $data);
    }
}
