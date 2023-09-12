<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Payment;
use App\Models\Accounting\PaymentItem;
use App\Models\Accounting\Cheque;
use App\Models\Accounting\Remit;
use App\Models\Administration\Review;
use App\Services\Service;
use App\Traits\ValidatorTrait;
use App\Traits\PaymentTrait;
use App\Traits\ReviewTrait;
use App\Traits\Num2Cht;
use Illuminate\Http\Request;

class PaymentController extends Controller
{

    use ValidatorTrait;
    use PaymentTrait;
    use ReviewTrait;
    use Num2Cht;
    
    public $service, $Payment, $PaymentItem, $Review, $Cheque, $Remit;

    function __construct()
    {
        $this->service     = new Service();
        $this->Payment     = new Payment();
        $this->PaymentItem = new PaymentItem();
        $this->Review      = new Review();
        $this->Cheque      = new Cheque();
        $this->Remit       = new Remit();
    }

    public function add(Request $request)
    {
        $req = $request->all();

        // 驗證資料
        $valid = $this->validData($req['list'], $this->PaymentItem->validationRules, $this->PaymentItem->validationMsg);
        if ($valid) {
            return $valid;
        }

        // 新增資料
        $create = $this->create($req['firm'], $req['list']);
        if ($create) {
            return $create;
        }

        return $this->service->response("00", "ok");
    }


    public function update(Request $request)
    {
        $req = $request->all();

        // 檢查審核，有審核退回
        $check = $this->Review->checkReview('payment', $req['id']);
        if ($check) {
            return $this->service->response('502', "id");
        }

        // 驗證資料
        $valid = $this->validData($req['list'], $this->PaymentItem->validationRules, $this->PaymentItem->validationMsg);
        if ($valid) {
            return $valid;
        }

        // 更新主表
        $payment = $this->Payment->updateData($req);
        // 更新細項
        $payment_item = $this->PaymentItem->updateData($req['id'], $req['list']);

        return $this->service->response("00", "ok");
    }

    public function list(Request $request)
    {
        $req = $request->all();

        // 預設筆數
        $count = (!empty($req['count'])) ? $req['count'] : 20;
        // 狀態字串轉數字
        $req['audit'] = $this->service->getStatusKey($req['audit']);

        // 查詢資料
        $results = $this->Payment->getPaymentList($count, $req);

        if (empty($results)) {
            return $this->service->response('02', "id");
        }

        foreach ($results[0] as $k => $v) {
            // 審核
            $audit = $this->Review->getReviewUser('payment', $v['id']);
            foreach ($audit as $k2 => $v2) {
                // 轉換日期為陣列
                $audit[$k2]['audit'] = $this->numberToStatus($v2['audit']);
                $audit[$k2]['date'] = ($v2['date'] == "") ? [] : $this->service->dateFormat($v2['date']);
            }

            $results[0][$k]['payDate'] = $this->service->dateFormat($v['payDate']);
            $results[0][$k]['status']  = $audit;
            foreach ($results[0][$k]['list'] as $k2 => $v2) {
                $results[0][$k]['list'][$k2]['type'] = (bool)$v2['type'];
            }
        }

        return $this->service->response_paginate("00", 'ok', $results[0], $results[1]);
    }

