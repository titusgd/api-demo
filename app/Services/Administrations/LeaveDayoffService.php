<?php

namespace App\Services\Administrations;

use Illuminate\Support\Facades\DB;

use App\Models\Administration\LeaveDayoff;
use App\Models\Administration\LeaveType;
use App\Models\Administration\LeaveAnnual;
use App\Models\Account\User;
use App\Models\Administration\SheduleDatetime;
use App\Services\Service;

use App\Traits\ReviewTrait;
use App\Traits\DateTrait;
use App\Traits\NotifyTrait;

class LeaveDayoffService extends Service
{
    use ReviewTrait;
    use DateTrait;
    use NotifyTrait;

    public function validList($request)
    {
        $service = new Service;

        // $data = [
        //     "date" => [
        //         "start" => (!empty($request->date['start'])) ? $request->date['start'] : '',
        //         "end" => (!empty($request->date['end'])) ? $request->date['end'] : '',
        //     ],
        //     "count" => $request->count,
        //     "page" => $request->page
        // ];

        $valid = $service->validatorAndResponse($request->all(), [
            "page" => "required|integer",
            "count" => "required|integer",
            "date.start" => "required|date",
            "date.end" => "required|date",
        ], [
            "page.required" => "01 page",
            "page.integer" => "01 page",
            "date.start.required" => "01 start",
            "date.start.date" => "01 start",
            "date.end.required" => "01 end",
            "date.end.date" => "01 end",
        ]);
        if ($valid) return $valid;
    }

    public function validUpdate($request)
    {

        $valid = Service::validatorAndResponse($request->all(), [
            "id" => "required|integer|exists:*.leave_dayoffs,id",
            "type" => 'required|integer|exists:*.leave_types,id',
            // 時間陣列
            "date.start.0" => 'required|date_format:Y/m/d',
            "date.start.1" => 'required|date_format:H:i:s',
            "date.end.0" => 'required|date_format:Y/m/d',
            "date.end.1" => 'required|date_format:H:i:s',

            'note' => 'required|string'
        ], [
            'id.required' => '01 id',
            'id.integer' => '01 id',
            'id.exists' => '02 id',
            'type.required' => '01 type',
            'type.integer' => '01 type',
            'type.exists' => '02 type',
            'date.start.0.required' => '01 start',
            'date.start.0.date_format' => '01 start',
            'date.start.1.required' => '01 start',
            'date.start.1.date_format' => '01 start',
            'date.end.0.required' => '01 end',
            'date.end.0.date_format' => '01 end',
            'date.end.1.required' => '01 end',
            'date.end.1.date_format' => '01 end',
            'note.required' => '01 note',
            'note.string' => '01 note'
        ]);
        if ($valid) return $valid;
    }

    public function validDel($request)
    {
        $valid = Service::validatorAndResponse(
            $request->all(),
            [
                'id' => 'required|integer|exists:*.leave_dayoffs,id',
            ],
            [
                'id.required' => '01 id',
                'id.integer' => '01 id',
                'id.exists' => '02 id',
            ]
        );
        if ($valid) return $valid;
        // 取得審核列表，如果已有人審核，則無法刪除
        $review = self::getReviewModel();
        $review_data = $review->select('id', 'user_id', 'status')
            ->where('type', '=', 'leaveDayoff')
            ->where('fk_id', '=', $request->id)
            ->where('status', '!=', '1')
            ->get()
            ->toArray();

        if ($review_data) return Service::response('202', 'id');
    }

    public function delLeaveDayoff($leave_id)
    {
        $del = LeaveDayoff::where('id', '=', $leave_id)->delete();
        return Service::response('00', 'ok');
    }

    public function validAdd($request)
    {
        // 1.檢查資料輸入格式

        $service = new Service();
        $relus = [ // "id"=>"required|integer|exists:leave_dayoffs,id",
            "type" => 'required|integer|exists:*.leave_types,id',
            // 時間陣列
            "date.start.0" => 'required|date_format:Y/m/d',
            "date.start.1" => 'required|date_format:H:i:s',
            "date.end.0" => 'required|date_format:Y/m/d',
            "date.end.1" => 'required|date_format:H:i:s',

            // 'note' => 'required|string'

        ];
        (!empty($request->note)) && $relus['note'] = 'string';
        $valid = Service::validatorAndResponse(
            $request->all(),
            $relus,
            [
                'id.required' => '01 id',
                'id.integer' => '01 id',
                'id.exists' => '02 id',
                'type.required' => '01 type',
                'type.integer' => '01 type',
                'type.exists' => '02 type',
                'date.start.0.required' => '01 start',
                'date.start.0.date_format' => '01 start',
                'date.start.1.required' => '01 start',
                'date.start.1.date_format' => '01 start',
                'date.end.0.required' => '01 end',
                'date.end.0.date_format' => '01 end',
                'date.end.1.required' => '01 end',
                'date.end.1.date_format' => '01 end',
                'note.required' => '01 note',
                'note.string' => '01 note'
            ]
        );
        
        if ($valid) return $valid;

        // 檢查時間區段
        //          開始 結束
        $time_start = implode(' ', $request->date['start']);
        $time_end = implode(' ', $request->date['end']);
        $result = LeaveDayoff::select('id', 'start', 'end')
            ->where('user_id', '=', auth()->user()->id)
            ->whereRaw(
                "((start <= '{$time_start}' and end > '{$time_start}') or (start < '{$time_end}' and end > '{$time_end}') or (start > '{$time_start}' and end <= '{$time_end}'))"
            )
            ->get()->toArray();
        $temp_leave_ids = array_column($result, 'id');
        // 取得審核結果 -> 2 未通過的
        $review_model = self::getReviewModel();
        $review_data = $review_model->select('id', 'fk_id', 'status')
            ->where('type', '=', 'leaveDayoff')
            ->where('status', '=', '2')
            ->whereIn('fk_id', $temp_leave_ids)
            ->get()->toArray();
        $temp_leave_ids2 = array_column($review_data, 'fk_id');
        $temp_leave_ids2 = array_unique($temp_leave_ids2);
        $temp_leave_ids = array_diff($temp_leave_ids, $temp_leave_ids2);

        if (!empty($temp_leave_ids)) return $service->response('203', 'date');
        // 取得，並檢查排班時段
        // $leave_dayoff_time = $this->getLeaveHour($request->date);
        // if ($leave_dayoff_time == 0) return $service->response('204', 'date');
        
        // 檢查排班表時段
        
        // $check_time_in_range = function ($datetime) use ($request, $service) {
        //     $shedule_data = $this->selectShedule($request->date);
        //     $temp_datetime_arr = [];
        //     foreach ($shedule_data as $key => $val) {

        //         $temp_time = json_decode($val['time']);
        //         $temp_time = [$temp_time[0], $temp_time[1]];
        //         foreach ($temp_time as $key2 => $val2) {
        //             $temp_datetime_arr[] = "{$val['date']} {$val2}:00";
        //         }
        //     }

        //     $temp_max_time = max($temp_datetime_arr);
        //     $temp_min_time = min($temp_datetime_arr);
        //     if (strtotime($datetime) < strtotime($temp_min_time) || strtotime($datetime) > strtotime($temp_max_time)) {
        //         return $service->response('206', 'date');
        //     }
        // };
        // $start_in_range = $check_time_in_range($time_start);
        // if ($start_in_range) return $service->response('206', 'date');

        // $end_in_range = $check_time_in_range($time_end);
        // if ($end_in_range) return $service->response('206', 'date');

        // 取得請假最小時數

        $min_time = LeaveType::select('id', 'min')->where('id', '=', $request->type)->first()->toArray();
        $time_diff = $this->datetimeDiff(
            $request->date['start']['0'] . ' ' . $request->date['start']['1'],
            $request->date['end']['0'] . ' ' . $request->date['end']['1'],
        );

        $time = ($time_diff->d * 24) + ($time_diff->h); // 天數轉小時
        if ($time < $min_time['min']) return $service->response('205', 'date');
    }

