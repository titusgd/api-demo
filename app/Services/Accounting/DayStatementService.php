<?php

namespace App\Services\Accounting;

use App\Services\Service;
use App\Models\Accounting\DayStatement;
use App\Models\Accounting\DayStatementData;
use App\Models\Accounting\DayStatementLog;
use App\Models\Accounting\Payment;
use App\Models\Accounting\PaymentItem;
use App\Models\Accounting\TransferVoucher;
use App\Models\Accounting\TransferVoucherItem;
use App\Models\Accounting\Receipt;
use App\Models\Accounting\ReceiptItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use App\Traits\ReviewTrait;

class DayStatementService extends Service
{
    use ReviewTrait;
    private $err_msg = [
        'year' => [
            'required' => '01 year',
            'digits' => '01 year',
            'integer' => '01 year',
            'min' => '01 year',
            'max' => '07 year',
        ],
        'month' => [
            'required' => '01 month',
            'integer' => '01 month',
            'min' => '01 month',
            'max' => '07 month',
            'numeric' => '01 month'
        ],
        'day' => [
            'required' => '01 day',
            'integer' => '01 day',
            'min' => '01 day',
            'max' => '07 day',
            'numeric' => '01 day'
        ]
    ];

    public function validDate($request)
    {

        $day = date("t", strtotime($request->year . '-' . $request->month));
        $rules = [
            'year' => 'required|digits:4|integer|min:1900|max:2500',
            'month' => 'required|numeric|min:1|max:12',
            'day' => 'required|numeric|min:1|max:' . $day
        ];

        $valid = Service::validatorAndResponse($request->all(), $rules, Arr::dot($this->err_msg));
        if ($valid) return $valid;

        $now_date = date('Y-m-d');
        $input_date = implode('-', $request->all());
        // 輸入日期不得大於當前日期
        if (strtotime($input_date) > strtotime($now_date)) {
            return response()->json(['code' => '01', 'msg' => 'date', 'data' => []]);
        }
        $date = $request->year . '-' . str_pad($request->month, 2, '0', STR_PAD_LEFT) . '-' . $request->day;
        $day_statement = DayStatement::select('id', 'statement_date', 'flag')
            ->where('statement_date', $date)
            ->where('flag', 0)
            ->get()
            ->toArray();

        if (!empty($day_statement))  return Service::response('503', 'date');
    }

    public function validIndex($request)
    {
        $rules = [
            'year' => 'required|digits:4|integer|min:1900|max:2500',
            'month' => 'required|numeric|min:1|max:12'
        ];

        $valid = Service::validatorAndResponse($request->all(), $rules, Arr::dot($this->err_msg));
        if ($valid) return $valid;
    }
    public function validDetail($request)
    {
        $rules = [
            'year' => 'required|digits:4|integer|min:1900|max:2500',
            'month' => 'required|numeric|min:1|max:12'
        ];

        $valid = Service::validatorAndResponse($request->all(), $rules, Arr::dot($this->err_msg));
        if ($valid) return $valid;
    }