    public function pay(Request $request)
    {
        $req = $request->all();

        // 檢查審核，有審核退回
        $check = $this->Review->checkReview('payment', $req['id']);
        if ($check) {
            return $this->service->response('502', "id");
        }

        // 驗證資料
        $valid = $this->validData($req, $this->PaymentItem->validationRulesPay, $this->PaymentItem->validationMsgPay);
        if ($valid) {
            return $valid;
        }

        // 驗證支票資料 
        if ($req['payment'] == 'cheque') {
            $valid = $this->validData($req, $this->PaymentItem->validationRulesCheque, $this->PaymentItem->validationMsgCheque);
            if ($valid) {
                return $valid;
            }
        }

        // 判斷輸入金額是否超過付款單總金額(扣除已付金額)
        $check = $this->PaymentItem->checkPrice($req['id'], $req['price']);
        if (!$check) {
            return $this->service->response("501", "price");
        }

        // 新增付款細項
        $req['payment'] = $this->service->getStatusKey($req['payment']); // 付款方式轉數字
        $payment_item = $this->PaymentItem->pay($req);
        if (!$payment_item) {
            return $this->service->response("999", "");
        }

        // 依照付款方式新增相關單據
        switch ($req['payment']) {
            case 24:
                $create = $this->Cheque->createData('payment', $req['id'], $req['subject'], $req['ticketNumber'], $req['expirationDate'], $req['cashDate'], $req['price']);
                if (!$create) {
                    return $this->service->response("999", "");
                }
                break;
            case 25:
                $create = $this->Remit->createData('payment', $req['id'], $req['subject'], date('Y-m-d'), $req['price']);
                if (!$create) {
                    return $this->service->response("999", "");
                }
                break;
        }

        return $this->service->response("00", "ok");
    }

    public function detail(Request $request)
    {
        $req = $request->all();

        // 取主表資料
        $main = $this->Payment->getPaymentDetail($req['id']);

        // 取借方總金額
        $debit_total = $this->PaymentItem->getPrice($req['id'], 1);
        $debit_total = [$debit_total, $this->Num2Cht($debit_total)];

        // 取借方細項
        $debit_item = $this->PaymentItem->getPaymentItem($req['id'], 1);
        foreach ($debit_item as $k => $v) {
            if (!empty($v['subject'])) $debit_item[$k]['subject'] = $v['subject'][0];
        }

        // 取貸方總金額
        $credit_total = $this->PaymentItem->getPrice($req['id'], 2);
        $credit_total = [$credit_total, $this->Num2Cht($credit_total)];

        // 取貸方細項
        $credit_item = $this->PaymentItem->getPaymentItem($req['id'], 2);
        foreach ($credit_item as $k => $v) {
            if (!empty($v['subject'])) $credit_item[$k]['subject'] = $v['subject'][0];
        }

        // 審核
        $audit = $this->Review->getReviewUser('payment', $req['id']);
        foreach ($audit as $k => $v) {
            // 轉換日期為陣列
            $audit[$k]['audit'] = $this->numberToStatus($v['audit']);
            $audit[$k]['date'] = ($v['date'] == "") ? [] : $this->service->dateFormat($v['date']);
        }


        $results = $main[0];
        $results['firm'] = $results['firm'][0];
        $results['store'] = $results['store'][0];
        $results['expenditure']['total'] = $debit_total;
        $results['expenditure']['list']  = $debit_item;
        $results['payment']['total']     = $credit_total;
        $results['payment']['list']      = $credit_item;
        $results['status']  = $audit;

        // 轉換日期為陣列
        $results['date'] = $this->service->dateFormat($results['date']);

        return $this->service->response("00", "ok", $results);
    }

    public function del(Request $request)
    {

        $req = $request->all();

        // 檢查審核，有審核退回
        $check = $this->Review->checkReview('payment', $req['id']);
        if ($check) {
            return $this->service->response('502', "id");
        }

        // 刪除主表
        $this->Payment->del($req['id']);
        // 刪除細項
        $this->PaymentItem->del($req['id']);
        // 刪除簽核
        $this->Review->del('payment', $req['id']);
        // 刪除相關單據cheque、remit
        $this->Cheque->del('payment', $req['id']);
        $this->Remit->del('payment', $req['id']);

        return $this->service->response('00', "ok");
    }

    public function audit(Request $request)
    {

        $result = $this->updateReview(
            'payment',
            $request->id,
            'approval',
            ''
        );

        // 檢查層級，並發布通知        
        // if ($request->status == 'approval') {
        //     $review_list = $service->addAuditNotice('application',$request->id);
        // }

        return $this->service->response('00', "ok");
    }

    public function auditCancel(Request $request)
    {
        $result = $this->cancelReview(
            'payment',
            $request->id
        );

        return $this->service->response('00', "ok");
    }
}