    public function updateLeaveDayoff($request)
    {
        $update_fn = function ($request, $update_type = false) {
            $start = implode(' ', $request->date['start']);
            $end = implode(' ', $request->date['end']);
            $hour = $this->getLeaveHour($request->date);
            $leave_data = LeaveDayoff::find($request->id);

            $leave_data->start = $start;
            $leave_data->end = $end;
            $leave_data->note = $request->note;
            $leave_data->total_hour = $hour;
            ($update_type) && $leave_data->leave_type_id = $request->type;
            $leave_data->save();
        };
        $reviw_model = self::getReviewModel();
        $review_data = $reviw_model->select('id', 'type', 'fk_id', 'user_id', 'status')
            ->where('type', '=', 'leaveDayoff')
            ->where('fk_id', '=', $request->id)
            ->where('status', '!=', '1')
            ->get()->toArray();
        // 進入審核流程，只有店負責以上權限方可修改
        $res = ['code' => '00', 'msg' => 'ok'];
        if (!empty($review_data)) {
            // 正職:3 、 PT:5 不可修改
            if (auth()->user()->user_groups_id == '3' || auth()->user()->user_groups_id == '5') {
                $update_fn($request, true);
            } else {
                $res = ['code' => '202', 'msg' => 'id'];
            }
        } else {
            $update_fn($request);
        }
        return Service::response($res['code'], $res['msg']);
    }

    public function addLeaveDayoff($request)
    {
        $note = '';
        (!empty($request->note)) && $note = $request->note;
        $leave_dayoff = $this->createLeaveDayoff($request->type, $request->date, $note);
        return Service::response('00', 'ok');
    }

