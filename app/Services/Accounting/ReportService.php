<?php

namespace App\Services\Accounting;

use App\Services\Service;
use App\Models\Accounting\Order;
use App\Models\Accounting\OrderProduct;
use App\Services\Accounting\InvoiceService;
use Illuminate\Support\Facades\DB;
use App\Models\Accounting\Invoice;
use App\Traits\ReceiptTrait;
use Carbon\CarbonPeriod;

class ReportService extends Service
{
    use ReceiptTrait;

    private $req, $user_id, $store_id;
    private $invoice_arr = [];
    private $order_type = ['1' => '內用', '2' => '外帶', '3' => '外送'];
    private $order_type_en = ['1' => 'inside', '2' => 'takeaway', '3' => 'delivery'];
    private $type = ['1' => '已開立', '2' => '已作廢'];
    private $invoice_service;
    function __construct($req)
    {
        $this->req = Service::requireToArray($req);
        $this->user_id = Service::getUserId();
        $this->store_id = Service::getUserStoreId();
        $this->invoice_service = new InvoiceService();
    }

    // 新增 orders
    public function create($req)
    {
        if ($req['data']['0']['1'] == '發票號碼') unset($req['data']['0']);

        foreach ($req['data'] as $key => $val) {
            switch ($val['6']) {
                case '內用':
                    $val['6'] = 1;
                    break;
                case '外帶':
                    $val['6'] = 2;
                    break;
            }
            if (!Invoice::where('invoice', '=', $val['1'])->first()) $this->orderInsert($val);
        }

        if ($this->invoice_arr) {
            Invoice::insert($this->invoice_arr);
            $order_ids = array_column($this->invoice_arr, 'order_id');
            $inv_id = Invoice::select('id as invoice_id', 'order_id')->whereIn('id', $order_ids)->get();

            $order_id = '';
            $query = 'update orders set invoice_id = CASE id';
            foreach ($inv_id as $key => $val) {
                $query .= " when '{$val['order_id']}' then '{$val['invoice_id']}'";
                $order_id .= "{$val['order_id']},";
            }
            $order_id = substr($order_id, 0, -1);
            $query .= " END WHERE id IN ({$order_id})";

            DB::update(DB::raw($query));
        }

        // 新增收款單->依照匯入起始日到結束日，一天產生一張
        $date_key = count($req['data']);
        list($startDate, $startTime) = explode(" ", $req['data'][1]['2']);
        list($endDate, $endTime)     = explode(" ", $req['data'][$date_key]['2']);
        $period = new CarbonPeriod($startDate, '1 day', $endDate);

        foreach ($period as $key => $value) {
            $this->createReceipt($value->format('Y-m-d'));
        } 

        return Service::response("00", "ok");
    }

    public function orderInsert($arr)
    {
        list($date, $time) = explode(" ", $arr['2']);
        
        $order = Order::create([
            // "invoice_number" => $arr[1],  // 統一發票
            // "date" => $arr['2'],            // 結帳時間
            "date" => $date,
            "time" => $time,
            "code" => $arr['3'],            // 原始單號
            // $arr[4]外部訂單
            "source" => $arr['5'],          // 訂單來源
            // "order_type" => $this->getOrderTypeToNum($arr[6]),      // 訂單種類:1.內用 2.外帶
            "order_type" => $arr['6'],      // 訂單種類:1.內用 2.外帶
            "charge" => $arr['7'],          // 服務費
            // $arr[8]數值簡化
            "discount" => $arr['9'],        // 折扣金額細項
            "total" => $arr['10'],          // 結帳金額
            "payment" => $arr[11],        // 支付方式
            "payment_info" => $arr['12'],   // 付款資訊
            "payment_memo" => $arr['13'],   // 支付註記
            // "invoice_type" => $arr[14],   // 目前狀況 = 發票狀態
            "name" => $arr['15'],           // 顧客姓名
            "phone" => $arr['16'],          // 顧客電話
            "meal_mome" => $arr['17'],      // 餐點註記
            //$arr[18]品項->寫入分表order_products
            "customer" => $arr['19'],       // 訂購人
            "customer_phone" => $arr['20'], // 訂購人電話
            "invoice_id" => "",
            "user_id" => $this->user_id, // 匯入人員id
            "store_id" => $this->store_id, //分店id
            "import_source" => 1
        ]);
        // 發票狀態 解析、處理
        if (strpos($arr['14'], "作廢") !== false) {
            list($invoice_type, $invalid_date, $invalid_time) = explode(" ", $arr['14']);
            $invalid_date = "{$invalid_date} {$invalid_time}";
        } else {
            $invoice_type = $arr['14'];
            // $invalid_date = "0000-00-00 00:00:00";
            $invalid_date = null;
        }

        array_push($this->invoice_arr, [
            "invoice" => $arr['1'],
            "order_id" => $order['id'],
            // "date" => $arr['2'],// 結帳時間
            "date" => $date,
            "time" => $time,
            "type" => $this->getTypeCode($invoice_type),
            "invalid_date" => $invalid_date,
            "user_id" => $this->user_id,
            "total" => $arr['10']
        ]);

        // 新增資料至 order_product 
        $this->orderProductInsert($arr['18'], $order->id);
        return $order->id;
    }

