<?php

namespace App\Http\Controllers\PettyCash;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\PettyCash\PettyCashService;
use App\Services\Service;
use App\Services\Administrations\PaymentVoucherService;

class PettyCashController extends Controller
{
    private $service;

    function __construct()
    {
        $this->service = new PettyCashService();
    }


    // 零用金申請
    public function apply(Request $request)
    {
        $req = $request->all();
        $vali = $this->service->validate($req);
        // inpute 檢查 column
        if ($vali->fails()) {
            $msg = explode(" ", $vali->errors()->first());
            return $this->service->response($msg[0], $msg[1]);
        }

        // -------------------- 資料檢查 --------------------
        if (!$this->service->checkStoreId($req['store'])) return $this->service->response("02", "store");
        // 字串切割，以"."作為分割條件
        $price = explode(".", $req['price'], 2);
        $serv = new Service();
        if ($price[0] == 0) return $serv->response("01", "price");
        if ($price[0] >= 100000000) return $serv->response("01", "price");
        
        if (!empty($price[1])) {
            if ($price[1] >= 100) return $serv->response("01", "price");
        }

        // -------------------- 新增資料 ----------------------
        $petty_cash = $this->service->create($req);
        if (empty($request->proposal)) {
            $pv_service = new PaymentVoucherService;

            $price = number_format($request->price, 2, '.', ',');

            $store_id = auth()->user()->store_id;
            $store = $pv_service->getStoreName($store_id);
            $content =  '分店：' . $store['store'] . " 申請零用金： {$price} 元整。";

            $product = [["summary" => '零用金', 'qty' => 1, 'price' => $request->price]];

            // 新增支憑、審核、通知
            $pv_service->addPaymentAndNotice(
                '零用金申請',
                $content,
                $product,
                $store_id,
                auth()->user()->id
            );
        }
        return ($petty_cash) ? $this->service->response("00", "ok") : $this->service->response("999");
    }

    public function record(Request $request)
    {
        $service = new PettyCashService();
        // 檢查資料
        $req = $request->all();
        // 回傳錯誤
        if ($service->recordVali($req)) return $service->recordVali($req);
        // 回傳查詢結果
        return $service->searchPettyCashApply($req);
    }
}
