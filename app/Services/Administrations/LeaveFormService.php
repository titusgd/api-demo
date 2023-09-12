<?php

namespace App\Services\Administrations;

use App\Models\Administration\LeaveDayoff;
use App\Models\Administration\LeaveType;
use App\Models\Administration\LeaveAnnual;
use App\Models\Account\User;

use App\Services\Administrations\LeaveDayoffService;
use App\Services\Service;

use App\Traits\ReviewTrait;
use App\Traits\NotifyTrait;
use Illuminate\Support\Facades\DB;

class LeaveFormService extends Service
{
    use ReviewTrait, NotifyTrait;
    private $review_rank = [
        '23' => 1,
        '22' => 2,
        '17' => 3
    ];
    public function validList($request)
    {
        $rules = [
            // 'page' => 'required|integer',
            'audit' => 'string',
            'access' => 'boolean',
        ];

        (!empty($request->date['page'])) && $rules['page'] = 'integer';
        (!empty($request->date['start'])) && $rules['date.start'] = 'required|date_format:Y/m/d';
        (!empty($request->date['end'])) && $rules['date.end'] = 'required|date_format:Y/m/d';
        (!empty($request->count)) && $rules['count'] = 'integer';
        (!empty($request->store)) && $rules['store'] = 'integer';
        (!empty($request->staff)) && $rules['staff'] = 'integer';
        (!empty($request->search)) && $rules['search'] = 'string';
        (!empty($request->audit)) && $rules[''] = 'string';

        $valid = Service::validatorAndResponse($request->all(), $rules, [
            "date.start.required" => '01 date',
            "date.start.date_format" => '01 date',
            "date.end.required" => '01 date',
            "date.end.date_format" => '01 date',
            'count.required' => '01 count',
            'count.integer' => '01 count',
            'store.integer' => '01 store',
            'staff.integer' => '01 staff',
            'search.string' => '01 search',
            'audit.string' => '01 audit',
            'access.boolean' => '01 access'
        ]);
        if ($valid) return $valid;
    }

    public function validAudit($request)
    {
        $rules = [
            '*.id' => 'required|integer',
            '*.audit' => 'required|string',
        ];

        $valid = Service::validatorAndResponse($request->all(), $rules, [
            '*.id.required' => '01 id',
            '*.id.integer' => '01 id',
            '*.audit.required' => '01 audit',
            '*.audit.integer' => '01 audit',
        ]);

        if ($valid) return $valid;

        // 檢查是否已經審核過了
        $temp_arr = $request->all();
        $temp_id = array_column($temp_arr, 'id');
        $fn_review = function ($fk_id, $select = '*') {
            $temp_query = self::getReviewModel();
            $temp_query = $temp_query->select($select);
            $temp_query = $temp_query->where('type', '=', 'leaveDayoff');
            $temp_query = $temp_query->whereIn('fk_id', $fk_id);
            return $temp_query;
        };
        $review_model = self::getReviewModel();
        // 檢查表單是否已經審核過了
        $temp_review_data = $fn_review($temp_id, ['id'])
            ->where(
                [
                    ['user_id', '=', auth()->user()->id],
                    ['status', '!=', '1']
                ]
            )
            ->get()->toArray();

        if ($temp_review_data) return Service::response('202', 'id');

        // 檢查陣列內，是否有包含審核不通過的
        if (auth()->user()->id == '23' || auth()->user()->id == '22' || auth()->user()->id == '17') {
            $temp_arr = $request->all();
            $temp_id = array_column($temp_arr, 'id');

            // 檢查上一層級是否審核過
            $review_data = $fn_review($temp_id, ['id', 'fk_id'])
                ->where([
                    ['rank', '=', (($this->review_rank[auth()->user()->id]) + 1)],
                    ['status', '=', '1']
                ])
                ->get()->toArray();
            if ($review_data) return Service::response('208', 'id');

            // 檢查此表單是否有審核不通過的
            $review_data = $fn_review($temp_id, ['id', 'fk_id'])
                ->where('status', '=', '2')
                ->get()->toArray();
            if ($review_data) return Service::response('207', 'id');
        }
    }

