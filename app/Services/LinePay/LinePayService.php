<?php

namespace App\Services\LinePay;

use App\Services\Service;
use Exception;

use App\Models\Order;
use App\Models\LinePayOrder;
use App\Models\LinePayOrderPackage;
use App\Models\LinePayOrderProduct;

use yidas\linePay\Response;
/**
 * Class LinePayService.
 */
class LinePayService extends Service
{

    public $validationRules = [
        'amount' => 'required|integer',
        'order_id' => 'required|string',
        'packages.*.id' => 'required|string',
        'packages.*.amount' => 'required|integer',
        'packages.*.name' => 'required|string',
        'packages.*.products.*.name' => 'required|string',
        'packages.*.products.*.quantity' => 'required|integer',
        'packages.*.products.*.price' => 'required|integer',
    ];

    public $validationMsg = [
        'amount.required' => '01 amount',
        'amount.integer' => '01 amount',
        'order_id.required' => '01 order_id',
        'order_id.string' => '01 order_id',
        "packages.*.id.required"   => "01 packages_id",
        "packages.*.id.string"     => "01 packages_id",
        "packages.*.amount.required"   => "01 packages_amount",
        "packages.*.amount.integer"     => "01 packages_amount",
        "packages.*.name.required"   => "01 packages_name",
        "packages.*.name.string"     => "01 packages_name",
        "packages.*.products.*.name.required"   => "01 packages.*.products.*.name",
        "packages.*.products.*.name.string"     => "01 packages.*.products.*.name",
        "packages.*.products.*.quantity.required"   => "01 packages.*.products.*.quantity",
        "packages.*.products.*.quantity.integer"     => "01 packages.*.products.*.quantity",
        "packages.*.products.*.price.required"   => "01 packages.*.products.*.price",
        "packages.*.products.*.price.integer"     => "01 packages.*.products.*.price",
    ];

    public function index($req)
    {

        $linePay = new \yidas\linePay\Client([
            'channelId' => env('LINE_PAY_CHANNEL_ID'),
            'channelSecret' => env('LINE_PAY_CHANNEL_SECRET'),
            'isSandbox' => env('LINE_PAY_SANDBOX')
        ]);

        $orders = [];
        $orders['orderId'] = $req['order_id'];
        $orders['amount'] = $req['amount'];
        $orders['currency'] = 'TWD';

        // 寫入 orders
        $order = new Order();
        $order->type = 2;
        $order->order_no = $req['order_id'];
        $order->amount = $req['amount'];
        $order->on_line = 1;
        $order->status = 0;
        $order->save();

        // 寫入 line_pay_order
        $linePayOrder = new LinePayOrder;
        $linePayOrder->order_id = $req['order_id'];
        $linePayOrder->amount = $req['amount'];
        $linePayOrder->currency = 'TWD';
        $linePayOrder->save();

        $orders['redirectUrls']['confirmUrl'] = env('LINE_PAY_CONFIRM_URL');
        $orders['redirectUrls']['cancelUrl'] = env('LINE_PAY_CANCEL_URL');

        foreach($req['packages'] as $key => $value){
            // 寫入 line_pay_order_package
            $linePayOrderPackage = new LinePayOrderPackage;
            $linePayOrderPackage->order_id = $req['order_id'];
            $linePayOrderPackage->package_main_id = $value['id'];
            $linePayOrderPackage->amount = $value['amount'];
            $linePayOrderPackage->name = $value['name'];
            $linePayOrderPackage->save();

            $orders['packages'][$key]['id'] = $value['id'];
            $orders['packages'][$key]['amount'] = $value['amount'];
            $orders['packages'][$key]['name'] = $value['name'];
            foreach($value['products'] as $k => $v){

                // 寫入 line_pay_order_product
                $linePayOrderProduct = new LinePayOrderProduct;
                $linePayOrderProduct->order_id = $req['order_id'];
                $linePayOrderProduct->package_id = $linePayOrderPackage->id;
                $linePayOrderProduct->name = $v['name'];
                $linePayOrderProduct->quantity = $v['quantity'];
                $linePayOrderProduct->price = $v['price'];
                $linePayOrderProduct->save();

                $orders['packages'][$key]['products'][$k]['name'] = $v['name'];
                $orders['packages'][$key]['products'][$k]['quantity'] = $v['quantity'];
                $orders['packages'][$key]['products'][$k]['price'] = $v['price'];
            }
        }


        $response = $linePay->request($orders);

        // Check Request API result (returnCode "0000" check method)
        if (!$response->isSuccessful()) {
            return Service::response('01', '', "ErrorCode {$response['returnCode']}: {$response['returnMessage']}");
        } else {
            return Service::response('00', 'OK', $response->toArray());
        }


    }

}
