<?php
namespace App\Services\Inventory;

use App\Services\Service;
use App\Models\Item;
use App\Models\RequisitionItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\RequisitionPurchaseOrder;
use Illuminate\Support\Facades\Validator;


class InventoryService extends Service {    

    public function validata($req){

        $validator = Validator::make($req,[
            '*.id'      =>'required|integer',
            // '*.price'   =>'required|integer',
            // '*.qty'     =>'required|integer',
            // '*.status'  =>'required|string',
        ]);
    
        // 檢測出現錯誤
        if($validator->fails()){
            // 取得第一筆錯誤
            $message = explode(" ",$validator->errors()->first());
            $message = explode(".",$message[1]);
            return [
                0 =>'01',
                1 =>[
                    "index"=>$message[0],
                    "key"=>$message[1]
                ]
            ];
        }   

    }

    public function createPurchaseOrder($req, $note, $store_id, $deliveryFee=0){

        $code     = $this->getCode('PO','purchase_orders', $store_id);  // 取單號
        $user_id  = $this->getUserId();                                 // 取user id
        // $store_id = $this->getUserStoreId();                         // 取store id
        // $user_id = 1;
        // $store_id = 1;

        // requisitions 請購單|requisition_items 請購單項目|requisition_purchase_orders 請購單關聯
        // purchase_orders 採購單|purchase_order_items 採購單項目

        // 寫入主表資料庫 - 建立採購單
        $PurchaseOrder = PurchaseOrder::create([
            "stores_id"   =>$store_id,
            "user_id"     =>$user_id,
            "code"        =>$code,
            "note"        =>$note,
            "delivery_fee"=>$deliveryFee,
            'petty_cash_list_id'=>'[]',
            'signee_note' =>'[]'
        ]);
        $insert_id = $PurchaseOrder->id;
        

        $return_array = [
            "id"=>$insert_id,
            "code"=>$code
        ];

        // 陣列調整
        foreach ( $req as $k => $v ) {            
            $get_id = RequisitionItem::find($v['id']);

            // 寫入明細資料庫 - 建立採購單細項
            $PurchaseOrderItem = PurchaseOrderItem::create(
                [
                    "purchase_order_id" =>$insert_id,
                    "items_id"          =>$get_id->items_id,
                    "price"             =>$v['price'],
                    "qty"               =>$v['qty'],
                    "note"              =>$v['note'],   // 採購單備註
                    "status"            =>3,
                    "status_date"       =>date ("Y-m-d H:i:s"),
                    'petty_cash_list_id'=>'[]'
                ]
            );     
            // $insert_item_id = $PurchaseOrderItem->id;  
            // dd($PurchaseOrderItem->toArray());
            // 寫入關聯資料      
            RequisitionPurchaseOrder::create([
                "requisition_item_id"=>$v['id'],
                "purchase_order_id"  =>$insert_id,
                'purchase_order_item_id' => $PurchaseOrderItem->id
            ]);
        }
        
        return $return_array;

    }  

    public function updatePurchaseOrder($req){

        $id = $req['id'];
        $item_id = array_column($req['list'], "id");

        // 刪除關聯
        // $del=RequisitionPurchaseOrder::whereIn("purchase_order_id",
        //     function($query) use($item_id, $id) { 
        //         $item_id = implode(",",$item_id);
        //         $query->select('purchase_order_items.id')
        //             ->from('purchase_orders')
        //             ->join('purchase_order_items','purchase_orders.id','=','purchase_order_items.purchase_order_id')
        //             ->whereRaw("`purchase_orders`.`id`={$id}")
        //             ->whereRaw('`purchase_order_items`.`id` not in (' . $item_id . ')');
        //     }
        // )
        // ->delete();

        // 刪除細項
        // PurchaseOrderItem::where('purchase_order_id',$req['id'])
        // ->whereNotIn('id',$item_id)
        // ->delete();        
        

        // 更新主表
        $purchase_order = PurchaseOrder::find($id);
        if ( $req['deliveryFee'] != '' ) {
            $purchase_order->delivery_fee = $req['deliveryFee'];
        }
        $purchase_order->note = $req['note'];
        $purchase_order->save();
        
        // 更新細項
        foreach( $req['list'] as $k => $v ) {

            // 檢查狀態，若狀態與資料庫的不符，則更新狀態時間
            // $get_status = PurchaseOrderItem::find($v['id']);
            // if ( $get_status->status != $this->getStatusKey($v['status']) ) {
            //     $req['list'][$k]['status_date'] = date ("Y-m-d H:i:s");
            // }

            // 更新
            PurchaseOrderItem::where('id',$v['id'])
            ->update($req['list'][$k]);
        }

    }
 
}