    public function audit($request)
    {
        $temp_arr = $request->all();
        $review_model = self::getReviewModel();

        $now_datetime = date('Y-m-d H:i:s');
        foreach ($temp_arr as $key => $value) {
            $update = [];
            $update['fk_id'] = $value['id'];
            $update['status'] = self::statusToNumber($value['audit']);
            (!empty($value['reason'])) && $update['note'] = $value['reason'];
            $update['date'] = $now_datetime;

            // 關閉通知
            self::closeNoticeAndNoticeUser('leaveDayoff', $value['id'], auth()->user()->id);

            // 更新審核資料表
            $review_update = $review_model
                ->where([
                    ['type', '=', 'leaveDayoff'],
                    ['fk_id', '=', $value['id']],
                    ['user_id', '=', auth()->user()->id]
                ])
                ->update($update);

            // -----------------------------------------------------------------
            // 新增審核通知
            // 判斷審核是否通過
            // 未通過
            $leave_data = LeaveDayoff::select(
                'number',
                DB::raw('(select name from users where users.id = user_id) as user_name'),
                DB::raw('(select name from leave_types where leave_types.id = leave_type_id) as leave_name'),
                'start as start_datetime',
                'end as end_datetime'
            )->where('id', '=', $update['fk_id'])->first()->toArray();

            $start = str_replace('-', '/', $leave_data['start_datetime']);
            $end = str_replace('-', '/', $leave_data['end_datetime']);

            $title = '假單 審核通知';
            $content = "單號 : {$leave_data['number']} 
                員工 : {$leave_data['user_name']} 
                假別 : {$leave_data['leave_name']} 
                日期 : " . $start . " ~ " . $end . " ";

            // 更新通知內容
            $fn_notice_update = function (int $notice_id, int $fk_id, string $uri) {
                $update_notice_link = $this->updateNoticeLink($notice_id, env('APP_URL') . $uri);
                $update_notice_type = self::updateNoticeTypeAndFkId($notice_id, 'leaveDayoff', $fk_id);
                $add_notice_user = self::updateNoticeUserTypeAndFkId($notice_id, 'leaveDayoff', $fk_id);
            };

            if ($update['status'] == 2) {
                // 未通過，發送通知給申請人
                $leave_data = LeaveDayoff::select('number', 'user_id')->where('id', '=', $update['fk_id'])->first();
                // print_R($user_id);
                $notice = self::addNotice(
                    $title,
                    $content . '
                    審核結果：未通過',
                    [$leave_data->user_id],
                    false
                );
                $fn_notice_update($notice->id, $update['fk_id'], '/administration/leave/leaveForm');
            }
            // 通過
            if ($update['status'] == 3) {
                $review_data = $review_model->select('rank')
                    ->where([
                        ['type', '=', 'leaveDayoff'],
                        ['fk_id', '=', $update['fk_id']],
                        ['user_id', '=', auth()->user()->id]
                    ])
                    ->get()->toArray();
                $leave_data = LeaveDayoff::select('number', 'user_id')->where('id', '=', $update['fk_id'])->first();
                if ($review_data[0]['rank'] == 1) {
                    //等於1表示審核結束
                    $notice = self::addNotice(
                        $title,
                        $content . '
                        審核結果：通過',
                        [$leave_data->user_id],
                        false
                    );
                    $fn_notice_update($notice->id, $update['fk_id'], '/administration/leave/dayOff');
                } else {
                    // 不等於1，進入下一層審核
                    $next_review = $review_model->select('user_id')
                        ->where([
                            ['type', '=', 'leaveDayoff'],
                            ['fk_id', '=', $update['fk_id']],
                            ['rank', '=', ($review_data[0]['rank'] - 1)]
                        ])
                        ->first();
                    $notice = self::addNotice(
                        $title,
                        $content,
                        [$next_review->user_id],
                        false
                    );
                    $fn_notice_update($notice->id, $update['fk_id'], '/administration/leave/leaveForm');
                }
            }
        }
        return Service::response('00', 'ok');
    }