    /** getLeaveHour()
     *  取得排班時段
     */
    public function getLeaveHour($date)
    {
        $shedule_data = $this->selectShedule($date);
        $leave_dayoff_time = 0;
        foreach ($shedule_data as $key => $val) {
            // 判斷開始時間
            $time = json_decode($val['time']);
            $sd_start_time = $val['date'] . ' ' . $time[0] . ":00";
            $sd_end_time = $val['date'] . ' ' . $time[1] . ":00";
            $sub_time = [];
            // 取得上班時間
            $sub_time['start'] = (strtotime($date['start'][0] . ' ' . $date['start'][1]) >= strtotime($sd_start_time)) ?
                $date['start'][0] . ' ' . $date['start'][1] : $sd_start_time;

            $sub_time['end'] = (strtotime($date['end'][0] . ' ' . $date['end'][1]) >= strtotime($sd_end_time)) ?
                $sd_end_time : $date['end'][0] . ' ' . $date['end'][1];

            // 取得小時數
            $sub_time = date_diff(new \DateTime($sub_time['start']), new \DateTime($sub_time['end']));
            $leave_dayoff_time += $sub_time->h - (($sub_time->h >= 6) ? 1 : 0);
        }
        return $leave_dayoff_time;
    }
    /** createLeaveDayoff
     *  @param int $type_id 請假類別
     *  @param array $date  請假日期
     *  @param string $note 備註
     *  @param bool $time_count
     */
    public function createLeaveDayoff($type_id, $date, $note, $time_count = false)
    {
        $start = implode(' ', $date['start']);
        $end = implode(' ', $date['end']);
        $leave_dayoff_time = $this->datetimeDiff($start, $end);

        // 時間是否扣除休息時間
        if ($time_count) {
            if ($leave_dayoff_time->h > 5 || $leave_dayoff_time->days == 0) $leave_dayoff_time = $leave_dayoff_time->h - 1;
        } else {
            $leave_dayoff_time = $leave_dayoff_time->h;
        }
        $leave_dayoff = new LeaveDayoff();
        $leave_dayoff->number = '';
        $leave_dayoff->user_id = auth()->user()->id;
        $leave_dayoff->substitute_id = 0;
        $leave_dayoff->start = $start;
        $leave_dayoff->end = $end;
        $leave_dayoff->total_hour = $leave_dayoff_time;
        $leave_dayoff->note = $note;
        $leave_dayoff->leave_type_id = $type_id;
        $leave_dayoff->save();

        $leave_dayoff->number = $this->getNumber($leave_dayoff->id);
        $leave_dayoff->save();
        // -------------------- 新增審核 ----------------------------------------
        // 取得審核清單
        $rank_list = self::getReviewRank('leaveDayoff', 3);
        
        // 查詢所屬店長
        $user_id = User::select('id')
            ->where('user_groups_id', '=', '2')
            ->where('store_id', '=', auth()->user()->store_id)
            ->where('flag', '!=', '0')
            ->first();
        $rank_list['4'] = ($user_id) ? $user_id->id : '0';
        // dd('338');
        $application_review = self::createReview(
            rank: $rank_list,
            fk_id: $leave_dayoff->id,
            type: 'leaveDayoff'
        );

        // 無店長，無rank[4]，改成系統審核，直接給過;
        if (!$user_id) {
            $now_date = date('Y-m-d H:i:s');
            $review_model = self::getReviewModel();
            $review_data = $review_model->select('id', 'user_id', 'rank')
                ->where('type', '=', 'leaveDayoff')
                ->where('fk_id', '=', $leave_dayoff->id)
                ->where('rank', '=', '4')
                ->where('user_id', '=', '0')
                ->get()
                ->toArray();

            $review_update = $review_model::find($review_data['0']['id']);
            $review_update->status = 3;
            $review_update->date = $now_date;
            $review_update->save();
        }

        // -------------------------------- 新增通知 ----------------------------------------------------------------
        $leave_data = LeaveDayoff::select(
            'number',
            DB::raw('(select name from users where users.id = user_id) as user_name'),
            DB::raw('(select name from leave_types where leave_types.id = leave_type_id) as leave_name'),
        )->where('id', '=', $leave_dayoff->id)->first()->toArray();

        $title = '假單 審核通知';
        $content = "單號 : {$leave_data['number']} 
            員工 : {$leave_data['user_name']} 
            假別 : {$leave_data['leave_name']} 
            日期 : " . str_replace('-','/',$start) . " ~ " . str_replace('-','/',$end) . " ";

        $reviews_user = self::selectReviewsUserList('leaveDayoff', $leave_dayoff->id);

        $notice = self::addNotice(
            $title,
            $content,
            // 如果無user_id，則寫入通知給rank[3]
            (!$user_id) ? [$reviews_user[1]['user_id']] : [$reviews_user[0]['user_id']]
        );
        self::updateNoticeLink($notice->id, env('APP_URL') . '/administration/leave/leaveForm');
        $update_notice = self::updateNoticeTypeAndFkId($notice->id, 'leaveDayoff', $leave_dayoff->id);
        $update_notice_user = self::updateNoticeUserTypeAndFkId($notice->id, 'leaveDayoff', $leave_dayoff->id);
    }

    /** getNumber
     *  取得假單單號
     */
    public function getNumber($pk_id)
    {
        $code = str_pad($pk_id, 8, '0', STR_PAD_LEFT);
        return $code;
    }

    /** selectShedule
     *  查詢排班表
     *  @param array $date['start'=>,'end'=>]
     */
    public function selectShedule($date)
    {
        $shedule_data = SheduleDatetime::select('date', 'time')
            ->where('date', '>=', $date['start'][0])
            ->where('date', '<=', $date['end'][0])
            ->whereRaw("JSON_CONTAINS(user_id, '" . auth()->user()->id . "' )")
            ->get()->toArray();
        return $shedule_data;
    }

    /** getList
     *  取得列表
     */
    public function getList(array $date, int $count)
    {
        $setPage = function ($leave_data) use (&$output) {
            $output['page']['total'] = $leave_data['last_page'];
            $output['page']['countTotal'] = $leave_data['total'];
            $output['page']['page'] = $leave_data['current_page'];
        };

        $now_date_year = date('Y');

        // 取得可休假列表
        $leave_list = $this->getLeaveType();
        // 取得特休天數
        $leave_data = $this->getLeaveAnnual(auth()->user()->id, $now_date_year);
        $leave_list[0]['days'] = array_sum(array_column($leave_data, 'pai_day'));

        // 取得輸入年度，人員請假列表
        $leave_dayoff_list = $this->getLeaveDayoff(auth()->user()->id, $date, $count);

        // 設定分頁
        $setPage($leave_dayoff_list['list']);

        $output['data']['leave'] = [];
        // dd($leave_list);
        foreach ($leave_list as $key => $value) {
            $output['data']['leave'][] = [
                'id' => $value['id'],
                'total' => $value['days'],
                'used' => (empty($leave_dayoff_list['total'][$value['id']])) ? 0 : $leave_dayoff_list['total'][$value['id']]
            ];
        }

        $output['data']['list'] = [];
        if (!empty($leave_dayoff_list['list']['data'])) {
            foreach ($leave_dayoff_list['list']['data'] as $key => $val) {
                $create = $this->dateFormat($val['create']);
                $start = $this->dateFormat($val['start']);
                $end = $this->dateFormat($val['end']);
                // 時數計算
                $day = $val['total_hour'] / 8;
                $day = explode('.', $day);
                $day = $day[0];
                $hour = $val['total_hour'] % 8;

                $temp_arr = [];
                foreach ($val['reviews'] as $review_key => $review_val) {
                    $temp_arr[] = [
                        'id' => $review_val['user_id'],
                        'name' => $review_val['user_name'],
                        'rank' => $review_val['rank'],
                        'audit' => $this->numberToStatus($review_val['status']),
                        'reason' => $review_val['note'],
                        'date' => (!empty($review_val['date'])) ? self::dateFormat($review_val['date']) : []
                    ];
                }

                $output['data']['list'][] = [
                    'id' => $val['id'],
                    'code' => $val['number'],
                    'type' => [
                        'id' => $val['leave_type_id'],
                        'name' => $val['leave_type_name']
                    ],
                    'date' => [
                        'apply' => $create,
                        'start' => $start,
                        'end' => $end
                    ],
                    'count' => [
                        'days' => (float)$day,
                        'hours' => $hour
                    ],
                    'note' => $val['note'],
                    'status' => $temp_arr,

                ];
            }
        }
        return Service::response_paginate('00', 'ok', $output['data'], $output['page']);
    }
    /** getUserTakeOfficeDate
     *  取得使用者到職日，如果沒有到職日，則回傳建立此筆資料的時間
     *  @param int $user_id 使用者id
     *  @return string yyyy-mm-dd H:i:s
     */
    public function getUserTakeOfficeDate($user_id)
    {
        $select_user_data = function ($user_id) {
            return User::select('id', 'take_office_date', 'created_at as create')
                ->where('id', '=', $user_id)
                ->first()->toArray();
        };
        $user_data = $select_user_data($user_id);
        // $user_data = User::select('id', 'take_office_date', 'created_at as create')->where('id', '=', $user_id)->first()->toArray();
        // 如果沒有到職日，以新增日期為到職日。
        if (empty($user_data['take_office_date'])) {

            $year = new \DateTime($user_data['create']);
            $year = $year->format('Y-m-d H:i:s');
            $year = explode(' ', $year);

            $temp = User::find($user_id);
            $temp->take_office_date = $year[0];
            $temp->save();
            $user_data = User::select('id', 'take_office_date', 'created_at as create')->where('id', '=', $user_id)->first()->toArray();
        }
        return $user_data['take_office_date'];
    }

