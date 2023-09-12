<?php

namespace App\Services;

use App\Models\DynamicTableModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Traits\AccessTrait;
use App\Traits\DBTrait;
// use App\Traits\Date;
use App\Traits\CountTimeTrait;
class Service
{
    // use Date,CallFirmApi;
    use AccessTrait,DBTrait,CountTimeTrait;
    private $headers = array(
        "Cache-Control"=>'no-cache, no-store, must-revalidate',
        'X-Content-Type-Options' => 'nosniff',
        'X-XSS-Protection' => '1',
        'Content-Type' => 'application/json; charset=utf-8',

    );
    private $user_id;

    private $status = [
        1  => 'pending',    // 未處理
        2  => 'processing', // 處理中
        3  => 'solved',     // 已處理
        4  => 'closed',     // 結案
        5  => 'in',         // 上班
        6  => 'out',        // 下班
        7  => "all",        // 所有
        8  => "apply",      // 申請中
        9  => "operation",  // 會計作業
        10 => "provide",    // 已發放
        11 => "receive",    // 已提領
        12 => "fail",       // 未通過
        13 => 'cancel',     // 取消
        14 => 'signed',     // 已簽收
        15 => 'paid',       // 已付款
        16 => 'unpaid',     // 未付款
        17 => 'received',   // 未付款
        18 => 'unreceived', // 未付款
        19 => 'audited',    // 已審核
        20 => 'unaudited',  // 未審核
        21 => 'director_audited',    // 已審核
        22 => 'director_unaudited',  // 未審核
        23 => "cash",        // 現金
        24 => "cheque",      // 支票
        25 => "remit",       // 匯款
        26 => "payable",     // 應付帳款
        27 => "receivable",  // 應收帳款
        28 => "creditCard",  // 信用卡
        29 => "digitalPayment", // 線上/電子支付
        30 => "other",          // 其他
    ];


    public function __construct(){


    }

    /** getStatusKey()
     *  取得狀態編號
     * 1=>'pending' 未處理 | 2=>'processing' 處理中 | 3=>'solved' 已處理 |  4=>'closed' 結案 | 5=>'in' 上班 | 6=>'out' 下班 |
     * 7=>"all" 所有 | 8=>"apply" 申請中 | 9=>"operation" 會計作業 | 10=>"provide" 已發放 | 11=>"receive" 已提領 | 12=>"fail" 未通過 |
     * 13=>"cancel" 取消 | 14=>"signed" 已簽收 |
     * @param string $value 狀態
     * @return integer
     *
     */
    public function getStatusKey($value)
    {
        return array_search($value, $this->status);
    }
    /** getStatusValue()
     *  取得狀態說明文字
     * 1=>'pending' 未處理 | 2=>'processing' 處理中 | 3=>'solved' 已處理 |  4=>'closed' 結案 | 5=>'in' 上班 | 6=>'out' 下班 |
     * 7=>"all" 所有 | 8=>"apply" 申請中 | 9=>"operation" 會計作業 | 10=>"provide" 已發放 | 11=>"receive" 已提領 | 12=>"fail" 未通過 |
     * 13=>"cancel" 取消 | 14=>"signed" 已簽收 |
     * @param integer $value 編碼
     * @return string
     *
     */
    public function getStatusValue($key)
    {
        return $this->status[$key];
    }
    // XXX 未來待廢除
    public function requireToArray($request)
    {
        $req = json_decode($request->getContent(), true);
        return $req;
    }

    public function dateFormat($date)
    {
        $date = explode(' ', str_replace('-', '/', $date));
        if ( count($date) == 1 ) {
            array_push($date, '');
        }
        return $date;
    }

    /** response
     *  response
     *  @param string $code 代碼
     *  @param string $msg 錯誤訊息
     *  @param array $data 資料
     *  @return json
     *
     */
    public function response($code, $message = "", $data = [])
    {
        $res = [
            "code" => $code,
            "msg" => $message,
            "data" => $data
        ];
        return response()->json($res, 200, $this->headers, JSON_UNESCAPED_UNICODE);
    }

