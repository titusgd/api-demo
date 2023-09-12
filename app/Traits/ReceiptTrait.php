<?php
namespace App\Traits;

use App\Models\Accounting\Receipt;
use App\Models\Accounting\ReceiptItem;
use App\Models\Accounting\Order;
use App\Models\Accounting\OrderProduct;
use App\Traits\CustomResponseTrait;
use App\Services\Service;
use App\Traits\ReviewTrait;
use Illuminate\Support\Facades\DB;

trait ReceiptTrait{

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
    public function createReceipt($date)
    {
        $service = new Service();

        $user_id  = $service->getUserId();
        $store_id = $service->getUserStoreId();
        // $user_id = 2;
        // $store_id = 1;


        // 新增付款單-主表   
        $code = $service->getCode('M','receipts',$store_id);  // 取單號

        $create = Receipt::create([
            "stores_id"   => $store_id,
            "user_id"     => $user_id,
            "object_id"   => $store_id,
            "object_type" => 'store',
            "code"        => $code,
            "receive_date"=>$date,
            // "note"       => $req['note'],
        ]);
        if ( !$create ) {
            return $this->response('999', '');    
        }
        $insert_id = $create->id;

        // 新增收款單-貸方細項
        $create = $this->addReceiveData($date, $store_id, $insert_id);
        if ( !$create ) {
            return $this->response('999', '');    
        }

        // 新增收款單-借方細項
        $create = $this->addPaymentData($date, $store_id, $insert_id);
        if ( !$create ) {
            return $this->response('999', '');    
        }

        // 建立 審核人員清單
        $review = $this->createReview(
            rank: $this->getReviewRank('paymentVoucher', 2), 
            fk_id: $insert_id,
            type: 'receipt'
        );

        return $this->response('0', 'ok');  
    }

    private function getSubjectId(string $summary) {

        $product = json_decode(file_get_contents("../resources/json/product.json"), true);
       
        foreach($product as $k => $v) {
            $res = array_search(rtrim($summary), $product[$k]['data']);
            if ( $res ) return $product[$k]['subject_id'];
        }
        return 0;

    }

    private function addReceiveData(string $date, int $store_id, int $insert_id){

        $item = OrderProduct::select(
            'name as summary', 
            'price', 
            DB::raw("count(id) as qty")
        )
        ->whereRaw("`order_id` in (select `id` from `orders` where `date` = ?)", $date)
        ->whereRaw('exists(select `id` from `orders` where `id` = `order_products`.`order_id` and `store_id`= ?)',$store_id)
        ->groupBy('name','price')
        ->get()
        ->toArray();

        foreach ( $item as $k => $v ) {
            // 若金額為正數(包含0)，則指定為貸方，反之為借方
            $debit_credit = ( $v['price'] >= 0 ) ? 2 : 1;

            $item[$k]['receipt_id']            = $insert_id;
            $item[$k]['price']                 = abs($v['price']);
            $item[$k]['summary']               = rtrim($v['summary']);
            $item[$k]['debit_credit']          = $debit_credit;
            $item[$k]['accounting_subject_id'] = $this->getSubjectId($v['summary']);
            unset($item[$k]['id']);
        }

        $create = ReceiptItem::insert($item);  
        
        return $create;
    }

    private function addPaymentData(string $date, int $store_id, int $insert_id) {

        $item = Order::select(
            'payment as summary', 
            DB::raw("0+cast(sum(`total`) as char) as price"), 
            DB::raw("1 as qty"), 
            DB::raw("concat(count(id),'筆') as note")
        )
        ->where([
            ['date',     '=', $date],
            ['store_id', '=', $store_id]
        ])
        ->groupBy('payment')
        ->get()
        ->toArray();

        foreach ( $item as $k => $v ) {

            // 若金額為正數(包含0)，則指定為借方，反之為貸方
            $debit_credit = ( $v['price'] >= 0 ) ? 1 : 2;

            $item[$k]['receipt_id'] = $insert_id;
            $item[$k]['debit_credit'] = $debit_credit;
            $patterns=["(LINE Pay 線下付款支付模組)","(自定義支付模組)","(現金支付模組)","(街口支付模組)"];
            $item[$k]['summary'] = str_replace("()","",preg_replace($patterns, '', $v['summary']));
            switch ($item[$k]['summary']) {
                case "現金" :
                    $item[$k]['accounting_subject_id'] = 3;    //現金
                    break;
                case "LINE Pay" : 
                    $item[$k]['accounting_subject_id'] = 533;  //應收帳款-LINE PAY信用卡
                    break;
                case "合庫電子支付" :
                    $item[$k]['accounting_subject_id'] = 532;  //應收帳款-合庫信用卡
                    break;
                case "街口支付" :
                    $item[$k]['accounting_subject_id'] = 537;  //應收帳款-街口支付
                    break;
                case "台灣Pay" :
                    $item[$k]['accounting_subject_id'] = 536;  //應收帳款-合庫臺灣PAY信用卡
                    break;
                default :
                    $item[$k]['accounting_subject_id'] = 0;
                    break;
            }

        }

        $create = ReceiptItem::insert($item);    

        return $create;
    }
}