    public function getList($request)
    {

        $data = $this->selectLeaveDayoff($request);
        $output = [];
        $output['page'] = [];
        $output['data'] = [];

        // 分頁、頁數設定
        $setPage = function ($data) use (&$output) {
            $output['page']['total'] = (!empty($data['last_page'])) ? $data['last_page'] : 1; // 總頁數
            $output['page']['countTotal'] = (!empty($data['total'])) ? $data['total'] : count($data);  // 總比數
            $output['page']['page'] = (!empty($data['current_page'])) ? $data['current_page'] : 1;
        };
        $setPage($data);
        $temp_source_data = (!empty($data['data'][0])) ? $data['data'] : $data;

        // 查無資料，則回傳空陣列
        if (empty($temp_source_data[0]))  return Service::response_paginate('00', 'ok', [], $output['page']);

        foreach ($temp_source_data as $key => $val) {
            $output['data'][$key]['id'] = $val['id'];
            $output['data'][$key]['code'] = $val['number'];
            $output['data'][$key]['type'] = [
                'id' => $val['leave_type_id'], 'name' => $val['leave_type_name']
            ];
            $output['data'][$key]['staff'] = [
                'id' => $val['leave_user_id'], 'name' => $val['leave_user_name']
            ];
            $output['data'][$key]['date'] = [
                'apply' => self::dateFormat($val['date_apply']),
                'start' => self::dateFormat($val['date_start']),
                'end' => self::dateFormat($val['date_end']),
            ];
            $days = intval($val['total_hour'] / 8);
            $hours = $val['total_hour'] % 8;

            $output['data'][$key]['count'] = [
                "days" => $days,
                'hours' => $hours
            ];
            $output['data'][$key]['note'] = $val['note'];
            $output['data'][$key]['status'] = [];

            foreach ($val['reviews'] as $key2 => $val2) {
                $output['data'][$key]['status'][$key2]['id'] = $val2['user_id'];
                $output['data'][$key]['status'][$key2]['name'] = $val2['user_name'];
                $output['data'][$key]['status'][$key2]['rank'] = $val2['rank'];
                $output['data'][$key]['status'][$key2]['audit'] = $this->numberToStatus($val2['status']);
                $output['data'][$key]['status'][$key2]['date'] = (!empty($val2['date'])) ? self::dateFormat($val2['date']) : [];
                $output['data'][$key]['status'][$key2]['reason'] = $val2['note'];
            }

            $temp_data = $this->selectLeaveType($val['date_apply'], $val['leave_user_id']);

            $output['data'][$key]['leave'] = [];
            foreach ($temp_data as $key2 => $val2) {
                $output['data'][$key]['leave'][$key2] = [
                    'id' => $val2['id'],
                    'total' => $val2['days'],
                    'used' => $val2['used']
                ];
            }
        }
        return Service::response_paginate('00', 'ok', $output['data'], $output['page']);
    }