    /** getLeaveAnnual
     *  取得特休
     *  @param int $user_id
     *  @param string $date
     */
    public function getLeaveAnnual($user_id, $date)
    {
        // 查詢特休，取得特休年分、天數
        $selectLeaveAnnual = function ($user_id, $date) {
            return LeaveAnnual::select('id', 'user_id', 'year', 'pai_day')
                ->where('user_id', '=', $user_id)
                ->where('start', 'like', $date . '%')
                // ->where('year', '=', $date)
                ->get()->toArray();
        };

        $list = $selectLeaveAnnual($user_id, $date);

        if (empty($list)) {
            // 清除紀錄
            LeaveAnnual::where('user_id', '=', $user_id)->delete();
            // 取得到職日期
            $take_office_date = $this->getUserTakeOfficeDate($user_id);

            // $take_office_date = $this->getUserTakeOfficeDate(auth()->user()->id);
            $annual_days = $this->trialLeaveDate($take_office_date, $user_id);

            $insert_arr = [];
            foreach ($annual_days as $key => $val) {
                $take_office_date = explode(' ', $val['take_of_date']);
                $start = explode(' ', $val['start']);
                $end = explode(' ', $val['end']);
                $insert_arr[] = [
                    'user_id' => $val['user_id'],
                    'year' => $val['year'],
                    'take_office_day' => $take_office_date[0], // 到職日期
                    'start' => $start['0'],
                    'end' => $end['0'],
                    'pai_day' => $val['pai_day'],
                    'formula' => $val['formula'],
                    'content' => $val['content'],
                    'version' => '',
                ];
            }
            LeaveAnnual::insert($insert_arr);
            $list = $selectLeaveAnnual($user_id, $date);
        }

        $take_office_date = auth()->user()->take_office_date;
        $now_date = date('Y-m-d') . ' 24:00:00';
        $diff = $this->datetimeDiff($take_office_date, $now_date);
        // 到職天數未滿183 則回傳空陣列，沒有特休
        return ($diff->days < 183) ? [] : $list;
    }

    public function getLeaveDayoff($user_id, $date, $count)
    {
        $date_start = (is_array($date['start'])) ? implode(' ', $date['start']) : $date['start'] . " 00:00:00";
        $date_end = (is_array($date['end'])) ? implode(' ', $date['end']) : $date['end'] . " 23:59:59";

        $list = LeaveDayoff::select(
            'id',
            DB::raw('(select store_id from users where users.id = user_id) as store_id'),
            'number',
            'leave_type_id',
            'total_hour',
            DB::raw('(select `name` from `leave_types` where leave_types.id = leave_type_id)as leave_type_name'),
            'start',
            'end',
            'note',
            'created_at as create',
        )->with([
            'reviews' => function ($query) {
                // 多條件查詢
                $query->select(
                    'id',
                    'type',
                    'fk_id',
                    'rank',
                    'date',
                    'status',
                    'user_id',
                    'note',
                    DB::raw('(select `name` from `users` where id = reviews.user_id )as user_name')
                )->where('type', '=', 'leaveDayoff')->orderBy('rank', 'desc');
            }
        ])
            ->where('user_id', '=', $user_id)
            // TODO:日期查詢，日後可能需要打開
            // ->where('start', '>=', $date_start)
            // ->where('end', '<=', $date_end)
            ->orderBy('id', 'desc')
            ->paginate($count)
            ->toArray();

        $temp_arr = [];
        foreach ($list['data'] as $key => $val) {

            $review_status_arr = [
                $val['reviews']['0']['status'],
                $val['reviews']['1']['status'],
                $val['reviews']['2']['status'],
                $val['reviews']['3']['status'],
            ];
            // 如果審核為:未通過(2)，則不納入計算。
            if (!in_array('2', $review_status_arr)) {

                if (!empty($temp_arr[$val['leave_type_id']])) {
                    $temp_arr[$val['leave_type_id']]['hour'] += (float)$val['total_hour'];
                } else {
                    $temp_arr[$val['leave_type_id']]['hour'] = (float)$val['total_hour'];
                }
            }
        }

        // 將小時數轉換為天，目前已一天8小時計算     8hr = 1day
        foreach ($temp_arr as $key => $val) {
            $temp_arr[$key] = $val['hour'] / 8;
        }

        return ['list' => $list, 'total' => $temp_arr];
    }

    // 取得休假類別列表
    /** getLeaveType
     *  取得休假類別列表資料
     */
    public function getLeaveType()
    {
        $leave_list = LeaveType::select('id', 'days')->get()->toArray();
        return $leave_list;
    }

