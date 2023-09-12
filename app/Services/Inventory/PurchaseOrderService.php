<?php
namespace App\Services\Inventory;

use App\Models\Item;
use App\Models\Store;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\RequisitionItem;
use App\Models\RequisitionPurchaseOrder;
use App\Models\Image;
use App\Services\Service;
// use App\Services\Inventory\InventoryService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\ResponseTrait;
class PurchaseOrderService extends Service
{
    use ResponseTrait;
    private $request;
    function __construct($request)
    {
        $this->request = $request;

    }
    public function runDel(){
        if(auth()->user()->user_groups_id != 1){
            $this->response = Service::response("10", "權限不足");
            return $this;
        }
        $req = $this->request->all();
        // 查詢採購簽收狀態
        $res = PurchaseOrderItem::where('purchase_order_id', $req['id'])
            ->where('status', '=', 14)
            ->get()
            ->toArray();
        $requisition_item_ids = RequisitionPurchaseOrder::select('requisition_item_id')
            ->where("purchase_order_id", $req['id'])
            ->pluck('requisition_item_id');

        // 只要有一筆狀態為"已簽收"，返回ERROR
        if (count($res) != 0) {
            $this->response = Service::response("05", "已簽收，無法刪除");
        } else {

            // 刪除關聯
            $del = RequisitionPurchaseOrder::where("purchase_order_id", $req['id'])
                ->delete();

            // 刪除主表
            $res = PurchaseOrder::where('id', $req['id'])
                ->delete();

            // 刪除次表
            $res = PurchaseOrderItem::where('purchase_order_id', $req['id'])
                ->where('status', '=', 3)
                ->delete();

            // 變更請購單狀態，恢復成1
            $res = RequisitionItem::whereIn('id', $requisition_item_ids)->update(['status' => 1]);
            $this->response = Service::response("00", "ok");
        }
        return $this;
    }
}