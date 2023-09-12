<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Receipt;
use App\Models\Accounting\ReceiptItem;
use App\Models\Administration\Review;
use App\Services\Service;
use App\Traits\ValidatorTrait;
use App\Traits\ReceiptTrait;
use App\Traits\DateTrait;
use App\Traits\Num2Cht;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{

    use ValidatorTrait;
    use ReceiptTrait;
    use Num2Cht;
    use DateTrait;

    function __construct()
    {
        $this->service     = new Service();
        $this->Receipt     = new Receipt();
        $this->ReceiptItem = new ReceiptItem();
        $this->Review      = new Review();
    }

    public function list(Request $request)
    {
        $req = $request->all();

        // 預設筆數
        $count = (!empty($req['count'])) ? $req['count'] : 20;
        // 狀態字串轉數字
        $req['audit'] = $this->service->getStatusKey($req['audit']);

        // 查詢資料
        $results = $this->Receipt->getReceiptList($count, $req);

        if ( empty($results) ) {
            return $this->service->response('02', "id");   
        }

        foreach ( $results[0] as $k => $v ) {
            // 審核
            $audit = $this->Review->getReviewUser('receipt',$v['id']);
            foreach ( $audit as $k2 => $v2 ) {
                // 轉換日期為陣列
                $audit[$k2]['audit'] = $this->numberToStatus($v2['audit']);
                $audit[$k2]['date'] = ( $v2['date'] == "" ) ? [] : $this->service->dateFormat($v2['date']);

            }

            $results[0][$k]['payDate']    = $this->service->dateFormat($v['payDate']);
            $results[0][$k]['receiveDate']= $this->service->dateFormat($v['receiveDate']);
            $results[0][$k]['status']     = $audit;
            foreach ( $results[0][$k]['list'] as $k2 => $v2 ) {
                $results[0][$k]['list'][$k2]['type'] = (boolean)$v2['type'];
            }  
        }

        return $this->service->response_paginate("00",'ok',$results[0],$results[1]);
    }

    public function detail(Request $request) 
    {
        $req = $request->all();

        // 取主表資料
        $main = $this->Receipt->getReceiptDetail($req['id']);

        // 取貸方總金額
        $credit_total = $this->ReceiptItem->getPrice($req['id'],2);
        $credit_total = [$credit_total,$this->Num2Cht($credit_total)];
        
        // 取貸方細項
        $credit_item = $this->ReceiptItem->getReceiptItem($req['id'],2);
        foreach ( $credit_item as $k => $v ) {
            if ( !empty($v['subject']) ) {
                $credit_item[$k]['subject'] = $v['subject'][0];
            } else {
                $credit_item[$k]['subject'] = ['id'=>null,'code'=>'','name'=>''];
            }
        }

        // 取借方總金額
        $debit_total = $this->ReceiptItem->getPrice($req['id'],1);
        $debit_total = [$debit_total,$this->Num2Cht($debit_total)];

        // 取借方細項
        $debit_item = $this->ReceiptItem->getReceiptItem($req['id'],1);
        foreach ( $debit_item as $k => $v ) {
            if ( !empty($v['subject']) ) {
                $debit_item[$k]['subject'] = $v['subject'][0];
            } else {
                $debit_item[$k]['subject'] = ['id'=>null,'code'=>'','name'=>''];
            }
        }

        // 審核
        $audit = $this->Review->getReviewUser('receipt',$req['id']);
        foreach ( $audit as $k => $v ) {
            // 轉換日期為陣列
            $audit[$k]['audit'] = $this->numberToStatus($v['audit']);
            $audit[$k]['date'] = ( $v['date'] == "" ) ? [] : $this->service->dateFormat($v['date']);
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
        $results['date']        = $this->service->dateFormat($results['date']);
        $results['receiveDate'] = $this->service->dateFormat($results['receiveDate']);
       
        return $this->service->response("00","ok",$results);  
    }


    public function update(Request $request)
    {
        $req = $request->all();

        // return $this->createReceipt('2022-06-13');

        // 檢查審核，有審核退回
        $check = $this->Review->checkReview('receipt',$req['id']);
        if ( $check ) {
            return $this->service->response('502', "id");
        }

        // 驗證資料
        $valid = $this->validData($req['list'], $this->ReceiptItem->validationRules, $this->ReceiptItem->validationMsg);
        if ($valid) {
            return $valid;
        }        

        // 更新主表
        // $recepit = $this->Receipt->updateData($req);
        // 更新細項
        $recepit_item = $this->ReceiptItem->updateData($req['id'],$req['list']);

        return $this->service->response("00","ok"); 

    }

    public function del(Request $request)
    {

        $req = $request->all();

        // 檢查審核，有審核退回
        $check = $this->Review->checkReview('receipt',$req['id']);
        if ( $check ) {
            return $this->service->response('502', "id");
        }
        
        // 刪除主表
        $this->Recepit->del($req['id']);
        // 刪除細項
        $this->RecepitItem->del($req['id']);
        // 刪除簽核
        $this->Review->del('receipt',$req['id']);
        // 刪除相關單據cheque、remit
        $this->Cheque->del('receipt',$req['id']);
        $this->Remit ->del('receipt',$req['id']);

        return $this->service->response('00', "ok");        
    }

    public function audit(Request $request)
    {

        $result = $this->updateReview(
            'receipt',
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
            'receipt',
            $request->id
        );

        return $this->service->response('00', "ok");        
    }


}