    // 特休試算
    /** trialLeaveDate
     *  特休試算
     *  @param string $take_office_date
     */
    public function trialLeaveDate($take_office_date, $user_id)
    {
        // 計算日期區間天數
        $getDays = function ($start_d, $end_d) {
            $start_datetime = new \DateTime($start_d);
            $end_datetime = new \DateTime($end_d);
            $date_diff = date_diff($start_datetime, $end_datetime);
            return $date_diff;
        };
        // 小數點四捨五入，只會出現0.5
        $decimalPlace = function ($number) {
            $number2 = explode(".", $number);
            if (empty($number2[1]) || ($number2[1] == 5)) return $number;
            
            return  round($number);
        };
        // 只要有小數點，都是0.5
        $decimalPlace2 = function ($number) {
            $number = explode(".", $number);
            if ($number[0] == 0 && empty($number[1])) return 0;
            if (empty($number[1])) $number[1] = 0;
            $temp_number = 0;
            $temp_number = $number[0] + ((!empty($number[0]) && $number[1] != 0) ? 0.5 : 0);
            
            return $temp_number;
        };

        // 2016特休制度
        $v2016LeaveDays = function (int $year) {
            $temp_arr = [
                'str' => '', 'leave_day' => 0
            ];
            $text = function ($type, $text1 = null, $text2 = null, $text3 = null) {
                $text = '';
                switch ($type) {
                    case 1:
                        $text = '未滿 ' . $text1 . ' 年者，依規定給予 ' . $text2 . ' 天';
                        break;
                    case 2:
                        $text = '滿 ' . $text1 . ' 年者未滿 ' . $text2 . '年，依規定給予 ' . $text3 . ' 天';
                        break;
                    case 3:
                        $text = '滿 ' . $text1 . ' 年以上者，依規定給予 ' . $text2 . ' 天，每一年加1日，最多 30 日 /' . $text3;
                        break;
                }
                return $text;
            };
            $leave_day = 0;
            switch ($year) {
                case 0:
                    $temp_arr['str'] = $text(1, '1', '0');
                    // $temp_arr['str'] = '未滿 1 年者，依規定給予 0 天';
                    $temp_arr['leave_day'] = 0;

                    break;
                case ($year >= 1 && $year < 3):
                    $temp_arr['str'] = $text(2, '1', '3', '7');
                    // $temp_arr['str'] = '滿 1 年者未滿 3年，依規定給予 7 天';
                    $temp_arr['leave_day'] = 7;
                    break;
                case ($year >= 3 && $year < 5):
                    $temp_arr['str'] = $text(2, '3', '5', '10');
                    // $temp_arr['str'] = '滿 3 年者未滿 5 年，依規定給予 10 天';
                    $temp_arr['leave_day'] = 10;
                    break;
                case ($year >= 5 && $year < 10):
                    // $leave_day = 14;
                    $temp_arr['str'] = $text(2, '5', '10', '14');
                    // $temp_arr['str'] = '滿 5 年者未滿 10 年，依規定給予 14 天';
                    $temp_arr['leave_day'] = 14;
                    break;
                case ($year >= 10):
                    $leave_day = 15 + ($year - 10);
                    $leave_day = ($leave_day >= 30) ? 30 : $leave_day;
                    $temp_arr['str'] = '滿 10 年以上者，依規定給予 14 天，每一年加1日，最多 30 日' . "({$leave_day})";
                    $temp_arr['leave_day'] = $leave_day;
                    break;
            }
            return $temp_arr;
        };

        // 2017 特休制度
        $v2017LeaveDays = function (int $year, int $dd = null) {
            $str_text = function ($num, $text1 = null, $text2 = null, $text3 = null) {
                $str_text = '';
                switch ($num) {
                    case 1:
                        $str_text = '未滿 ' . $text1 . ' 個月者，依規定給予 ' . $text2 . ' 天';
                        break;
                    case 2:
                        $str_text = '滿 ' . $text1 . ' 個月未滿 ' . $text2 . ' 年者，依規定給予 ' . $text3 . ' 天';
                        break;
                    case 3:
                        $str_text = '滿 ' . $text1 . ' 年者未滿 ' . $text2 . ' 年，依規定給予 ' . $text3 . ' 天';
                        break;
                    case 4:
                        $str_text = '滿 ' . $text1 . ' 年以上者，依規定給予 ' . $text2 . ' 天，每一年加1日，最多 ' . $text3 . ' 日';
                        break;
                }
                return $str_text;
            };
            $temp_arr = [
                'str' => '', 'leave_day' => 0
            ];
            switch ($year) {
                case 0:
                    if ($dd >= 183) {
                        // $temp_arr['str'] = '滿 6 個月未滿 1 年者，依規定給予 3 天';
                        $temp_arr['str'] = $str_text(2, '6', '1', '3');
                        $temp_arr['leave_day'] = 3;
                    } else {

                        $temp_arr['str'] = $str_text(1, '6', '0');
                        $temp_arr['leave_day'] = 0;
                    }
                    break;
                case ($year == 1):
                    // $temp_arr['str'] = '滿 1 年者未滿 2 年，依規定給予 7 天';
                    $temp_arr['str'] = $str_text(3, '1', '2', '7');
                    $temp_arr['leave_day'] = 7;
                    break;
                case ($year == 2):
                    // $temp_arr['str'] = '滿 2 年者未滿 3 年，依規定給予 10 天';
                    $temp_arr['str'] = $str_text(3, '2', '3', '10');
                    $temp_arr['leave_day'] = 10;
                    break;
                case ($year >= 3 && $year < 5):
                    // $temp_arr['str'] = '滿 3 年者未滿 5 年，依規定給予 14 天';
                    $temp_arr['str'] = $str_text(3, '3', '5', '14');
                    $temp_arr['leave_day'] = 14;
                    break;
                case ($year >= 5 && $year < 10):
                    // $temp_arr['str'] = '滿 5 年者未滿 10 年，依規定給予 15 天';
                    $temp_arr['str'] = $str_text(3, '5', '10', '15');
                    $temp_arr['leave_day'] = 15;
                    break;
                case ($year >= 10):
                    // 15+n
                    $leave_day = 15 + ($year - 10);
                    $leave_day = ($leave_day >= 30) ? 30 : $leave_day;
                    // $temp_arr['str'] = '滿 10 年以上者，依規定給予 15 天，每一年加1日，最多 30 日';
                    $temp_arr['str'] = $str_text(4, '10', '15', '30') . "({$leave_day})";
                    $temp_arr['leave_day'] = $leave_day;
                    break;
            }
            return $temp_arr;
        };
        // 到職日期
        $year = explode("-", $take_office_date);
        $date = $year[1] . '-' . $year[2];
        // 往後推10年
        $now_year = date('Y') + 10;
        $year_list = [];
        for ($i = $year[0]; $i <= $now_year; $i++) {
            $year_list[$i] = $i . '-' . $year[1] . '-' . $year[2];
        }
        //特休試算開始
        $temp_leave_arr = [];
        foreach ($year_list as $key => $year_val) {
            switch ($key) {
                case $key < 2017:
                    $day1 = $getDays($year_val, $key . '-12-31 24:00:00');
                    $day2 = ($key != $year[0]) ? 0 : $getDays($key . '-1-1 00:00:00', $year_val);
                    // $day2 = 0;
                    // if ($key != $year[0]) {
                    //     $day2 = $getDays($key . '-1-1 00:00:00', $year_val);
                    // }
                    // 到職日到年底的比例
                    $take_office_date_proportion = round(($day1->days / 365), 2);

                    // 計算年資 = 當年年底 - 入職日
                    $job_year = date_diff(new \DateTime($key . '-12-31 24:00:00'), new \DateTime($take_office_date));

                    // 取得特休天數
                    $leave_days = $v2016LeaveDays($job_year->y);

                    // 入職到年底特休計算 = 到職比到年底*特休天數
                    $leave_day1 = round(($take_office_date_proportion * $leave_days['leave_day']), 1);

                    $leave_day1 = $decimalPlace($leave_day1);

                    $leave_day2 = $leave_days['leave_day'] - $leave_day1;

                    $next_year = $key + 1;

                    $tod = explode(' ', $year_val);
                    $tod[0] = explode('-', $tod[0]);
                    $tod_y = $tod[0][0];
                    $tod_m_d = $tod[0][1] . '/' . $tod[0]['2'];

                    $formula_str = "計算公式:
                        到職日{$tod_m_d}~12/31 天數 = {$day1->days},
                        (到職天數:年日數) {$day1->days}:365 =>{$take_office_date_proportion}：1 ,
                        {$leave_day1}(可休假天數) = {$take_office_date_proportion}(年日比) x{$leave_days['leave_day']}(法規天數)
                    ";
                    $temp_leave_arr[] = [
                        // "user_id" => auth()->user()->id,
                        "user_id" => $user_id,
                        "take_of_date" => $take_office_date,
                        "year" => $key,
                        "start" => "{$key}-$year[1]-$year[2]",
                        "end" => "{$key}-12-31 24:00:00",
                        "pai_day" => $leave_day1,
                        "formula" => nl2br($formula_str),
                        "content" => $leave_days['str'] . "/依比例給予 {$leave_day1} 天",
                    ];

                    $temp = explode(" ", $year[2]);
                    $temp_year = date('Y/m/d', strtotime(($key + 1) . "-{$tod_m_d} -1 day"));

                    $formula_str = "計算公式:
                        到職日{$tod_m_d}~12/31 天數 = {$day1->days},
                        (到職天數:年日數) {$day1->days}:365 =>{$take_office_date_proportion}：1 ,
                        {$leave_day2}(可休假天數) = {$leave_days['leave_day']}-({$take_office_date_proportion}(年日比) x{$leave_days['leave_day']}(法規天數))
                    ";
                    $temp_leave_arr[] = [
                        // "user_id" => auth()->user()->id,
                        "user_id" => $user_id,
                        "take_of_date" => $take_office_date,
                        "year" => $key + 1,
                        "start" => "{$next_year}-1-1 00:00:00",
                        "end" => "{$temp_year} 24:00:00",
                        "pai_day" => $leave_day2,
                        "formula" => nl2br($formula_str),
                        "content" => $leave_days['str'] . "/依比例給予 {$leave_day2} 天",
                    ];
                    break;

                case ($key >= 2017):
                    // 判斷是否為任職第一年
                    if ($take_office_date == $year_val) {

                        $office_date = new \DateTime($take_office_date);
                        // 取得到職6個月後的日期
                        $take_office_add_six_month = date('Y-m-d H:i:s', strtotime($take_office_date . ' +6 month'));
                        $take_office_add_six_month = new \DateTime($take_office_add_six_month);

                        // 取得天數
                        $day1 = $getDays($take_office_date, $take_office_add_six_month->format('Y-m-d H:i:s'));
                        $day1 = date_diff($office_date, $take_office_add_six_month);
                        // 取得 特休總天數 + 文字敘述
                        $leave_days = $v2017LeaveDays($day1->y, $day1->days);
                        $leave_day1 = $leave_days['leave_day'];
                        $formula_str = '';
                        $formula_str = "計算公式: 未滿半年無特休。";

                        // 第一年，未滿6個月
                        $temp_leave_arr[] = [
                            // "user_id" => auth()->user()->id,
                            "user_id" => $user_id,
                            "take_of_date" => $take_office_date,
                            "year" => $key,
                            "start" => $take_office_date,
                            "end" => $take_office_add_six_month->format('Y-m-d') . " 24:00:00",
                            "pai_day" => 0,
                            "formula" => ($formula_str),
                            "content" => '未滿 6 個月者，依規定給予 0 天' . "/依比例給予 0 天",
                        ];

                        // 天數比例計算(到職日/半年183) 滿六個月，未滿一年。
                        $day2_pluse = $getDays($take_office_add_six_month->format('Y-m-d H:i:s'), $key . '-12-31 24:00:00');
                        $day2 = $getDays($office_date->format('Y-m-d H:i:s'), $key . '-12-31 24:00:00');
                        // 到職天數比 = ((到職日~12月31 天數)/183) // 四捨五入，取小數2位
                        $take_office_date_proportion = round(($day2_pluse->days / 183), 2);
                        // 使用2017後的特休制度
                        $leave_days = $v2017LeaveDays($day2->y, $day2->days);
                        // 特休1 = 總天數 * ((到職日~12/31 天數)/365);
                        $leave_day2 = $take_office_date_proportion * $leave_days['leave_day'];
                        $leave_day2 = $decimalPlace2($leave_day2);

                        $formula_str = "計算公式:
                            到職日 {$take_office_add_six_month->format('m/d')}~12/31 天數 = {$day1->days},
                            (到職天數:年日數) {$day2_pluse->days}:183 =>{$take_office_date_proportion}：1 ,
                            {$leave_day2}(可休假天數) = {$take_office_date_proportion}(年日比) x{$leave_days['leave_day']}(法規天數)
                        ";
                        $temp_leave_arr[] = [
                            // "user_id" => auth()->user()->id,
                            "user_id" => $user_id,
                            "take_of_date" => $take_office_date,
                            "year" => $key,
                            "start" => $take_office_add_six_month->format('Y-m-d H:i:s'),
                            "end" => $key . '-12-31 24:00:00',
                            "pai_day" => $leave_day2,
                            "formula" => nl2br($formula_str),
                            "content" => $leave_days['str'] . "/依比例給予 {$leave_day2} 天",
                        ];
                        // 特休剩餘天數 = 總天數 - 特休1
                        $leave_day3 = $leave_days['leave_day'] - $leave_day2;

                        $next_year = $key + 1;
                        $temp = explode(" ", $year[2]);

                        $formula_str = "計算公式:
                            到職日 01/01 ~ {$office_date->format('m/d')} 天數 = {$day2_pluse->days},
                            (到職天數:年日數) {$day2_pluse->days}:183 =>{$take_office_date_proportion}：1 ,
                            {$leave_day3}(可休假天數) = {$leave_days['leave_day']} - ({$take_office_date_proportion}(年日比) x{$leave_days['leave_day']}(法規天數))
                        ";
                        $temp_leave_arr[] = [
                            // "user_id" => auth()->user()->id,
                            "user_id" => $user_id,
                            "take_of_date" => $take_office_date,
                            "year" => $key + 1,
                            "start" => "{$next_year}-01-01 00:00:00",
                            "end" => "{$next_year}-{$year[1]}-{$temp[0]} 24:00:00",
                            "pai_day" => $leave_day3,
                            "formula" => nl2br($formula_str),
                            "content" => $leave_days['str'] . "/依比例給予 {$leave_day3} 天",
                        ];
                    } else {
                        // $take_office_date
                        $getYearMD = function ($date) {
                            list($year_m_d,) = explode(" ", $date);
                            return $year_m_d;
                        };
                        // 取得日期的年
                        $getYear = function ($date) {
                            $date = explode(" ", $date);
                            list($year, $mm, $dd) = explode("-", $date[0]);
                            return $year;
                        };
                        // 取得日期的月/日
                        $getMD = function ($date) {
                            $date = explode(" ", $date);
                            list($year, $mm, $dd) = explode("-", $date[0]);
                            return $mm . '-' . $dd;
                        };
                        $getFormula2str = function ($type, $take_office_m_d, $day, $take_office_date_proportion, $leave_day, $leave_days) {
                            // $take_office_m_d 到職 月/日
                            // $day 到職月/日 至 當年12/31 天數
                            // $take_office_date_proportion 到職日 至 當年12/31 比值
                            // $leave_day 可休天數
                            // $leave_days 法規給假天數
                            $text = "計算公式:
                            到職日{$take_office_m_d}~12/31 天數 = {$day},
                            (到職天數:年日數) {$day}:365 =>" . (float)$take_office_date_proportion . "：1";
                            switch ($type) {
                                case "1":
                                    $text .= "
                                    {$leave_day}(可休假天數) = " . (float)$take_office_date_proportion . "(年日比) x{$leave_days['days']}(法規天數)
                                    ";
                                    break;
                                case "2":
                                    // $temp = 1-$take_office_date_proportion;
                                    $text .= "
                                    {$leave_day}(可休假天數) = {$leave_days['days']} - (" . (float)$take_office_date_proportion . "(年日比) x{$leave_days['days']}(法規天數))
                                    ";
                                    break;
                            }
                            return nl2br($text);
                        };

                        $base_data = [
                            // 到職日期
                            "take_office_date" => [
                                "datetime" => $take_office_date,
                                "Y-m-d" => $getYearMD($take_office_date),
                                'DateTime' => new \DateTime($take_office_date),
                                'year' => $getYear($take_office_date),
                                'm-d' => $getMD($take_office_date)
                            ],
                            // 當年度日期
                            "now_year_date" => [
                                'datetime' => $year_val,
                                'Y-m-d' => $getYearMD($year_val),
                                'DateTime' => new \DateTime($getYearMD($year_val) . ' 24:00:00'),
                                'year' => $getYear($year_val),
                                'm-d' => $getMD($year_val)
                            ],

                        ];

                        // 年日比
                        $base_data['first_date']['year'] = $key;
                        $base_data['first_date']['start_date'] = $base_data['now_year_date']['datetime'];
                        $base_data['first_date']['end_date'] = $base_data['now_year_date']['year'] . '-12-31' . ' 24:00:00';
                        $temp_diff = $this->datetimeDiff($base_data['first_date']['start_date'], $base_data['first_date']['end_date']);

                        $base_data['first_date']['days'] = $temp_diff->days;
                        $base_data['first_date']['proportion'] = round(($base_data['first_date']['days'] / 365), 2);

                        $base_data['job_tenure'] = $this->datetimeDiff(
                            $base_data['take_office_date']['DateTime']->format('Y-m-d H:i:s'),
                            $base_data['now_year_date']['DateTime']->format('Y-m-d H:i:s')
                        );

                        $get_leave_day_and_description = $v2017LeaveDays($base_data['job_tenure']->y, $base_data['job_tenure']->days);
                        $base_data['first_date']['leave_day_total']['days'] = $get_leave_day_and_description['leave_day'];
                        $base_data['first_date']['leave_day_total']['description'] = $get_leave_day_and_description['str'];
                        $base_data['first_date']['day'] = $decimalPlace2(
                            round(
                                ($base_data['first_date']['proportion'] * $base_data['first_date']['leave_day_total']['days']),
                                1   // 取小數一位
                            )
                        );
                        $base_data['second_date']['year'] = $key;
                        $base_data['second_date']['start_date'] = ($key + 1) . '-01-01 00:00:00';

                        $end_date = new \DateTime(date('Y-m-d H:i:s', strtotime(($key + 1) . '-' . $base_data['take_office_date']['m-d'] . ' -1 day')));
                        $end_date = explode(' ', $end_date->format('Y-m-d H:i:s'));
                        $base_data['second_date']['end_date'] = $end_date['0'] . ' 24:00:00';
                        $temp_diff = $this->datetimeDiff($base_data['second_date']['start_date'], $base_data['second_date']['end_date']);

                        $base_data['second_date']['days'] = $temp_diff->days;
                        $base_data['second_date']['proportion'] = 1 - $base_data['first_date']['proportion'];
                        $base_data['second_date']['leave_day_total']['days'] = $get_leave_day_and_description['leave_day'];
                        $base_data['second_date']['leave_day_total']['description'] = $get_leave_day_and_description['str'];
                        $base_data['second_date']['day'] = $base_data['first_date']['leave_day_total']['days'] - $base_data['first_date']['day'];

                        $temp_leave_arr[] = [
                            // "user_id" => auth()->user()->id,
                            "user_id" => $user_id,
                            "take_of_date" => $base_data['take_office_date']['Y-m-d'],
                            "year" => $base_data['first_date']['year'],
                            "start" =>  $base_data['first_date']['start_date'],
                            "end" => $base_data['first_date']['end_date'],
                            "pai_day" => $base_data['first_date']['day'],
                            "formula" => $getFormula2str(
                                '1',
                                $base_data['take_office_date']['m-d'],
                                $base_data['first_date']['days'],
                                $base_data['first_date']['proportion'],
                                $base_data['first_date']['day'],
                                $base_data['first_date']['leave_day_total']
                            ),
                            "content" => $base_data['first_date']['leave_day_total']['description'] . "/依比例給予 {$base_data['first_date']['leave_day_total']['days']} 天",
                        ];

                        $temp_leave_arr[] = [
                            // "user_id" => auth()->user()->id,
                            "user_id" => $user_id,
                            "take_of_date" => $base_data['take_office_date']['Y-m-d'],
                            "year" => $base_data['second_date']['year'],
                            "start" =>  $base_data['second_date']['start_date'],
                            "end" => $base_data['second_date']['end_date'],
                            "pai_day" => $base_data['second_date']['day'],
                            "formula" => $getFormula2str(
                                '2',
                                $base_data['take_office_date']['m-d'],
                                $base_data['first_date']['days'],
                                $base_data['first_date']['proportion'],
                                $base_data['second_date']['day'],
                                $base_data['second_date']['leave_day_total']
                            ),
                            "content" => $base_data['second_date']['leave_day_total']['description'] . "/依比例給予 {$base_data['second_date']['day']} 天",
                        ];
                    }
                    break;
            }
        }
        return $temp_leave_arr;
    }

