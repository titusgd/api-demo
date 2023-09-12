<?php

namespace App\Services\Invoice;
// self service 
use App\Services\Invoice\InvoiceService;

// laravel methods
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Str;
// trait
use App\Traits\ResponseTrait;
use App\Traits\RulesTrait;

class SearchService extends InvoiceService
{
    use RulesTrait, ResponseTrait;
    private $request;
    protected $ver = '2.0';

    public function __construct($request)
    {
        parent::__construct();
        $this->request = $request;
        // $this->request = $this->arrayKeySnakeToBigHump($request->all());
    }


    public function runValidate($method, $type = null)
    {
        switch ($method) {
            case 'searchAll':
                $rules = [
                    'search_type' => 'in:1,2,3,4',
                    'invoice_from' => 'nullable|in:"",0,1',
                    'invoice_status' => 'in:"",0,1,2,3',
                    'allow_status' => 'in:"",0,1',
                    'category' => 'in:"B2B","B2C"',
                    'start_date' => 'required|date_format:Y-m-d',
                    'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date|before:' . Carbon::parse($this->request['start_date'])->addDays(90)->format('Y-m-d'),
                ];
                $data = $this->request;
                break;
            case 'search':
                $rules['search_type'] = 'in:0,2,3';
                $rules['invoice_number'] = 'string|max:9';
                switch ($type) {
                    case '0':
                        $rules['random_num'] = 'required|string|max:8';
                        $rules['display_flag'] = 'nullable|in:"",0,1';
                        break;
                    case '2':
                        $rules['allowance_no'] = 'required|string|max:20';
                        break;
                    case '3':
                        $rules['invalid_no'] = 'required|string|max:20';
                        break;
                }
                $data = $this->request;
                break;
        }

        $this->response = self::validate($data, $rules);

        return $this;
    }
    public function runSearchAll()
    {
        if (!empty($this->response)) return $this;
        $request = $this->arrayKeySnakeToBigHump($this->request);
        $temp_data = collect($request)->map(function ($item, $key) {
            ($item === null) && $item = "";
            ($key == "StartDate" || $key == "EndDate") && $item = str_replace(["/", '-'], '-', $item);
            return $item;
        });
        $now_time = time();

        $temp_data
            ->put('Version', '2')
            ->put('TimeStamp', $now_time);
        $temp_data->forget('SearchType');
        $response =  InvoiceService::RunInvoiceMakeV2(
            $temp_data->toArray(),
            'invoice_searchall',
            $this->request['search_type']
        );
        if ($response['Status'] != 'SUCCESS') {
            $this->response = InvoiceService::response('01', '代碼:' . $response['Status'] . '(' . $response['Message'] . ')');
        } else {
            switch ($this->request['search_type']) {
                case 1:
                    $list = collect($response['ReturnInvoice'])->map(function ($item, $key) {
                        $temp_arr["invoice_trans_no"] = $item["InvoiceTransNo"];
                        $temp_arr["merchant_order_no"] = $item["MerchantOrderNo"];
                        $temp_arr["invoice_number"] = $item["InvoiceNumber"];
                        $temp_arr["random_num"] = $item["RandomNum"];
                        $temp_arr["buyer_name"] = $item["BuyerName"];
                        $temp_arr["buyer_UBN"] = $item["BuyerUBN"];
                        $temp_arr["buyer_address"] = $item["BuyerAddress"];
                        $temp_arr["buyer_phone"] = $item["BuyerPhone"];
                        $temp_arr["buyer_email"] = $item["BuyerEmail"];
                        $temp_arr["category"] = $item["Category"];
                        $temp_arr["total_amt"] = $item["TotalAmt"];
                        $temp_arr["create_time"] = $item["CreateTime"];
                        $temp_arr["invoice_status"] = $item["InvoiceStatus"];

                        $temp_item = collect(json_decode($item["ItemDetail"], true))->map(function ($item2, $key2) {
                            $temp_item_a['item_num'] = $item2["ItemNum"];
                            $temp_item_a['item_name'] = $item2["ItemName"];
                            $temp_item_a['item_count'] = $item2["ItemCount"];
                            $temp_item_a['item_word'] = $item2["ItemWord"];
                            $temp_item_a['item_price'] = $item2["ItemPrice"];
                            $temp_item_a['item_amount'] = $item2["ItemAmount"];
                            return $temp_item_a;
                        });

                        $temp_arr["item_detail"] = $temp_item;
                        $temp_arr["tour_name"] = $item["TourName"];
                        $temp_arr["tour_no"] = $item["TourNo"];
                        $temp_arr["tour_date"] = $item["TourDate"];
                        $temp_arr["tax_noted"] = $item["TaxNoted"];
                        return $temp_arr;
                    });
                    break;
                case 2:
                    $list = collect($response['ReturnInvoice'])->map(function ($item, $key) {
                        $temp_arr["allowanceNo"] = $item["AllowanceNo"];
                        $temp_arr["invoice_number"] = $item["InvoiceNumber"];
                        $temp_arr["buyer_email"] = $item["BuyerEmail"];
                        $temp_arr["total_amt"] = $item["TotalAmt"];
                        $temp_arr["create_time"] = $item["CreateTime"];
                        $temp_arr["allow_status"] = $item["AllowStatus"];
                        

                        $temp_item = collect(json_decode($item["ItemDetail"], true))->map(function ($item2, $key2) {
                            $temp_item_a['item_num'] = $item2["ItemNum"];
                            $temp_item_a['item_name'] = $item2["ItemName"];
                            $temp_item_a['item_count'] = $item2["ItemCount"];
                            $temp_item_a['item_word'] = $item2["ItemWord"];
                            $temp_item_a['item_price'] = $item2["ItemPrice"];
                            $temp_item_a['item_amount'] = $item2["ItemAmount"];
                            return $temp_item_a;
                        });
                        $temp_arr["item_detail"] = $temp_item;
                        return $temp_arr;
                    });
                    break;
                    case 3:
                        $list = collect($response['ReturnInvoice'])->map(function ($item, $key) {
                            $temp_arr["invalid_no"] = $item["InvalidNo"];
                            $temp_arr["invoice_number"] = $item["InvoiceNumber"];
                            $temp_arr["invalid_reason"] = $item["InvalidReason"];
                            $temp_arr["create_time"] = $item["CreateTime"];
                            $temp_arr["invalid_status"] = $item["InvalidStatus"];
                            return $temp_arr;
                        });
                        break;
            }
            $this->response = InvoiceService::response('00', 'ok', [
                'merchant_id' => $response['MerchantID'],
                'list' => $list
            ]);
        }
        // dd($response);
        // TODO:尚未完成檢查碼，須將回傳資料驗證補齊
        return $this;
    }
    public function runSearch($type)
    {
        if (!empty($this->response)) return $this;
        $request = $this->arrayKeySnakeToBigHump($this->request);
        $this->ver = "1.1";
        $temp_data = collect($request)->map(function ($item, $key) {
            if ($item === null) $item = "";
            return $item;
        });
        $now_time = time();
        $temp_data
            ->put('Version', $this->ver)
            ->put('TimeStamp', $now_time);

        $response =  InvoiceService::RunInvoiceMakeV2(
            $temp_data->toArray(),
            'invoice_search',
            $type
        );

        if ($response['Status'] != 'SUCCESS') {
            $this->response = InvoiceService::response('01', '代碼:' . $response['Status'] . '(' . $response['Message'] . ')');
        } else {
            if ($type == 3) {
                $this->response = InvoiceService::response('00', 'ok', [
                    'invalid_no' => $response['InvalidNo'],
                    'invoice_number' => $response['InvoiceNumber'],
                    'invalid_type' => $response['InvalidType'],
                    'invalid_status' => $response['InvalidStatus'],
                    'invalid_comment' => $response['invalidComment'],
                    'invalid_create_time' => $response['InvalidCreateTime'],
                    'invalid_date' => $response['InvalidDate'],
                    'seller_name' => $response['SellerName'],
                ]);
            }
            if ($type == 2) {
                $this->response = InvoiceService::response('00', 'ok', [
                    'allowance_no' => $response['AllowanceNo'],
                    'invoice_number' => $response['InvoiceNumber'],
                    'merchant_order_no' => $response['MerchantOrderNo'],
                    'buyer_email' => $response['BuyerEmail'],
                    'allowance_type' => $response['AllowanceType'],
                    'allowance_status' => $response['AllowanceStatus'],
                    'allowance_create_time' => $response['AllowanceCreateTime'],
                    'item_detail' => json_decode($response['ItemDetail'], true),
                    'allowance_total_amt' => $response['AllowanceTotalAmt'],
                    'remain_amt' => $response['RemainAmt'],
                    'seller_name' => $response['SellerName']
                ]);
            }
        }
        // TODO:尚未完成檢查碼，須將回傳資料驗證補齊
        return $this;
    }

