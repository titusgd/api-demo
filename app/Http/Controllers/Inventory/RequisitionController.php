<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Accounting\ReceiptItem;
use App\Models\Item;
use App\Models\Store;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\RequisitionPurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Image;
use App\Models\PurchaseOrder;
use App\Services\Service;
use App\Services\Inventory\InventoryService;
use App\Services\Files\ImageUploadService;
use App\Traits\NotifyTrait;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;







class RequisitionController extends Controller
{
    use NotifyTrait;

    public $notice_group_id = [3, 16, 17];
    public $notice_content = [
        'title' => '新增 請購單 通知',
        'content' => '新增請購單通知，請購單編號 :',
        'link' => '/inventory/requisitions'
    ];
    function __construct()
    {
        $this->InventoryService = new InventoryService();
        $this->Service = new Service();
    }

    public function list(Request $request)
    {

        $service = new Service();


        // 將請求轉成陣列
        // $req = $service->requireToArray($request);
        $req = $request->toArray();
        // 預設筆數
        $count = (!empty($req['count'])) ? $req['count'] : 20;

        // 狀態字串轉數字
        $status = $service->getStatusKey($req['status']);

        // 組成查詢陣列
        $where = [];
        if (isset($req['store']) && $req['store'] != 0) {
            array_push($where, ['stores_id', '=', $req['store']]);
        }
        if (isset($req['id']) && $req['id'] != 0) {
            array_push($where, ['id', '=', $req['id']]);
        }
        if (is_array($req['date'])) {
            if (isset($req['date'][0])) {
                array_push($where, ['requisitions.created_at', '>=', $req['date'][0] . " 00:00:00"]);
            }
            if (isset($req['date'][1])) {
                array_push($where, ['requisitions.created_at', '<=', $req['date'][1] . " 23:59:59"]);
            }
        }

        // 組成狀態查詢陣列
        $statusWhere = (!empty($status)) ? ['status' => $status] : [];
        // note
        // 使用Eloquent 查詢並取得資料
        $results = Requisition::select(
            "id",
            "code as requisitionsNumber",
            DB::raw('(select store from stores where stores.`id` = requisitions.`stores_id`) as store'),
            DB::raw('(select name from users where id = requisitions.user_id) as editor'),
            "requisitions.created_at as date",
            'note'
        )
            ->where($where)
            ->whereExists(
                function ($query) use ($statusWhere) {
                    $query->select(DB::raw(1))
                        ->from('requisition_items')
                        ->whereRaw('requisition_items.requisitions_id = requisitions.id')
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
            "total"     => $total,      // 總頁數
            "countTotal" => $countTotal, // 總筆數
            "page"      => $page,       // 頁次
        ];

        foreach ($results['data'] as $k => $v) {
            // requisition_items
            // 取得細項資料
            $item_results = RequisitionItem::select(
                "id",
                DB::raw('(select name from items where items.`id` = requisition_items.`items_id`) as name'),
                DB::raw('(select unit from items where items.`id` = requisition_items.`items_id`) as unit'),
                DB::raw('(select firm from firms where firms.`id` = (select firms_id from items where id = requisition_items.items_id)) as firm'),
                DB::raw('(select gui_number from firms where firms.`id` = (select firms_id from items where id = requisition_items.items_id)) as guiNumber'),
                DB::raw('0+cast(qty as char) as qty'),
                DB::raw("cast(price as unsigned) as price"),
                DB::raw("cast((qty*price) as unsigned) as total"),
                "status",
                DB::raw("ifnull((
                    select code from purchase_orders a, requisition_purchase_orders b 
                    where a.id = b.purchase_order_id
                    and b.requisition_item_id = requisition_items.id
                    limit 1
                ),'') as purchaseOrderNumber"),
                // 'note',
                // DB::raw('(select note from requisitions where requisitions.`id` = requisition_items.`requisitions_id`)as requisition_note'),
                // DB::raw("ifnull((
                //     select 
                //         concat(
                //             note, 
                //             '$$',
                //             (select ifnull(note,'') from purchase_order_items where purchase_order_id = a.id and items_id = requisition_items.items_id)
                //         )
                //     from purchase_orders a, requisition_purchase_orders b 
                //     where a.id = b.purchase_order_id
                //     and b.requisition_item_id = requisition_items.id
                //     limit 1
                // ),'') as note"),
                DB::raw("ifnull((
                    select GROUP_CONCAT(url) from images 
                    where type = 'PurchaseOrder'
                    and fk_id = (
                        select purchase_order_id from requisition_purchase_orders 
                        where requisition_item_id = requisition_items.id
                    )
                ),'') as image"),
                'note',
                // 簽收人員
                // DB::raw('(
                //     select name
                //     from users
                //     where users.id=(
                //     select signee_id
                //     from purchase_order_items
                //     where purchase_order_items.id = (
                //     select purchase_order_item_id from requisition_purchase_orders 
                //     where requisition_purchase_orders.requisition_item_id = requisition_items.id
                //     )))as signee')

                // DB::raw('(select name from users users.`id` = (
                //     select signee_id from purchase_order_items where purchase_order_items.`id` = 
                //     (
                //         select purchase_order_item_id from requisition_purchase_orders 
                //         where requisition_purchase_orders.requisition_item_id = requisition_items.id
                //     )
                // ))as editor')
                // 'requisitions_id'
            )->with(['requisition' => function ($query) {
                $query->select('id', 'note');
            }])
                ->where('requisitions_id', $v['id'])
                ->where($statusWhere)
                ->get()
                ->map(function ($item, $key) {
                    // $item['note'] = [
                    //     (!empty($item['requisition'][0]['note'])) ? $item['requisition'][0]['note'] : "",
                    //     (!empty($item['note'])) ? $item['note'] : ''
                    // ];
                    // $item['note'] =
                    // $temp = $item['image'];
                    unset($item['requisitions_id'], $item['requisition']);
                    // $item['image'] = $temp;
                    return $item;
                })
                ->toArray();
            $audit = false;
            // 狀態數字轉字串、判斷是否可審核
            foreach ($item_results as $k2 => $v2) {
                $item_results[$k2]['status'] = $service->getStatusValue($v2['status']);
                // $item_results[$k2]['note']   = ($v2['note'] != '')  ? explode('$$', $v2['note']) : [0 => "", 1 => ""];
                // if($item_results[$k2]['status']!='solved'){
                $item_results[$k2]['image']  = ($item_results[$k2]['status'] != 'solved' & $v2['image'] != '') ? explode(',', $v2['image']) : [];
                // }else{
                //     $item_results[$k2]['image']  =[];
                // }

                if ($item_results[$k2]['purchaseOrderNumber'] == '') {
                    $audit = true;
                }
            }

            // 日期轉為陣列
            $results['data'][$k]['audit'] = $audit;
            $results['data'][$k]['date']  = $service->dateFormat($v['date']);
            $results['data'][$k]['list']  = $item_results;
            $results['data'][$k]['note'] = $v['note'];
        }