    public function createData($request)
    {
        $now_datetime = date('Y-m-d H:i:s');
        $add_item_data = function ($items, $table_name) use (&$data, $now_datetime) {
            $items = collect($items);
            $items->map(function ($item) use (&$data, $table_name, $now_datetime) {
                $temp = collect();
                $temp->put('day_statement_id', $data['day_statement']['id'])
                    ->put('debit_credit', $item['debit_credit'])    // 類別
                    ->put('summary', $item['summary'])              // 摘要
                    ->put('price', $item['total_price'])            // 金額(總)
                    ->put('accounting_subject_id', $item['accounting_subject_id']) //會計科目
                    ->put('pay_type', (!empty($item['pay_type']) ? $item['pay_type'] : 0))            // 支付方式
                    ->put('type', $table_name)                            // 來源資料表
                    ->put('fk_id', $item['id'])                     // 來源id
                    ->put('code', $item['code'])                    // 來源code
                    ->put('created_at', $now_datetime);
                // 無使用，暫時移除
                // ->put('summons', '')                            // 傳票號碼
                // ->put('invoice', '');                           // 單據號碼

                if ($item['debit_credit'] == '1') {                // 總支 1.借方
                    $data['day_statement']['total_pay'] = bcadd((float)$data['day_statement']['total_pay'], (float)$item['total_price']);
                }
                if ($item['debit_credit'] == '2') {                // 總收 2.貸方
                    $data['day_statement']['total_receive'] = bcadd((float)$data['day_statement']['total_receive'], (float)$item['total_price']);
                }

                $data['day_statement_data'][] = $temp->toArray();
            });
        };

        $input_data = collect();
        $input_data->put('date', $request->year . '-' . str_pad($request->month, 2, '0', STR_PAD_LEFT) . '-' . $request->day);
        $input_data->put('year', $request->year);
        $input_data->put('month', $request->month);
        $input_data->put('day', $request->day);

        $statement_date = $this->getDayStatement($input_data['date'], $input_data['month'], $input_data['year']);

        $data = [
            'day_statement' => [
                'id' => $statement_date['id'],
                'total_receive' => (float)$statement_date['total_receive'],    // 總收 2.貸方
                'total_pay' => (float)$statement_date['total_pay'],            // 總支 1.借方
                'time' => $statement_date['time'] + 1,                  // 次數+1
                'flag' => 0,                                            // 0 不可編輯
                'summons' => $this->resetSummons($statement_date['id'], $input_data['month'], $input_data['year']),

            ],
            'day_statement_data' => collect([])
        ];
        // 子表製作
        // payments 
        $add_item_data($this->getPaymentItem($input_data['date']), 'payment_items');
        // transfer_voucher
        $add_item_data($this->getTransferVoucher($input_data['date']), 'transfer_voucher_items');
        // requisition
        $add_item_data($this->getRequisition($input_data['date']), 'receipt_items');

        // 寫入資料
        // 更新主表
        DayStatement::where('id', $data['day_statement']['id'])->update($data['day_statement']);

        // 新增日結細項()
        DayStatementData::insert($data['day_statement_data']->toArray());
        // 新增log新增log
        $this->addLog(1, $data['day_statement']['id']);
        // 建立審核清單
        // ReviewTrait::createR
        // use App\Traits\ReviewTrait;
        return Service::response('00', 'ok', []);
    }

    public function getList($year, $month)
    {
        $date = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT);
        $day = date("t", strtotime($year . '-' . $month));

        $day_statement_id = DayStatement::select('id', 'statement_date', 'time')
            ->where('statement_date', 'like', $date . '%')
            ->get();

        $output = [];

        foreach (range(1, $day) as $num) {
            $output[$num] = (object)[
                'id' => 0,
                'date' => [],
                'time' => 0
            ];
        }

        $day_statement_id->map(function ($item) use (&$output) {
            $temp_date = explode('-', $item['statement_date']);
            $temp_day = $temp_date[count($temp_date) - 1];
            $output[$temp_day] = [
                'id' => $item['id'],
                'date' => [$item['statement_date'], '00:00:00'],
                'time' => $item['time']
            ];
        });