    private function orderProductInsert($str, $oreder_id)
    {
        $temp = explode(',', $str);
        $temp_arr = [];
        foreach ($temp as $key => $val) {
            list($name, $price) = explode('$', $val);
            $price = str_replace('-', '', $price);
            $price = str_replace("\$", '', $price);
            $temp_arr[$key]['name'] = $name;
            $temp_arr[$key]['price'] = $price;
            $temp_arr[$key]['order_id'] = $oreder_id;
        }
        OrderProduct::insert($temp_arr);
    }

    /** getOrderTypeToNum()
     *  取得訂單種類編號 : 1.內用|2.外帶
     */
    private function getOrderTypeToNum($str, $case = 1)
    {
        switch ($case) {
            case 1:
                return array_search($str, $this->order_type);
                break;
            case 2:
                return array_search($str, $this->order_type_en);
                break;
        }
    }

    /** getOrderTypeToStr()
     *  取得訂單種類名稱 : 1.內用|2.外帶
     */
    private function getOrderTypeToStr($num)
    {
        return $this->order_type[$num];
    }


    /** getRequestArray()
     *  取得轉換過的 request
     */
    public function getRequestArray()
    {
        return $this->req;
    }

    // transactio request 資料檢查
    public function transactioValidator($req)
    {
        $vali = [
            'date.start' => 'required|date',
            'date.end' => 'required|date',
            'count' => 'required|integer',
            'page' => 'required|integer',
            'store' => 'required|integer|exists:stores,id',

        ];

        if (!empty($req['people'])) {
            $vali['people'] = 'array';
            $vali['people.*.*'] = 'integer';
        }

        if (!empty($req['type'])) {
            $vali['type'] = 'array';
            if (!empty($req['type']['inside'])) $vali['type.inside'] = 'boolean';
            if (!empty($req['type']['inside'])) $vali['type.takeaway'] = 'boolean';
            if (!empty($req['type']['inside'])) $vali['type.delivery'] = 'boolean';
        }

        $msg = [
            'date.start.required' => '01 start',
            'date.start.date' => '01 start',
            'date.end.required' => '01 end',
            'date.end.date' => '01 end',
            'count.required' => '01 count',
            'count.integer' => '01 count',
            'page.required' => '01 page',
            'page.integer' => '01 page',
            'people.integer' => '01 people',
            'type.integer' => '01 type',
            'type.regex' => '02 type',
            'people.array' => '01 people',
            'people.*.integer' => '01 people',
            'type.array' => '01 type',
            'type.*.boolean' => '01 type',
            'store.required' => '01 store',
            'store.integer' => '01 store',
            'store.exists' => '02 store'

        ];
        $errors = Service::validatorAndResponse($req, $vali, $msg);
        return $errors;
    }

    public function transactioValidatorTime($req)
    {
        if (!empty($req['time']['start'])) {
            $time_star = explode(':', $req['time']['start']);
            if ($time_star[0] <= -1 || $time_star[0] > 24) return Service::response('01', 'start');
            if ((int)$time_star[1] <= -1 || (int)$time_star[1] > 60) return Service::response('01', 'start');
        }
        if (!empty($req['time']['end'])) {
            $time_end = explode(':', $req['time']['end']);
            // 時
            if ($time_end[0] <= -1 || $time_end[0] > 24) return Service::response('01', 'end');
            // 分
            if ($time_end[1] <= -1 || $time_end[1] > 60) return Service::response('01', 'end');
        }
    }