    // public function getCheckCodeSearch(string $startDate, string $endDate, int $timeStamp)
    // {
    //     $key = $this->HashKey;
    //     $iv = $this->HashIV;

    //     $check_code_array = array(
    //         'MerchantID' => $this->MerchantID,
    //         'StartDate' => str_replace(['-', '/'], '', $startDate),
    //         'EndDate' => str_replace(['-', '/'], '', $endDate),
    //         'TimeStamp' => $timeStamp,
    //     );
    //     ksort($check_code_array);
    //     $check_code_str = http_build_query($check_code_array);
    //     $check_code_str = "HashIV=$iv&$check_code_str&HashKey=$key";
    //     $check_code = strtoupper(hash('sha256', $check_code_str));
    //     return $check_code;
    // }
    /**
     * getCheckCode 取得確認碼
     * 
     * @param array $check_code_array 確認驗證資料
     * $check_code_array 內容如下 :
     * 1. [
     * 'MerchantID' => '', // 旅行社統一編號
     *   'MerchantOrderNo' => '', // 旅行社自訂編號
     *   'InvoiceTransNo' => '', // 電子收據開立流水號
     *   'TotalAmt' => '', // 收據金額
     *   'RandomNum' => '' // 收據防偽隨機碼]
     * 
     * 2.[
     * StartDate' => '20200511', // 查詢起始日期(去除分隔號)
     * 'EndDate' => '20200515', // 查詢結束日期(去除分隔號)
     * 'TimeStamp' => 1588320000, // 查詢送出之時間戳記
     * ]
     */
    public function getCheckCode(array $check_code_array)
    {
        $key = $this->HashKey;
        $iv = $this->HashIV;

        // $check_code_array = array(
        //     'MerchantID' => $this->MerchantID,
        //     'StartDate' => str_replace(['-', '/'], '', $startDate),
        //     'EndDate' => str_replace(['-', '/'], '', $endDate),
        //     'TimeStamp' => $timeStamp,
        // );
        $check_code_array['MerchantID'] = $this->MerchantID;
        ksort($check_code_array);
        $check_code_str = http_build_query($check_code_array);
        $check_code_str = "HashIV=$iv&$check_code_str&HashKey=$key";
        $check_code = strtoupper(hash('sha256', $check_code_str));
        return $check_code;
    }
}