        return Service::response('00', 'ok', $output);
    }

    // ********** get Data *****************************************************
    private function getDayStatement($date, $month, $year)
    {
        $day_statement_id = '';
        if ($this->dayStatementIsSet($date)) {
            $day_statement = DayStatement::select('id')->where('statement_date', '=', $date)->get()->toArray();
            $day_statement_id = $day_statement[0]['id'];
        } else {
            $day_statement_id = $this->addMainData($date, $month, $year);
        }

        $day_statement = DayStatement::select(
            'id',
            'total_receive',
            'total_pay',
            'time',
            'flag'
        )->where('id', $day_statement_id)->get()->toArray();
        return $day_statement[0];
    }

    private function getPaymentItem($date)
    {
        $payment = Payment::select('id')
            ->where('created_at', 'like', $date . ' %')
            ->get()
            ->pluck('id')
            ->toArray();

        $payment_item = PaymentItem::select(
            'id',
            'payment_id',
            'accounting_subject_id',
            'pay_type',
            'debit_credit',
            'qty',
            'price',
            'summary',
            DB::raw('(qty*price)as total_price'),
            DB::raw('(select code from payments where `payments`.`id` = payment_id)as code')
        )->whereIn('id', $payment)->get()->toArray();

        return $payment_item;
    }

    private function getTransferVoucher($date)
    {
        $transfer_voucher = TransferVoucher::select('id')
            ->where('created_at', 'like', $date . ' %')
            ->get()
            ->pluck('id')
            ->toArray();
        $transfer_voucher = TransferVoucherItem::select(
            'id',
            'transfer_voucher_id',
            'accounting_subject_id',
            'debit_credit',
            'summary',
            DB::raw('("1") as qty'),
            'price',
            DB::raw('( `price` ) as total_price'),
            DB::raw('(select code from transfer_vouchers where `transfer_vouchers`.`id` = transfer_voucher_id)as code')
        )
            ->whereIn('id', $transfer_voucher)
            ->get()
            ->toArray();
        return $transfer_voucher;
    }

    private function getRequisition($date)
    {

        $receipt = Receipt::select('id')
            ->where('created_at', 'like', $date . '%')
            ->get()
            ->pluck('id')
            ->toArray();
        $receipt_item = ReceiptItem::select(
            'id',
            'receipt_id',
            'accounting_subject_id',
            'pay_type',
            'debit_credit',
            'summary',
            'qty',
            'price',
            DB::raw('(qty*price)as total_price'),
            DB::raw('(select code from receipts where `receipts`.`id` = receipt_id)as code')
        )->whereIn('receipt_id', $receipt)->get()->toArray();
        return $receipt_item;
    }
    // ********** log **********************************************************
    private function addLog($action, $day_statement_id)
    {
        DayStatementLog::create([
            'user_id' => auth()->user()->id,
            'day_statement_id' => $day_statement_id,
            'action' => $action,
        ]);
    }
    // ********** other methods ************************************************
    // 建立主表資料，並回傳id
    private function addMainData($date, $month, $year)
    {

        $day_statement = new DayStatement();
        $day_statement->statement_date = $date;
        $day_statement->total_receive = 0;
        $day_statement->total_pay = 0;
        $day_statement->time = 0;
        $day_statement->flag = 0;
        $day_statement->summons = '';
        $day_statement->save();

        return $day_statement->id;
    }

    // 傳票號碼
    private function resetSummons($id, $month, $year)
    {
        // 傳票號碼 = 民國(3) - 月份(2) - 流水號(4) 例: 112-01-0001
        $code = ($year - 1911) . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-';
        $temp_summons = DayStatement::select('summons')->where('summons', 'like', $code . '%')
            ->orderBy('summons', 'desc')
            ->first();
        if ($temp_summons) {
            $temp_summons = explode('-', $temp_summons['summons']);
            $temp_summons[2] = str_pad((((int)$temp_summons[2]) + 1), 4, '0', STR_PAD_LEFT);
            $summons = implode('-', $temp_summons);
        } else {
            $summons = $code . '0001';
        }
        return $summons;
    }

    private function dayStatementIsSet($date)
    {
        return (!empty(DayStatement::select('id')->where('statement_date', '=', $date)->get()->toArray())) ? true : false;
    }

    public function isDayStatementId($id)
    {
        $day_statement = DayStatement::select('id')->where('id', '=', $id)->get()->toArray();
        if (empty($day_statement))  return Service::response('02', 'id');
        if ($id == 0)  return Service::response('02', 'id');
    }

    public function deleteData($id)
    {
        $this->addLog('2', $id);
        DayStatement::where('id', '=', $id)->update([
            'total_receive' => 0,
            'total_pay' => 0,
            'flag' => 1
        ]);
        DayStatementData::where('day_statement_id', '=', $id)->delete();
    }

    public function detail($id)
    {
        $day_statement = DayStatement::select('*')
            ->with(['day_statement_data' => function ($query) {
                $query->select(
                    'id',
                    'day_statement_id',
                    'debit_credit',
                    'summary',
                    'price',
                    'accounting_subject_id',
                    'pay_type',
                    'type',
                    'fk_id',
                    'code'
                );
            }])
            ->where('id', '=', $id)->get();
        $res = [];
        $res['date'][] = [(str_replace('-', '/', $day_statement[0]['statement_date'])), ''];
        $day_statement_data = [];
        foreach ($day_statement[0]['day_statement_data'] as $item) {
            $pay_type = '';
            $pay_type = ($item['pay_type'] == 0) ? '無' : Service::getStatusValue($item['pay_type']);

            $day_statement_data[] = [
                'id' => $item['id'],
                'type' => ($item['debit_credit'] == 2) ? true : false,
                'summary' => (!empty($item['summary']) ? $item['summary'] : ''),
                'price' => (!empty($item['price']) ? $item['price'] : ''),
                'account' => $pay_type
            ];
        }

        $res['list'] = [
            'total' => [
                'receive' => $day_statement[0]['total_receive'],
                'payment' => $day_statement[0]['total_pay']
            ],
            'data' => $day_statement_data
        ];
        $res['balance'] = [];
        $res['subject'] = [];
        $res['status'] = [
            'id' => '',
            'name' => '',
            'rank' => '',
            'audit' => '',
            'date' => '',
            'reason' => ''
        ];

        dd($day_statement->toArray(), $res);
    }
}
