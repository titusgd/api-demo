<?php
namespace App\Traits;

use App\Models\Accounting\Payment;
use App\Models\Accounting\PaymentItem;
use App\Traits\CustomResponseTrait;
use App\Services\Service;
use App\Traits\ReviewTrait;


trait PaymentTrait{

    use CustomResponseTrait;   
    use ReviewTrait;

    /*
    firm id ->廠商id, 
    item    ->細項資料 
        array[
            [
                subject_id, 會計科目id
                summary,    摘要
                qty,        數量
                price,      單價
                note        備註
            ]
        ]
    */
    public function create($firm_id, $item)
    {
        $service = new Service();
        $user_id  = $service->getUserId();
        $store_id = $service->getUserStoreId();
        // $user_id = 2;
        // $store_id = 1;

        // 新增付款單-主表   
        $code = $service->getCode('I','payments',$store_id);  // 取單號

        $create = Payment::create([
            "stores_id"  => $store_id,
            "user_id"    => $user_id,
            "object_id"  => $firm_id,
            "object_type"=> 'firm',
            "code"       => $code,
            // "note"       => $req['note'],
        ]);
        if ( !$create ) {
            return $this->response('999', '');    
        }

        $insert_id = $create->id;

        // 新增付款單-細項
        foreach ( $item as $k => $v ) {
            // 若金額為正數(包含0)，則指定為借方，反之為貸方
            $debit_credit = ( $v['price'] >= 0 ) ? 1 : 2;

            $item[$k]['payment_id'] = $insert_id;
            $item[$k]['price'] = abs($v['price']);
            $item[$k]['debit_credit'] = $debit_credit;
            $item[$k] = $this->service->array_replace_key($item[$k], "subject", "accounting_subject_id");
            unset($item[$k]['id']);
        }
        $create = PaymentItem::insert($item);    
        if ( !$create ) {
            return $this->response('999', '');    
        }

        // 建立 審核人員清單
        $review = $this->createReview(
            rank: $this->getReviewRank('paymentVoucher', 2), 
            fk_id: $insert_id,
            type: 'payment'
        );
    }
}