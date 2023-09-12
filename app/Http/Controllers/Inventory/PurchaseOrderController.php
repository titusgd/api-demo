<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Store;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\RequisitionItem;
use App\Models\RequisitionPurchaseOrder;
use App\Models\Image;
use App\Services\Service;
use App\Services\Inventory\InventoryService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Services\Inventory\PurchaseOrderService;

class PurchaseOrderController extends Controller
{
    private $Service;
    private $InventoryService;

    function __construct()
    {
        $this->Service = new Service();
        $this->InventoryService = new InventoryService();
    }

    public function list(Request $request)
    {
        $req = $request->all();

        // 預設筆數
        $count = (!empty($req['count'])) ? $req['count'] : 20;

        // 狀態字串轉數字
        $status = $this->Service->getStatusKey($req['status']);

        // 組成查詢陣列
        $where = [];
        if (isset($req['store']) && $req['store'] != 0) {
            array_push($where, ['stores_id', '=', $req['store']]);
        }
        if (!empty($req['code'])) {
            array_push($where, ['code', '=', $req['code']]);
        }
        if (is_array($req['date'])) {
            if (isset($req['date'][0])) {
                array_push($where, ['created_at', '>=', $req['date'][0] . " 00:00:00"]);
            }
            if (isset($req['date'][1])) {
                array_push($where, ['created_at', '<=', $req['date'][1] . " 23:59:59"]);
            }
        }

        // 組成狀態查詢陣列
        $statusWhere = (!empty($status)) ? ['status' => $status] : [];

        // 使用Eloquent 查詢並取得資料
        $results = PurchaseOrder::select(
            "id",
            "code as purchaseOrderNumber",
            DB::raw('(select store from stores where stores.id = purchase_orders.stores_id) as store'),
            DB::raw("ifnull(note,'') as note"),
            "delivery_fee as deliveryFee",
            DB::raw("left(created_at,10) as date"),
            // DB::raw("ifnull((
            //     select GROUP_CONCAT(url) from images 
            //     where type = 'PurchaseOrder' and fk_id = purchase_orders.id ),'') as image"),
            // DB::raw("ifnull((select url from images where fk_id = purchase_orders.id and type = 'PurchaseOrder'),'') as image"),
            'petty_cash_list_id as pettyCash',
            'signee_note'
        )
            ->where($where)
            ->whereExists(
                function ($query) use ($statusWhere) {
                    $query->select(DB::raw(1))
                        ->from('purchase_order_items')
                        ->whereRaw('purchase_order_items.purchase_order_id = purchase_orders.id')
                        ->where($statusWhere);
                }
            )
            ->orderBy('id', 'desc')
            ->paginate($count)
            ->toArray();
        // 製作page回傳格式
        $total      = $results['last_page'];    //總頁數
        $countTotal = $results['total'];        //總筆數
        $page       = $results['current_page'];  //當前頁次

        $pageinfo = [
            "total"      => $total,      // 總頁數
            "countTotal" => $countTotal, // 總筆數
            "page"       => $page,       // 頁次
        ];
        foreach ($results['data'] as $k => $v) {
            $signee_note = json_decode($v['signee_note'], true);
            // 取得細項資料
            $item_results = PurchaseOrderItem::select(
                "id",
                DB::raw('(select name from items where items.`id` = purchase_order_items.`items_id`) as name'),
                DB::raw('(select unit from items where items.`id` = purchase_order_items.`items_id`) as unit'),
                "qty",
                DB::raw("cast(price as unsigned) as price"),
                DB::raw("cast((qty*price) as unsigned) as total"),
                "status",
                "status_date as date",
                DB::raw("ifnull(note,'') as note"),
                'signee_id',
                'status as signee_status',
                DB::raw('(select name from users where users.`id` = purchase_order_items.`signee_id`)as signee_name'),
            )
                ->where('purchase_order_id', $v['id'])
                ->where($statusWhere)
                ->get()
                ->toArray();
            // 狀態數字轉字串
            $audit = false;

            foreach ($item_results as $k2 => $v2) {
                // 日期格式化
                $item_results[$k2]['date'] = $this->Service->dateFormat($v2['date']);
                // ----- 方式1 --------------------
                // 簽收初始化
                $item_results[$k2]['signee'] = [
                    'id' => null,
                    'name' => '',
                    'date' => [],
                    'image' => [],
                ];
                // 狀態為簽收時，則寫入簽收相關資訊
                if ($item_results[$k2]['status'] == '14') {
                    $item_results[$k2]['signee']['id'] = $v2['signee_id'];
                    $item_results[$k2]['signee']['name'] = $v2['signee_name'];
                    $item_results[$k2]['signee']['date'] = $v2['date'];
                    $image = Image::select('url')
                        ->where('type', '=', 'PurchaseOrder')
                        ->where('fk_id', '=', $v['id'])
                        ->get()->toArray();
                    foreach ($image as $key => $value) {
                        $item_results[$k2]['signee']['image'][$key] = $value['url'];
                    }
                }

                // 狀態變更成文字碼
                $item_results[$k2]['status'] = $this->Service->getStatusValue($v2['status']);
                ($item_results[$k2]['status'] != '14') && $audit = true;
                $item_results[$k2]['signNote'] = '';
                if (!empty($signee_note)) {
                    foreach ($signee_note as $key2 => $value2) {
                        if (in_array($v2['id'], $value2['item_id'])) {
                            $item_results[$k2]['signNote'] = $value2['note'];
                        }
                    }
                }
                // if ($item_results[$k2]['status'] != '14') {
                //     $audit = true;
                // }
                // 移除多於鍵
                unset(
                    $item_results[$k2]['signee_id'],
                    $item_results[$k2]['signee_name'],
                    $item_results[$k2]['signee_status']
                );
            }

            // 日期轉為陣列
            $results['data'][$k]['date'] = $this->Service->dateFormat($v['date']);
            $results['data'][$k]['audit'] = $audit;
            $results['data'][$k]['list'] = $item_results;

            // 如果null輸入空陣列
            $petty_cash = json_decode($results['data'][$k]['pettyCash'], true);
            $results['data'][$k]['pettyCash'] = (!empty($petty_cash)) ? $petty_cash : [];
            unset($results['data'][$k]['signee_note']);
        }


        return $this->Service->response_paginate("00", 'ok', $results['data'], $pageinfo);
    }

