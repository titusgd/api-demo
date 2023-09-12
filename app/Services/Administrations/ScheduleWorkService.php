<?php

namespace App\Services\Administrations;

use App\Services\Service;
use App\Models\Administration\ScheduleStartDate;
use App\Models\Administration\ScheduleWork;
use App\Traits\RulesTrait;
use App\Traits\ResponseTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class ScheduleWorkService extends Service
{

    use RulesTrait;
    use ResponseTrait;
    private $schedule_start_date_id;
    private $request;
    private $store_id;
    private $default_work_days = 28;    // 預設排班天數為28天
    private $default_one_day_hours = 8; // 預設一天工作8小時

    public function __construct($request)
    {
        $this->request = collect($request->toArray());
    }

    public function setScheduleStartDateId(int $schedule_start_date_id): self
    {
        $this->schedule_start_date_id = $schedule_start_date_id;
        return $this;
    }

    public function getScheduleStartDateId(): int
    {
        return $this->schedule_start_date_id;
    }

    public function setStoreId(int $store_id): self
    {
        $this->store_id = $store_id;
        return $this;
    }

    public function getStoreId(): int
    {
        return $this->store_id;
    }

    public function runValidate(string $method): self
    {
        switch ($method) {
            case 'store':
                $day_num = cal_days_in_month(CAL_GREGORIAN, $this->request['month'], $this->request['year']);
                $rules = [
                    'store_id' => 'required|exists:stores,id',
                    'staff_id' => 'required|exists:users,id',
                    'year' => 'required|integer|between:2020,2100',
                    'month' => 'required|integer|between:1,12',
                    'day' => 'required|integer|between:1,' . $day_num,
                    'type' => 'nullable|integer',
                    'note' => 'nullable|string|max:255',
                ];
                $data = $this->request->toArray();
                $data['monty'] = str_pad($data['month'], 2, '0', STR_PAD_LEFT);
                break;
            case 'list':
                $rules = [
                    'store_id' => 'required|exists:stores,id',
                    'year' => 'required|integer|between:2020,2100',
                    'month' => 'required|integer|between:1,12'
                ];
                $data = $this->request->toArray();
                break;
        }

        $this->response = self::validate($data, $rules);
        if (!empty($this->response)) return $this;
        // 依前端要求，無須檢查是否排班

        // if ($method == 'store') {
        //     // 檢查是否已排班
        //     $data = ScheduleWork::where('staff_id', '=', $this->request['staff_id'])
        //         ->where(
        //             'schedule_date',
        //             '=',
        //             $this->request['year']
        //                 . '-'
        //                 . $this->zeroComplement($this->request['month'])
        //                 . '-'
        //                 . $this->zeroComplement($this->request['day'])
        //         )
        //         ->first();
        //     // $data = ScheduleWork::where('staff_id', '=', $this->request['staff_id'])
        //     //     ->where('schedule_date', '=', $this->request['year'] . '-' . str_pad($this->request['month'], 2, '0', STR_PAD_LEFT) . '-' . str_pad($this->request['day'], 2, '0', STR_PAD_LEFT))->first();
        //     if (!empty($data)) $this->response = Service::response('03', 'day');
        // }
        return $this;
    }

    public function store()
    {
        if (!empty($this->response)) return $this;
        $schedule_date = implode('-', [
            $this->request['year'],
            // 補數
            $this->zeroComplement($this->request['month']),
            $this->zeroComplement($this->request['day'])
        ]);
        // 移除已排定日期
        $sd = ScheduleWork::where('year', '=', $this->request['year'])
            ->where('month', '=', $this->zeroComplement($this->request['month']))
            ->where('day', '=', $this->zeroComplement($this->request['day']))
            ->where('staff_id', '=', $this->request['staff_id'])
            ->delete();
        
        $this->request
            ->put('updater_id', auth()->user()->id)
            ->put('schedule_date', $schedule_date);
        (empty($this->request['type'])) && $this->request->put('type', 0);
        (empty($this->request['note'])) && $this->request->put('note', '');

        $schedule_start_date = ScheduleWork::create($this->request->toArray());
        (!empty($schedule_start_date['id'])) && $this->response = Service::response('00', 'ok');
        return $this;
    }

    public function list()
    {
        if (!empty($this->response)) return $this;
        $data = [];
        // dd($this->request);
        // 檢查並查詢，是否有此月份資料，取最新一筆
        // $date = ScheduleStartDate::select('start_date as date')
        //     ->where('store_id', '=', $this->request['store_id'])
        //     ->where('start_date', 'like', $this->request['year'] . '-' . $this->zeroComplement($this->request['month']) . '%')
        //     ->orderBy('created_at', 'DESC')
        //     ->first();
        // $temp_date = $date;

        # 查無日期，則以輸入的為主
        if (empty($date)) {
            $date['date'] = $this->request['year'] . '-' . $this->zeroComplement($this->request['month']) . '-01';
        }
        $this->default_work_days = Carbon::createFromDate($date['date'])->daysInMonth;
        // dd($default_work_days);
        // $default_work_days
        // 取得查詢日期區間
        $date_start = Carbon::createFromDate($date['date'])->format('Y-m-d');
        // $date_arr = explode('-', $date['date']);
        // $date_to = Carbon::createFromDate($date_arr[0], $date_arr[1], $date_arr[2])->addDays(28)->format('Y-m-d');
        // $date_to = Carbon::createFromDate($date['date'])->addDays(28)->format('Y-m-d');
        $data['start_date'] = $date_start;
        // 取得排班資料
        $word_data = ScheduleWork::select(
            "id",
            "store_id",
            "staff_id",
            "year",
            "month",
            "day",
            "type",
            "note",
            "schedule_date"
        )
            ->where('store_id', '=', $this->request['store_id'])
            ->where('schedule_date', '>=', $date_start)
            ->where(
                'schedule_date',
                '<=',
                Carbon::createFromDate($date['date'])
                    ->addDays($this->default_work_days) // 使用預設排班天數
                    ->format('Y-m-d')
            )
            ->orderBy('schedule_date', 'ASC')           // 天數排序由小->大
            ->get();

        // * 取得班表內的員工資料
        // - 從排班表取得員工id
        $staff_list = $word_data->pluck('staff_id')->unique()->toArray();
        // TODO: JSON查詢範例
        $staff_list = User::select('id', 'name', 'code', 'store_id', 'flag', 'support_store_id')
            ->whereIn('id', $staff_list)    // 排班表名單
            ->orWhereRaw("JSON_CONTAINS(support_store_id, '{$this->request['store_id']}' )") // json查詢，支援分店
            ->orWhere('store_id', '=', $this->request['store_id'])  // 店鋪
            ->where('flag', '=', '1')     // 帳號啟用狀態
            ->get()
            ->toArray();
        // 變更預設時間
        // $this->default_work_days = Carbon::createFromDate($year, $month, 1)>daysInMonth;
        // * 排班陣列預設值設定 需有28天的資料，沒有就帶空的
        $set_day = function ($word_data, $staff_id) use ($date) {
            $word_data_arr = [];
            $hours = 0;
            // 將排班日轉為陣列資料，並將key設定為日期
            foreach ($word_data as $work_data_key => $work_data_item) {
                if ($staff_id == $work_data_item['staff_id']) {
                    $hours++;
                    $word_data_arr[$work_data_item['schedule_date']] = $work_data_item->toArray();
                }
            }

            // 設定排班日，並將尚未排班則寫入預設值
            $day_default = [];
            for ($i = 0; $i < $this->default_work_days; $i++) {
                // day 計算
                $e_date = Carbon::createFromDate($date['date'])->addDays($i)->format('Y-m-d');
                $e_day = explode('-', $e_date);

                if (!empty($word_data_arr[$e_date])) {
                    $day_default['schedule'][] = [
                        'day' => $word_data_arr[$e_date]['day'],
                        'type' => $word_data_arr[$e_date]['type'],
                        'note' => $word_data_arr[$e_date]['note'],
                    ];
                } else {
                    $day_default['schedule'][] = [
                        'day' => (int)$e_day['2'],
                        'type' => 1,
                        'note' => ''
                    ];
                }
            }
            return ['schedule' => $day_default['schedule'], 'hours' => $hours];
        };
        foreach ($staff_list as $key => $value) {
            $temp = collect([]);
            $schedule = $set_day($word_data, $value['id']);
            $temp->put('id', $value['id'])
                ->put('code', $value['code'])
                ->put('name', $value['name'])
                ->put('hours', ($schedule['hours'] * $this->default_one_day_hours))
                ->put('schedule', $schedule['schedule']);

            // $temp['id'] = $value['id'];
            // $temp['code']   = $value['code'];
            // $temp['name'] = $value['name'];
            // 排班表與工時
            // $temp['hours'] = 0;
            // $temp['schedule'] = [];
            // $schedule = $set_day($word_data, $value['id']);
            // $temp['schedule'] = $schedule['schedule'];
            // $temp['hours'] = $schedule['hours'] * $this->default_one_day_hours;
            $staff_list[$key] = $temp->toArray();
        }
        // 將排班總表資料寫入陣列
        $data['list'] = $staff_list;

        // (empty($temp_date)) && $data['start_date'] = '';

        $date = ScheduleStartDate::select('start_date as date')
            ->where('store_id', '=', $this->request['store_id'])
            ->orderBy('created_at', 'DESC')
            ->get();
        // 指定起始日
        $data['start_date'] = (empty($date[0])) ? '' : $date[0]['date'];
        // if (empty($date)) {
        //     $data['start_date'] = '';
        // } else {
        //     dd($date);
        //     $data['start_date'] = $date[0]['date'];
        // }
        $this->response  = Service::response('00', 'ok', $data);
        return $this;
    }

    private function zeroComplement($num, $len = 2)
    {
        return str_pad($num, $len, '0', STR_PAD_LEFT);
    }
}
