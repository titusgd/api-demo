<?php

namespace App\Http\Controllers\LinePay;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Order;
use App\Traits\ValidatorTrait;
use App\Services\LinePay\LinePayService;

class LinePayController extends Controller
{

    private $service;

    use ValidatorTrait;

    function __construct()
    {
        $this->service = new LinePayService;
    }

    // line pay 付款
    public function index(Request $request)
    {

        $req = $request->all();
        // 驗證資料
        $valid = $this->validData($req, $this->service->validationRules, $this->service->validationMsg);
        if ($valid) {
            return $valid;
        }

        return $this->service->index($request);

    }

    // line pay 付款確認
    public function confirm(Request $request)
    {
        $req = $request->all();

        $order = Order::where('order_no', $req['orderId'])->first();
        $order->transactionId = $req['transactionId'];
        $order->status = 1;
        $order->save();

        return "訂單：" . $req['orderId'] . " 已經付款成功";
    }

    // line pay 取消
    public function refund(Request $request)
    {
        $req = $request->all();
        dd($req);
    }

}
