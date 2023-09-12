<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\TransferVoucher;
use App\Models\Accounting\TransferVoucherItem;
use App\Models\Administration\Review;
use App\Services\Service;
use App\Traits\ValidatorTrait;
use App\Traits\ReviewTrait;
use Illuminate\Http\Request;

class TransferVoucherController extends Controller
{
    use ValidatorTrait;
    use ReviewTrait;
    public $service,$Transfer,$TransferItem,$Review;
    function __construct()
    {
        $this->service      = new Service();
        $this->Transfer     = new TransferVoucher();
        $this->TransferItem = new TransferVoucherItem();
        $this->Review       = new Review();
    }

    public function add(Request $request)
    {
        $req = $request->all();
        $user_id = $this->service->getUserId();
        $store_id = $this->service->getUserStoreId();
        // $user_id=1;
        // $store_id=1;

        // 驗證資料
        $valid = $this->validData($req['list'], $this->TransferItem->validationRules, $this->TransferItem->validationMsg);
        if ($valid) {
            return $valid;
        }        
       
        // 新增主表資料
        $code = $this->service->getCode('Z','transfer_vouchers',$store_id);  // 取單號
        $insert_id = $this->Transfer->createData($store_id, $user_id, $code, $req);

        // 新增細項資料
        $this->TransferItem->createData($insert_id, $req['list']);

        // 建立 審核人員清單
        $review = $this->createReview(
            rank: $this->getReviewRank('paymentVoucher', 2), 
            fk_id: $insert_id,
            type: 'transfer_voucher'
        );

        return $this->service->response("00","ok");  
    }


    public function update(Request $request)
    {
        $req = $request->all();

        // 檢查審核，有審核退回
        $check = $this->Review->checkReview('transfer_voucher',$req['id']);
        if ( $check ) {
            return $this->service->response('502', "id");
        }

        // 驗證資料
        $valid = $this->validData($req['list'], $this->TransferItem->validationRules, $this->TransferItem->validationMsg);
        if ($valid) {
            return $valid;
        }        

        // 更新主表
        $this->Transfer->updateData($req);
        // 更新細項
        $this->TransferItem->updateData($req['id'],$req['list']);

        return $this->service->response("00","ok");  
    }

    public function list(Request $request)
    {
        $req = $request->all();

        // 預設筆數
        $count = (!empty($req['count'])) ? $req['count'] : 20;
        // 狀態字串轉數字
        $req['audit'] = $this->service->getStatusKey($req['audit']);

        // 查詢資料
        $results = $this->Transfer->getTransferList($count, $req);

        if ( empty($results) ) {
            return $this->service->response('02', "id");   
        }

        foreach ( $results[0] as $k => $v ) {
            // 審核
            $audit = $this->Review->getReviewUser('transfer_voucher',$v['id']);
            foreach ( $audit as $k2 => $v2 ) {
                // 轉換日期為陣列
                $audit[$k2]['audit'] = $this->numberToStatus($v2['audit']);
                $audit[$k2]['date'] = ( $v2['date'] == "" ) ? [] : $this->service->dateFormat($v2['date']);

            }
            $results[0][$k]['store'] = $v['store'][0];
            $results[0][$k]['Date']= $this->service->dateFormat($v['Date']);
            $results[0][$k]['status']  = $audit;
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
        $main = $this->Transfer->getTransferDetail($req['id']);

        // 取貸方總金額
        $credit_total = $this->TransferItem->getPrice($req['id'],2);

        // 取借方總金額
        $debit_total = $this->TransferItem->getPrice($req['id'],1);

        // 取細項
        $item = $this->TransferItem->getTransferItem($req['id']);
        foreach ( $item as $k => $v ) {
            // 轉換日期為陣列
            $item[$k]['type'] = (boolean)$v['type'];
            $item[$k]['subject'] = $v['subject']['0'];
        }

        // 審核
        $audit = $this->Review->getReviewUser('transfer_voucher',$req['id']);
        foreach ( $audit as $k => $v ) {
            // 轉換日期為陣列
            $audit[$k]['audit'] = $this->numberToStatus($v['audit']);
            $audit[$k]['date'] = ( $v['date'] == "" ) ? [] : $this->service->dateFormat($v['date']);
        }

        $results = $main[0];
        $results['store'] = $results['store'][0];
        $results['total'] = [
            "debit" =>$debit_total,
            "credit"=>$credit_total
        ];
        $results['list'] = $item;
        $results['status']  = $audit;

        // 轉換日期為陣列
        $results['date'] = $this->service->dateFormat($results['date']);
        
        return $this->service->response("00","ok",$results);  
    }

    public function del(Request $request)
    {

        $req = $request->all();

        // 檢查審核，有審核退回
        $check = $this->Review->checkReview('transfer_voucher',$req['id']);
        if ( $check ) {
            return $this->service->response('502', "id");
        }
        
        // 刪除主表
        $this->Transfer->del($req['id']);
        // 刪除細項
        $this->TransferItem->del($req['id']);
        // 刪除簽核
        $this->Review->del('transfer_voucher',$req['id']);

        return $this->service->response('00', "ok");        
    }

    public function audit(Request $request)
    {

        $result = $this->updateReview(
            'transfer_voucher',
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

}