    /** datetimeDiff
     *  兩個時間相減，並轉換成物件
     *  @param string $datetime1 
     *  @param string $datetime2
     *  @return object
     */
    public function datetimeDiff(string $datetime1, string $datetime2): object
    {
        $temp_diff = date_diff(
            new \DateTime(date('Y-m-d H:i:s', strtotime($datetime1))),
            new \DateTime(date('Y-m-d H:i:s', strtotime($datetime2)))
        );
        return $temp_diff;
    }
    /** yearProrate
     *  入值日年比例計算
     *  @param string $start_date 開始計算時間
     *  @param integer $days 比例數365或168
     *  @return array 
     * 
     */
    public function yearProrate(string $start_date, int $days = 365)
    {
        $time_start = '00:00:00';
        $time_end  = '24:00:00';
        $temp_arr = [];
        $temp_take_office_date = new \DateTime($start_date);
        $year = $temp_take_office_date->format('Y');
        $md = $temp_take_office_date->format('m-d');

        // 時段身為兩段:
        // date1 時段1是從輸入日期，一直計算至當年的12/31
        // date2 時段2是從 01/01，一直計算至輸入的日期

        // -- date1
        $date1 = [];
        $date1['year'] = $year;
        $date1['date']['start'] = $temp_take_office_date->format('Y-m-d ') . $time_start;
        $date1['date']['end'] = $year . '-12-31 ' . $time_end;
        $temp_date_obj = $this->datetimeDiff($date1['date']['start'], $date1['date']['end']);

        // 比例計算
        $date1['prorate'] = round(($temp_date_obj->days / $days), 2);
        // 取得起始日至年底的天數總和
        $date1['days'] = $temp_date_obj->days;

        // -- date2
        $date2['year'] = $year;
        $date2['date']['start'] = ($year + 1) . '-01-01 ' . $time_start;
        $temp_date = date('Y-m-d', strtotime(($year + 1) . '-' . $md . ' -1 days'));
        $date2['date']['end'] = $temp_date . ' ' . $time_end;
        // 比例計算使用 1 - date1['prorate'] 使用減的方式，以減少計算上的誤差
        $date2['prorate'] = 1 - $date1['prorate'];

        $temp_date_obj = $this->datetimeDiff($date2['date']['start'], $date2['date']['end']);
        $date2['days'] = $temp_date_obj->days;
        return [$date1, $date2];
    }
    /** checkUserFlag
     *  檢查使用者flag是否為起用狀態，如為關閉狀態時，則回傳錯誤response格式 code:103
     */
    public function checkUserFlag()
    {
        if (auth()->user()->flag == 0) return Service::response('103', '');
    }
}