    public function add(Request $request)
    {

        $input = $request->all();
        $note = $input['note'];
        $store_id = $this->Service->getUserStoreId();

        // 檢查資料
        $vali = $this->InventoryService->validata($input['list']);
        if (is_array($vali)) {
            return $this->Service->response($vali[0], $vali[1]);
        }

        // 新增採購單資料
        $create = $this->InventoryService
        ->createPurchaseOrder($input['list'], $note, $store_id, $input['deliveryFee']);

        return $this->Service->response("00", "OK");
    }


    public function update(Request $request)
    {

        $input = $request->all();

        // 檢查資料
        $vali = $this->InventoryService->validata($input['list']);
        if (is_array($vali)) {
            return $this->Service->response($vali[0], $vali[1]);
        }

        // 更新採購單資料
        $create = $this->InventoryService->updatePurchaseOrder($input);

        return $this->Service->response("00", "OK");
    }

    public function del(Request $request)
    {
        return (new PurchaseOrderService($request))
            ->runDel()
            ->getResponse();
        
        // $req = $request->all();
        // // 查詢採購簽收狀態
        // $res = PurchaseOrderItem::where('purchase_order_id', $req['id'])
        //     ->where('status', '=', 14)
        //     ->get()
        //     ->toArray();
        // $requisition_item_ids = RequisitionPurchaseOrder::select('requisition_item_id')
        //     ->where("purchase_order_id", $req['id'])
        //     ->pluck('requisition_item_id');

        // // 只要有一筆狀態為"已簽收"，返回ERROR
        // if (count($res) != 0) {
        //     return $this->Service->response("05", "");
        // } else {

        //     // 刪除關聯
        //     $del = RequisitionPurchaseOrder::where("purchase_order_id", $req['id'])
        //         ->delete();

        //     // 刪除主表
        //     $res = PurchaseOrder::where('id', $req['id'])
        //         ->delete();

        //     // 刪除次表
        //     $res = PurchaseOrderItem::where('purchase_order_id', $req['id'])
        //         ->where('status', '=', 3)
        //         ->delete();

        //     // 變更請購單狀態，恢復成1
        //     $res = RequisitionItem::whereIn('id', $requisition_item_ids)->update(['status' => 1]);
        // }

        // return $this->Service->response("00", "ok");
    }
}