    public function response_paginate($code, $message = "", $data = [], $page = [])
    {
        $res = [
            "code" => $code,
            "msg" => $message,
            "page" => $page,
            "data" => $data
        ];
        return response()->json($res, 200, $this->headers, JSON_UNESCAPED_UNICODE);
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function array_replace_key($array, $old_key, $new_key)
    {
        $keys = array_keys($array);
        $index = array_search($old_key, $keys);
        $keys[$index] = $new_key;
        return array_combine($keys, array_values($array));
    }

    public function statusToString($str)
    {
        $status_str = "";
        switch ($str) {
            case 1:
                $status_str = "pending";
                break;
            case 2:
                $status_str = "processing";
                break;
            case 3:
                $status_str = "solved";
                break;
            case 4:
                $status_str = "closed";
                break;
            case 5:
                $status_str = "in";
                break;
            case 6:
                $status_str = "out";
                break;
        }
        return $status_str;
    }

    /** dateROCToAD()
     *  民國轉西元年
     *  @param string $str 民國 yyymmdd 或 yyy/mm/dd 或yyy-mm-dd
     *  @param string $replace
     *  @return string yyyy-mm-dd
     */
    public function dateROCToAD($str, $replace)
    {
        $str = str_replace("/", "", $str);
        $str = str_replace("-", "", $str);

        $data = [];
        $date_array = str_split($str);
        $roc = [];
        array_push($roc, $date_array[0]);
        array_push($roc, $date_array[1]);
        array_push($roc, $date_array[2]);

        $mm = [];
        array_push($mm, $date_array[3]);
        array_push($mm, $date_array[4]);
        $dd = [];
        array_push($dd, $date_array[5]);
        array_push($dd, $date_array[6]);

        $roc = implode("", $roc);
        $mm = implode("", $mm);
        $dd = implode("", $dd);

        $ad = $roc + 1911;

        $date = implode($replace, [$ad, $mm, $dd]);
        return $date;
    }

    /** dateADToRoc()
     *  民國轉西元年
     *  @param string $str 西元 yyyymmdd 或 yyyy/mm/dd 或 yyyy-mm-dd
     *  @param string $replace
     *  @return string yyyy-mm-dd
     */
    public function dateADToRoc($str, $replace)
    {
        $str = str_replace("/", "", $str);
        $str = str_replace("-", "", $str);

        $data = [];
        $date_array = str_split($str);
        $ad = [];
        array_push($ad, $date_array[0]);
        array_push($ad, $date_array[1]);
        array_push($ad, $date_array[2]);
        array_push($ad, $date_array[3]);

        $mm = [];
        array_push($mm, $date_array[4]);
        array_push($mm, $date_array[5]);
        $dd = [];
        array_push($dd, $date_array[6]);
        array_push($dd, $date_array[7]);

        $ad = implode("", $ad);
        $mm = implode("", $mm);
        $dd = implode("", $dd);

        $roc = $ad - 1911;

        $date = implode($replace, [$roc, $mm, $dd]);
        return $date;
    }

    /** getUserId()
     *  取得使用者
     *  @return int
     */
    public function getUserId()
    {
        $this->user_id  = Auth::user()->id;
        return $this->user_id;
    }

    /** checkValiDate()
     *  @param obj|array $validate - Validation object
     *  @return array|object
     */
    public function checkValiDate($validate)
    {
        if ($validate->fails()) {
            list($code, $message) = explode(" ", $validate->errors()->first());
            return $this->response($code, $message);
        }
    }
    /** validatorAndResponse()
     *  資料驗證，並回傳一筆錯誤，response 格式。
     *  @param array $data 檢測資料
     *  @param array $relus 檢測條件
     *  @param array $message 錯誤訊息
     *  @param array $customAttributes
     *  @return obj
     */
    public function validatorAndResponse($data, $relus, $message = [], $customAttributes = [])
    {
        $vali = Validator::make($data, $relus, $message, $customAttributes);
        $errors = $this->checkValiDate($vali);
        if ($errors) {
            return $errors;
        }
    }


}