    public function selectLeaveType(string $date, int $user_id)
    {
        // 特休天數查詢
        $leave_annual_fn = function ($leave_year, $user_id) {
            return LeaveAnnual::select('id', 'take_office_day', 'start', 'end', 'pai_day')
                ->where([
                    ['start', 'like', $leave_year . '%'],
                    ['user_id', '=', $user_id]
                ])
                ->orderBy('id', 'asc')
                ->get()->toArray();
        };

        // 查詢請假類別以及請假時數
        $date = str_replace('/', '-', $date);
        $date = explode(' ', $date);
        $data = LeaveType::select('id', 'days')->get()->toArray();
        $year = explode('-', $date[0]);

        // 查詢特休天數
        $leave_annuals = $leave_annual_fn($year[0], $user_id);

        // 特休日期
        if (!$leave_annuals) {
            // 特休重新產生
            $leave_dayoff_servcie = new LeaveDayoffService();
            $leave_dayoff_servcie->getLeaveAnnual($user_id, $year[0]);
            // 查詢特休天數
            $leave_annuals = $leave_annual_fn($year[0], $user_id);
        }
        $take_office_day = new \DateTime($leave_annuals[0]['take_office_day']);
        $work_days = date_diff($take_office_day, new \DateTime($year[0] . '-' . $year[1] . '-' . $year[2]));
        if ($work_days->days < 183) {
            $data[0]['days'] = 0;
        } else {
            foreach ($leave_annuals as $key => $val) {
                $data[0]['days'] += $val['pai_day'];
            }
        }

        $temp_data = LeaveDayoff::select('id')
            ->where([
                ['user_id', '=', $user_id],
                ['start', 'like', $year[0] . '%'],
                ['end', 'like', $year[0] . '%']
            ])
            ->get()->toArray();
        $fk_id = array_column($temp_data, 'id');
        $review_model = self::getReviewModel();
        $review_data = $review_model->select('fk_id', 'status')
            ->where('type', '=', 'leaveDayoff')
            ->whereIn('fk_id', $fk_id)
            ->get()->toArray();

        $fk_id = array_column($review_data, 'fk_id');
        $fk_id = array_unique($fk_id);

        $review_data2 = $review_model->select('fk_id', 'status')
            ->where([
                ['type', '=', 'leaveDayoff'],
                ['status', '=', '2']
            ])
            ->whereIn('fk_id', $fk_id)
            ->get()->toArray();
        $fk_id2 = array_column($review_data2, 'fk_id');
        $fk_id2 = array_unique($fk_id2);
        $fk_id  = array_diff($fk_id, $fk_id2);

        $temp_data = LeaveDayoff::select('id', 'leave_type_id', 'total_hour')->whereIn('id', $fk_id)->get()->toArray();

        $temp_total = [];
        foreach ($temp_data as $key => $val) {
            $temp_total[$val['leave_type_id']] = (!empty($temp_total[$val['leave_type_id']])) ?
                $temp_total[$val['leave_type_id']] + $this->hourToDay($val['total_hour']) :
                $this->hourToDay($val['total_hour']);
        }
        foreach ($data as $key => $val) {
            $data[$key]['used'] = (!empty($temp_total[$val['id']])) ? $temp_total[$val['id']] : 0;
        }

        return $data;
    }
    /** hourToDay
     *  小時轉換為天數
     *  @return float
     */
    public function hourToDay($hours)
    {
        // 8小時為一天
        return $hours * 0.125;
    }

    /** dayToHour
     *  天數轉換為小時
     *  @return integer
     */
    public function dayToHour($day)
    {
        $hour = $day * 8; // 一天八小時
        return $hour;
    }