        return $service->response_paginate("00", 'ok', $results['data'], $pageinfo);
    }

    // 新增請購單
    public function add(Request $request)
    {

        $req = $request->all();
        $rules = [
            '*.id'      => 'required|integer',
            '*.qty'     => 'required|regex:/^\d*(\.\d{1,1})?$/',
        ];

        // 檢查
        $validator = Validator::make($req['data'], $rules);

        // 檢測出現錯誤
        if ($validator->fails()) {
            // 取得第一筆錯誤
            $message = explode(" ", $validator->errors()->first());
            $message = explode(".", $message[1]);
            return $this->Service->response(
                '01',
                [
                    "index" => $message[0],
                    "key" => $message[1]
                ]
            );
        }

        $user_id  = $this->Service->getUserId();
        $store_id = $this->Service->getUserStoreId();
        $code = $this->Service->getCode('PR', 'requisitions', '');  // 取單號
        $insert_data = [
            "stores_id" => $store_id,
            "user_id"  => $user_id,
            "code"     => $code,
            "note"     => (!empty($req['note'])) ? $req['note'] : '',
        ];

        //  && $insert_data['note'] = ;

        // 寫入主表資料庫
        $requisition = Requisition::create($insert_data);
        $insert_id = $requisition->id;

        // 陣列調整
        foreach ($req['data'] as $k => $v) {
            if ($v['qty'] == 0) {
                unset($req['data'][$k]);
            } else {
                // // 查詢品項資料
                $item_data = Item::find($v['id']);
                // // 檢查最低定貨量
                // if ( $item_data->mpq > 1 ) {
                //     if ( ($v['qty']%$item_data->mpq) !=0 ) {
                //         return $this->Service->response(
                //             '402',
                //             [
                //                 "index"=>$k,
                //                 "key"=>'qty'
                //             ]
                //         );
                //     }
                // } else {
                //     if ( $v['qty'] < $item_data->moq ) {
                //         return $this->Service->response(
                //             '401',
                //             [
                //                 "index"=>$k,
                //                 "key"=>'qty'
                //             ]
                //         );
                //     }
                // }                
                $req['data'][$k]['price'] = $item_data->price;
                $req['data'][$k] = $this->Service->array_replace_key($req['data'][$k], "id", "items_id");
                $req['data'][$k]['requisitions_id'] = $insert_id;
                $req['data'][$k]['status_date'] = date("Y-m-d H:i:s");
                $req['data'][$k]['note'] = (!empty($req['data'][$k]['note'])) ? $req['data'][$k]['note'] : '';
                unset($req['data'][$k]['new']);
            }
        }
        // dd($req['data']);
        // 寫入明細資料庫
        RequisitionItem::insert($req['data']);

        // ----- 新增通知 -----
        $requisition_id = $requisition->id;
        $type_name = "requisition";
        $this->notice_content['link'] = env('APP_URL') . $this->notice_content['link'];

        $notice = self::createNotice(
            $this->notice_content['title'],
            $this->notice_content['content'] . $code,
            $this->notice_content['link']
        );

        $notice_user = self::createNoticeUser(
            $notice->id,
            $this->notice_group_id
        );

        $this->updateNoticeData(
            $notice->id,
            $type_name,
            $requisition_id,
            $this->notice_content['link']
        );
        // -------------------
        return $this->Service->response("00", "ok");
    }


    public function update(Request $request)
    {

        $service = new Service();

        // 將請求轉成陣列
        $req = $service->requireToArray($request);

        // 檢查
        $validator = Validator::make($req['data'], [
            '*.id'      => 'required|integer',
            '*.qty'     => 'required|regex:/^\d*(\.\d{1,1})?$/'
        ]);

        // 檢測出現錯誤
        if ($validator->fails()) {
            // 取得第一筆錯誤
            $message = explode(" ", $validator->errors()->first());
            $message = explode(".", $message[1]);
            return $service->response(
                '01',
                [
                    "index" => $message[0],
                    "key" => $message[1]
                ]
            );
        }
        if (!empty($req['note'])) {
            Requisition::where('id', '=', $req['id'])->update(['note' => $req['note']]);
        }

        // TODO 先刪除，後新增
        // 1. 取得原始資料。

        $requisition_olds = RequisitionItem::select('id')->where('requisitions_id', '=', $req['id'])->pluck('id')->toArray();
        $requisition_olds = collect($requisition_olds);
        $request_data = collect($req['data']);
        $add_data = collect([]);
        $update_ids = collect([]);
        
        // 取得新增資料
        $request_data->map(function ($item, $key) use (&$add_data, &$request_data, $req, $update_ids) {
            // 資料分群，有new=true則為新增資料。沒有new或是new=false則更新的資料
            $item['new'] = (!empty($item['new'])) ? $item['new'] : false;
            if ($item['new']) {
                // 新增
                $add_data->push([
                    'requisitions_id' => $req['id'], //請購單主單號
                    'auditor_id' => 0, // 審核者
                    'items_id' => $item['id'],
                    'qty' => $item['qty'],
                    'note' => (!empty($item['note'])) ? $item['note'] : '',
                    'price' => Item::select('price')->where('id', '=', $item['id'])->pluck('price')->first(),
                    'status_date' => date("Y-m-d H:i:s"),
                ]);

                $request_data->forget($key);
            } else {
                // 更新
                $requisition_item = RequisitionItem::find($item['id']);
                $requisition_item->qty = $item['qty'];
                $requisition_item->note = $item['note'];
                $requisition_item->save();

                $update_ids->push($item['id']);
            }
        });


        // 刪除資料-批次刪除
        $del_ids = $requisition_olds->diff($update_ids)->toArray();
        RequisitionItem::whereIn('id', $del_ids)->delete();

        // 新增資料-批次新增
        RequisitionItem::insert($add_data->toArray());

        return $service->response("00", "ok");
    }

    // 請購單審核 + 建立採購單
    public function audit(Request $request)
    {
        // 將請求轉成陣列
        $req = $request->all();
        $note = ($req['note'] != '') ? $req['note'] : '';

        // 檢查
        $validator = Validator::make($req['list'], [
            '*.id'    => 'required|integer',
            // '*.price' =>'required|integer',
            '*.price' => 'required|regex:/^\d*(\.\d{1,4})?$/',
            '*.qty'   => 'required|numeric|regex:/^\d*(\.\d{1,1})?$/',
            // '*.status'=>'required|string',
            // '*.date'  =>'date_format:Y-m-d',
        ]);

        // 檢測出現錯誤
        if ($validator->fails()) {
            // 取得第一筆錯誤
            $message = explode(" ", $validator->errors()->first());
            $message = explode(".", $message[1]);
            return $this->Service->response(
                '01',
                [
                    "index" => $message[0],
                    "key" => $message[1]
                ]
            );
        }

        // 取請購單store_id
        $get_store_id = Requisition::select('stores_id')->find($req['id']);
        $store_id = $get_store_id->stores_id;

        foreach ($req['list'] as $k => $v) {
            //審核更新狀態為 3:solved
            $arr = $req['list'][$k];
            $arr['status'] = 3;
            unset($arr['note']);

            // 更新
            RequisitionItem::where('id', $v['id'])
                ->update($arr);
        }

        // 新增採購單資料
        $create = $this->InventoryService->createPurchaseOrder($req['list'], $req['note'], $store_id, $req['deliveryFee']);

        return $this->Service->response("00", "ok", $create);
    }


    public function search(Request $request)
    {
        $service = new Service();

        // 將請求轉成陣列
        $req = $service->requireToArray($request);
        $store_id = $service->getUserStoreId();
        $user_id  = $this->Service->getUserId();

        $where = [];
        array_push($where, ['use_flag', '=', 1]);
        // array_push($where,['store_id', '=', $store_id]);
        if ($req['group']) {
            array_push($where, ['item_groups_id', '=', $req['group']]);
        }
        if ($req['firm']) {
            array_push($where, ['firms_id', '=', $req['firm']]);
        }
        if (isset($req['search']) && $req['search'] != '') {
            array_push($where, ['name', 'like', '%' . $req['search'] . '%']);
        }

        // 使用Eloquent 查詢並取得資料
        $results = Item::select(
            "id",
            "item_groups_id",
            "firms_id",
            "name",
            "unit",
            "note",
            DB::raw('(select firm from firms where firms.`id` = items.`firms_id`) as firm_name'),
            DB::raw('(select name from item_groups where item_groups.`id` = items.`item_groups_id`) as group_name'),
            "price",
            DB::raw('50 as qty')
        )
            ->where($where)
            ->where(function ($results) use ($store_id) {
                $results->where('store_id', '=', $store_id);
                $results->orwhere('store_id', '=', 0);
            })
            ->orderBy('item_groups_id')
            ->orderBy('firms_id')
            ->orderByRaw("case when exists(select id from item_collections where item_id = items.id and user_id = {$user_id}) then 0 else 1 end")
            ->get()
            ->toArray();

        // 重整陣列
        $json = [];
        $group  = "";
        $firm   = "";
        foreach ($results as $k => $v) {
            if ($k == 0) {
                $group = $v['item_groups_id'];
                $firm  = $v['firms_id'];
                $index1 = 0;
                $index2 = 0;
                $index3 = 0;
                $json[$index1]['group'] = $v['item_groups_id'];
                $json[$index1]['name']  = $v['group_name'];
                $json[$index1]['firm'][$index2]['id']   = $v['firms_id'];
                $json[$index1]['firm'][$index2]['name'] = $v['firm_name'];
                $json[$index1]['firm'][$index2]['list'][$index3]['id']    = $v['id'];
                $json[$index1]['firm'][$index2]['list'][$index3]['name']  = $v['name'];
                $json[$index1]['firm'][$index2]['list'][$index3]['unit']  = $v['unit'];
            }
            if ($group != $v['item_groups_id']) {
                $index1 += 1;
                $index2 = 0;
                $index3 = 0;
                $group = $v['item_groups_id'];
                $firm = $v['firms_id'];
                $json[$index1]['group'] = $v['item_groups_id'];
                $json[$index1]['name']  = $v['group_name'];
                $json[$index1]['firm'][$index2]['id']   = $v['firms_id'];
                $json[$index1]['firm'][$index2]['name'] = $v['firm_name'];
            }
            if ($firm != $v['firms_id']) {
                $index2 += 1;
                $index3 = 0;
                $firm = $v['firms_id'];
                $json[$index1]['firm'][$index2]['id'] = $v['firms_id'];
                $json[$index1]['firm'][$index2]['name'] = $v['firm_name'];
            }
            $json[$index1]['firm'][$index2]['list'][$index3]['id']    = $v['id'];
            $json[$index1]['firm'][$index2]['list'][$index3]['name']  = $v['name'];
            $json[$index1]['firm'][$index2]['list'][$index3]['price'] = $v['price'];
            $json[$index1]['firm'][$index2]['list'][$index3]['qty']   = $v['qty'];
            $json[$index1]['firm'][$index2]['list'][$index3]['note']  = $v['note'];
            $json[$index1]['firm'][$index2]['list'][$index3]['unit']  = $v['unit'];
            $index3 += 1;
        }

        return $service->response_paginate("00", 'ok', $json);
    }

    public function del(Request $request)
    {
        $service = new Service();

        // 將請求轉成陣列
        $req = $service->requireToArray($request);

        $res = RequisitionItem::where('requisitions_id', $req['id'])
            ->where('status', '<>', 1)
            ->get()
            ->toArray();

        // 只要有一筆狀態不為"未處理"，返回ERROR
        if ($res) {
            return $service->response("05", "");
        } else {

            // 刪除主表
            $res = Requisition::where('id', $req['id'])
                ->delete();
            // 刪除次表
            $res = RequisitionItem::where('requisitions_id', $req['id'])
                ->where('status', '=', 1)
                ->delete();
        }

        return $service->response("00", "ok");
    }

    // 簽收
    public function sign(Request $request)
    {
        // 將請求轉成陣列
        $req = $request->all();
        // $id  = $req['id'];

        // 檢查
        // 輸入條件、規則
        $rules = [
            'id' => 'required|array',
            'id.*' => 'required|integer|exists:requisition_items,id'
        ];

        (!empty($req['note'])) && $rules['note'] = 'present|string';
        (!empty($request->image)) && $rules['image.*'] = 'string';

        // 錯誤訊息、代碼
        $error_message  = Arr::dot([
            'id' => [
                '*' => [
                    'exists' => '02 id',
                    'integer' => '01 id',
                    'required' => '01 id',
                ],
                'array' => '01 id'
            ],
            'image.*' => [
                'string' => '01 image'
            ],
            'note' => [
                'string'
            ]
        ]);

        $validator = Validator::make(
            $req,
            $rules,
            $error_message
        );

        // 檢測出現錯誤
        if ($validator->fails()) {
            // 取得第一筆錯誤
            $message = explode(" ", $validator->errors()->first());
            return $this->Service->response($message[0], $message[1]);
        }
        $purchase = collect([
            'id' => null,
            'item_id' => collect()
        ]);
        $requisition_item = function ($id) use ($req, &$purchase) {
            // 取採購單資料
            // $code = RequisitionPurchaseOrder::select('code', 'purchase_orders.id')
            //     ->join('purchase_orders', 'purchase_orders.id', '=', 'requisition_purchase_orders.purchase_order_id')
            //     ->where('requisition_item_id', '=', $id)
            //     ->get()
            //     ->toArray();
            $code = RequisitionPurchaseOrder::select(
                DB::raw(
                    '(
                        select code 
                        from purchase_orders 
                        where purchase_orders.id = requisition_purchase_orders.purchase_order_id
                    ) as code'
                ),
                'purchase_order_id as id',
                'purchase_order_item_id as item_id'
            )
                ->where('requisition_item_id', '=', $id)
                ->get()
                ->toArray();
            $purchase->put('id', $code[0]['id']);
            $purchase['item_id']->push($code[0]['item_id']);

            $purchase_id = $code[0]['id'];
            // 更新採購單狀態為 14:已簽收
            $update = PurchaseOrderItem::where('id', '=', $code[0]['item_id'])
                ->update([
                    'status' => 14,
                    'signee_id' => auth()->user()->id
                ]);

            // 更新請購單狀態為 4:結案
            $update = RequisitionItem::where('id', '=', $id)
                ->update(['status' => 4]);

            // 取得圖片
            if ($req['image']) {
                if (!is_array($req['image'])) {
                    $req['image'] = [$req['image']];
                }
                foreach ($req['image'] as $k => $v) {
                    $image_source = $v;
                    // base64解碼
                    $image_service = new ImageUploadService();
                    $image_data = $image_source;
                    $image_service->addImage($image_data, 'PurchaseOrder');
                    $image_id = $image_service->getId();
                    $image = Image::find($image_id);
                    $image->fk_id = $purchase_id;
                    $image->save();
                }
            }
        };
        foreach ($request->id as $key => $val) {
            $requisition_item($val);
        }
        // 簽收備註
        if (!empty($req['note'])) { // 如果有才做
            $purchase_order = PurchaseOrder::select('id', 'signee_note')->find($purchase['id']);
            $signee_json = collect((!empty($purchase_order->signee_note)) ? json_decode($purchase_order->signee_note) : []);
            $signee_json->push([
                'item_id' => $purchase['item_id']->toArray(),
                'note' => $req['note']
            ]);

            $purchase_order->signee_note = (!empty($signee_json->toJson())) ? $signee_json->toJson() : '[]';
            $purchase_order->save();
        }
        return $this->Service->response("00", "ok");
    }

    public function reject(Request $request)
    {
        // 將請求轉成陣列
        $req = $request->all();
        $id  = $req['id'];

        // 檢查
        $validator = Validator::make($req, [
            'id'    => 'required|integer',
        ], [
            'id.required'    => "01 id",
            'id.integer'     => "01 id",
        ]);

        // 檢測出現錯誤
        if ($validator->fails()) {
            // 取得第一筆錯誤
            $message = explode(" ", $validator->errors()->first());
            return $this->Service->response($message[0], $message[1]);
        }

        // 更新請購單狀態為 12:未通過
        $update = RequisitionItem::where('requisitions_id', $id)
            ->update(['status' => 12]);

        return $this->Service->response("00", "ok");
    }
}