    // 取得列表
    public function getList($req)
    {
        // 資料比數
        $count = (!isset($req['count'])) ? 20 : $req['count'];
        //----------------------------------------------------------------------
        $order_query = Order::select(
            '*',
            DB::raw('(select `type` from `invoices` where invoices.order_id = orders.id) as invoice_type'),
            DB::raw('(select `invoice` from `invoices` where invoices.order_id = orders.id) as invoice'),
            DB::raw('(select `total` from `invoices` where invoices.order_id = orders.id) as invalid_price')
        )
            ->whereBetween('date', [$req['date']['start'], $req['date']['end']])
            ->where('time', '>=', ($req['time']['start'] ?: '23:59') . ':00')
            ->where('time', '<=', ($req['time']['end'] ?: '23:59') . ':00')
            ->where('store_id', '=', (!empty($req['store'])) ? $req['store'] : auth()->user()->store_id);
            
        if (!empty($req['type'])) {
            $type_temp = [];
            $false_sum = 0;
            foreach ($req['type'] as $key => $val) {
                ($val) ? array_push($type_temp, $this->getOrderTypeToNum($key, 2)) : null;
            }
            $order_query->whereIn('order_type', $type_temp);
        }

        $data_all = $order_query->get();

        $info = [];
        $info['sum'] = $data_all->sum('total');                 // 結帳總金額
        $info['charge'] = $data_all->sum('charge');             // 服務費
        $info['discount'] = $data_all->sum('discount');         // 折扣金
        $info['receipt'] = count($data_all);                    // 開立發票總數
        $temp_invalid = 0;
        $invalid_price = 0;

        // 作廢加總
        foreach ($data_all as $val) {
            if ($val['invoice_type'] == 2) {
                $invalid_price += $val['invalid_price'];
                $temp_invalid++;
            }
        }
        $info['sum'] = $info['sum'] - $invalid_price;
        // 作廢發票數
        $info['invalid'] = $temp_invalid;
        // ------------------------------------- 分頁資訊輸出設定 -------------------------------------------------------
        $order_query = $order_query->orderBy('date', 'desc');
        $order_query = $order_query->orderBy('time', 'desc');
        $data_limit = $order_query->paginate($count);
        $current_page = $data_limit->lastPage();    //總頁數
        $total = $data_limit->total();             //總筆數
        $page = $data_limit->currentPage();

        // ------------------------------------- 輸出資料欄位格式設定 ----------------------------------------------------
        $list = [];
        foreach ($data_limit as $key => $val) {
            $list[$key]['order']['number'] = $val['code'];                                          // 單號
            $list[$key]['order']['source'] = $val['source'];                                        // 訂單來源
            $list[$key]['order']['external'] = $val['import_source'];                               // 外部單號

            $list[$key]['order']['date'] = [$val['date'], $val['time']];                            // 結帳時間
            $list[$key]['order']['type'] = $this->getOrderTypeToStr($val['order_type']);            // 訂單種類
            $list[$key]['order']['payment'] = $val['payment'];                                      // 支付方式
            $list[$key]['order']['name'] = $val['name'];                                            // 顧客姓名
            // invoice
            $list[$key]['invoice']['number'] = $val['invoice'];                                     // 發票號碼
            $list[$key]['invoice']['type'] = $this->getTypeStr($val['invoice_type']);               // 發票類別
            // price
            $list[$key]['price']['total'] = $val['total'];                                          // 結帳金額
            $list[$key]['price']['discount'] = $val['discount'];                                    // 折扣金額
            $list[$key]['price']['charge'] = $val['charge'];                                        // 服務費
        }

        // ------------------------------------------ response json set --------------------------------
        $page_info = [
            'total' => $current_page,           // 總頁數
            'countTotal' => $total,             // 總筆數
            'page' => $page,                    // 頁次
            'count' => $count
        ];
        $data = [
            'info' => $info,
            'list' => $list,
        ];
        return Service::response_paginate('00', 'ok', $data, $page_info);
    }

    public function getTypeCode($str)
    {
        return array_search($str, $this->type);
    }
    public function getTypeStr($sum)
    {
        return $this->type[$sum];
    }
}