    public function selectLeaveDayoff($request)
    {
        $user_id_list = [];
        $getUserIdList = function ($store_id) use (&$user_id_list) {
            $temp_arr = User::select('id')->where('store_id', '=', $store_id)->get()->toArray();
            $temp_arr = array_column($temp_arr, 'id');
            $user_id_list = array_merge($user_id_list, $temp_arr);
        };

        (!empty($request->store)) && $getUserIdList($request->store);

        (!empty($request->staff)) && array_push($user_id_list, $request->staff);

        if (!empty($request->search)) {
            // 單號、員工姓名、員工帳號
            // 查詢員工工號、姓名、帳號
            $temp_arr = User::select('id')
                ->where('name', 'like', "%" . $request->search . "%")
                ->orWhere('code', '=', $request->search)
                ->orWhere('account', '=', $request->search)
                ->get()->toArray();

            $temp_arr = array_column($temp_arr, 'id');
            $user_id_list = array_merge($user_id_list, $temp_arr);
            // 查詢單號
            $temp_arr = LeaveDayoff::select('user_id as id')->where('number', 'like', $request->search)->get()->toArray();
            $temp_arr = array_column($temp_arr, 'id');
            $user_id_list = array_merge($user_id_list, $temp_arr);
        }
        // 濾除重複
        $user_id_list = array_unique($user_id_list);

        $list_query = LeaveDayoff::select(
            'id',
            'number',
            'leave_type_id',
            'user_id as leave_user_id',
            'start as date_start',
            'end as date_end',
            'created_at as date_apply',
            'total_hour',
            'note'
        )
            ->selectRaw('(select name from leave_types where leave_types.id = leave_type_id)as leave_type_name')
            ->selectRaw('(select name from users where users.id = user_id) as leave_user_name');
        $list_query->with([
            'reviews' => function ($query) use ($request) {
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
                )->where('type', '=', 'leaveDayoff');

                $query->orderBy('rank', 'desc');
            }
        ]);

        (!empty($user_id_list)) && $list_query->whereIn('user_id', $user_id_list);

        // 審核狀態
        if (!empty($request->audit)) {
            // all：所有
            // unaudited：未審核
            // audited：已審核
            $review_model = self::getReviewModel();
            $review_query = $review_model->select('fk_id')
                ->where('type', '=', 'leaveDayoff');

            $audit = $request->audit;
            switch ($audit) {
                    // case 'all': // 全部:不下條件
                    //     break;
                case 'unaudited':   // 未審核
                    $review_query->where('status', '=', '1');
                    break;
                case 'audited':     // 已審核
                    $review_query->where('status', '!=', '1');
                    break;
            }

            $review_data = $review_query->get()->toArray();
            $review_id = array_column($review_data, 'fk_id');
            $review_id = array_unique($review_id);

            $list_query->whereIn('id', $review_id);
        }

        // 審核權限
        if (!empty($request->access)) {

            if (auth()->user()->id == '23' || auth()->user()->id == '22' || auth()->user()->id == '17') {
                // rank 1~3，可以審核的單
                $review_model = self::getReviewModel();
                $review_query = $review_model->select('fk_id')
                    ->where([
                        ['user_id', '=', auth()->user()->id], // 登入人員user_id
                        ['type', '=', 'leaveDayoff'],
                        ['status', '=', '1'] // 取得可以審核的資料
                    ]);

                $review_data = $review_query->get()->toArray();
                $review_data = array_column($review_data, 'fk_id');
                // 取得rank +1 已審核通過的名單
                $review_query = $review_model->select('fk_id')
                    ->where([
                        ['type', '=', 'leaveDayoff'],
                        ['rank', '=', (($this->review_rank[auth()->user()->id]) + 1)],
                        ['status', '=', '3']
                    ])
                    ->whereIn('fk_id', $review_data);
                    
                $review_data2 = $review_query->get()->toArray();
                $review_id = array_column($review_data2, 'fk_id');
                $review_id = array_unique($review_id);
                $list_query->whereIn('id', $review_id);
            } else {
                // rank:4，可以審核的單
                $review_model = self::getReviewModel();
                $review_query = $review_model->select('fk_id')
                    ->where([
                        ['user_id', '=', auth()->user()->id], // 登入人員user_id
                        ['type', '=', 'leaveDayoff'],
                        ['status', '=', '1']
                    ]);

                $review_data = $review_query->get()->toArray();
                $review_id = array_column($review_data, 'fk_id');
                $review_id = array_unique($review_id);
                $list_query->whereIn('id', $review_id);
            }
        }

        $list_data = (!empty($request->count)) ? $list_query->paginate($request->count)->toArray() : $list_query->get()->toArray();

        return $list_data;
    }
}
