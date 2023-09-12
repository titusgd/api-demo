<?php

namespace App\Services\Accounting;

use App\Services\Service;
use Illuminate\Support\Facades\DB;
use App\Models\Accounting\PaymentItem;
use App\Models\Accounting\TransferVoucher;
use App\Models\Accounting\TransferVoucherItem;
use App\Models\Administration\Review;
use App\Traits\ReviewTrait;
use Exception;


class PayableService extends Service
{

    use ReviewTrait;

    function __construct()
    {
        $this->Transfer     = new TransferVoucher();
        $this->TransferItem = new TransferVoucherItem();
        $this->Review       = new Review();
    }

    // 清單
    public function getlist($req)
    {      
        try {
            // DB::enableQueryLog();
            $req = json_decode($req['data']);

            // 組成查詢陣列
            $where = [];
            // if ( isset($req['store']) && $req['store'] != 0 ) {
            //     array_push($where,['object_id', '=', $req['store']]);
            //     array_push($where,['object_type', '=', 'store']);
            // }
            if ( isset($req->subject) && $req->subject != 0 ) {
                array_push($where,['accounting_subject_id', '=', $req->subject]);
            } else {
                return Service::response('01', 'subject');
            }
            if ( isset($req->date->start) ) {
                    array_push($where,['payments.created_at', '>=', $req->date->start . " 00:00:00"]);
            }
            if ( isset($req->date->end) ) {
                    array_push($where,['payments.created_at', '<=', $req->date->end . " 23:59:59"]);
            }

            // 查詢資料
            $res = PaymentItem::select(
                "payments.code",
                "payment_items.id",
                "payment_items.payment_id",
                "summary",
                "pay_date as payDate",
                DB::raw("(select code from transfer_vouchers where id = payment_items.transfer_voucher_id) as transferVoucher"),
                // DB::raw('0+cast(qty as char) as qty'),
                // DB::raw('0+cast(price as char) as price'),
                DB::raw("0+cast((qty*price) as char) as price"),
                "payment_items.note",
            )
            ->join('payments','payments.id','payment_items.payment_id')
            ->where($where)
            // ->where(function ($query)  {
            //         $query->where("accounting_subject_id",365);                
            //         $query->orwhere(DB::raw("(select subject_id from accounting_subjects where id = accounting_subject_id)"),"=",365);
            // })
            ->with([
                'subject',
                'status'
            ])
            ->orderBy('code')
            ->paginate($req->count)                                                                                                                                                                                        
            ->toArray();

            // dd(DB::getQueryLog());

            // 製作page回傳格式
            $pageinfo = [
                "total"     =>$res['last_page'],     // 總頁數
                "countTotal"=>$res['total'],         // 總筆數
                "page"      =>$res['current_page'],  // 頁次
            ];        

            return Service::response_paginate("00",'ok',$res['data'],$pageinfo);

        } catch (Exception $e) {
            $message  = 'Exception Message: '   . $e->getMessage();
            $message .= '<br>Exception Code: '  . $e->getCode(); 
            $message .= '<br>Exception String: '. $e->__toString(); 
            
            return Service::response('999', '', $message);
        }    

    }

    // 新增
    public function create($req)
    {

        try {

            foreach($req['list'] as $k=>$v) {
                $req['list'][$k]['type'] = 1;
            }

            $user_id =Service::getUserId();
            $store_id=Service::getUserStoreId();    

            $insert_data = PaymentItem::select(
                "accounting_subject_id as subject",
                "debit_credit as type",
                "summary",
                "price",
                "note"
            )
            ->whereIn('id',$req['select'])
            ->where('transfer_voucher_id',null)
            ->get()
            ->toArray();

            if ( !$insert_data ) {
                return Service::response('02', '');
            }

            // 新增主表資料
            $code = Service::getCode('Z','transfer_vouchers',$store_id);  // 取單號
            $insert_id = $this->Transfer->createData($store_id, $user_id, $code, ['store'=>0]);

            // 新增細項資料
            $this->TransferItem->createData($insert_id, $insert_data);
            $this->TransferItem->createData($insert_id, $req['list']);

            // 更新id回付款單，代表已銷帳
            PaymentItem::whereIn('id',$req['select'])
            ->update(
                [
                    "transfer_voucher_id" => $insert_id,
                ]
            );


            // 建立 審核人員清單
            $review = $this->createReview(
                rank: $this->getReviewRank('paymentVoucher', 2), 
                fk_id: $insert_id,
                type: 'transfer_voucher'
            );

            return Service::response('00', 'OK', $insert_id);

        } catch (Exception $e) {
            $message  = 'Exception Message: '   . $e->getMessage();
            $message .= '<br>Exception Code: '  . $e->getCode(); 
            $message .= '<br>Exception String: '. $e->__toString(); 
            
            return Service::response('999', '', $message);
        }    

    }

}