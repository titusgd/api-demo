<?php

namespace App\Services\PettyCash;

use App\Models\PettyCash\PettyCashList;
use App\Models\PettyCash\PettyCashListItem;
use App\Models\Image;
use App\Models\firm;
use App\Models\Store;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\Service;
use App\Services\Files\ImageUploadService;
use App\Models\PurchaseOrderItem;
use App\Models\RequisitionItem;
use App\Models\PurchaseOrder;
use App\Models\Requisition;
use App\Models\RequisitionPurchaseOrder;
use Illuminate\Support\Arr;

class PettyCashListService extends Service
{
    private $request_array;

    // 錯誤訊息
    private $error_messages = [
        // -------------------------------- 分店編號 ----------------------------
        "store.required" => "01 store",
        "store.integer" => "01 store",
        "store.exists" => "02 store",
        //-------------------------------- 統一發票號碼 -------------------------
        "data.*.number.required" => "01 number",
        "data.*.number.string" => "01 number",
        "data.*.number.max" => "01 number",
        "data.*.number.unique" => "03 number",
        "data.*.number.regex" => "01 number",
        // ------------------------------- 發票日期 -----------------------------
        "data.*.date.required" => "01 date",
        "data.*.date.integer" => "01 date",
        "data.*.date.max" => "01 date",
        "data.*.date.regex" => "01 date",
        "data.*.date.date" => "01 date",
        // ------------------------------- 廠商 --------------------------------
        "data.*.firm.required" => "01 firm",
        "data.*.firm.string" => "01 firm",
        "data.*.firm.integer" => "01 firm",
        "data.*.firm.max" => "01 firm",
        "data.*.firm.numeric" => "01 firm",
        // ------------------------------- 圖片 --------------------------------
        "data.*.image.required" => '01 image',
        "data.*.image.present" => '01 image',
        // ------------------------------- 備註 --------------------------------
        "data.*.memo.string" => "01 memo",
        "data.*.memo.max" => "01 memo",
        // ------------------------------- 商品 --------------------------------
        "data.*.product.array" => "01 product",
        "data.*.product.required" => "01 product",
        // ------------------------------- 商品摘要 -----------------------------
        "data.*.product.*.summary.string" => "01 summary",
        "data.*.product.*.summary.required" => "01 summary",
        // ------------------------------- 商品數量 -----------------------------
        "data.*.product.*.qty.required" => "01 qty",
        "data.*.product.*.qty.integer" => "01 qty",
        // ------------------------------- 商品金額 -----------------------------
        "data.*.product.*.price.required" => "01 price",
        "data.*.product.*.price.numeric" => "01 price",
        "data.*.product.*.price.between" => "01 price",
        "purchaseOrderNumber.integer" => "01 purchaseOrderNumber",
        "purchaseOrderNumber.array" => "01 purchaseOrderNumber",
        "purchaseOrderNumber.*.integer" => "02 purchaseOrderNumber",
        "purchaseOrderNumber.*.exists" => "02 purchaseOrderNumber",

    ];
    private $detialVali = [

        "status" => "string",
        "store" => "required|integer",
        "count" => "required|integer",
        "page" => "required|integer",
    ];
    private $detail_error_message = [
        "date.start.date" => "01 start",
        "date.start.string" => "01 start",
        "date.end.date" => "01 end",
        "date.end.string" => "01 end",

        "status.string" => "01 status",

        "store.required" => "01 store",
        "store.integer" => "01 store",
        "store.exists" => "02 store",

        "count.required" => "01 count",
        "count.integer" => "01 count",

        "page.required" => "01 page",
        "page.integer" => "01 page",

        "price.required" => "01 price",
        "price.integer" => "01 price",
        "price.max" => "01 price",

        "proposal.integer" => "01 proposal"

    ];


    function __construct($req)
    {
        $this->request_array = Service::requireToArray($req);
    }

    public function validate()
    {
        for ($i = 0; $i < count($this->request_array['data']); $i++) {
            $this->request_array['data'][$i]['number'] = str_replace('-', '', $this->request_array['data'][$i]['number']);
        }

        $rules = collect([
            "store" => "required|integer|exists:stores,id",
            "data" => [
                "*" => [
                    "number" => "required|string|max:10|unique:petty_cash_lists,number",
                    "date" => "required|date",
                    "firm" => "required|string|numeric",
                    "image" => "present|array",
                    //"file"=>"mimes:jpg,png,pdf,doc",
                    "memo" => "string|max:100",
                    "product" => [
                        "*" => [
                            "id" => 'present|integer|exists:requisition_items',
                            "summary" => "required|string",
                            "qty" => "required|integer",
                            "price" => "required|numeric|between:0,99999999.99"
                        ]
                    ]
                ]
            ],
        ]);

        $validate = Validator::make($this->request_array, Arr::dot($rules), $this->error_messages);
        if ($this->checkValiDate($validate)) return $this->checkValiDate($validate);

        // 尋訪data內，所有的image
        foreach ($this->request_array['data'] as $key => $val) {
            if (!empty($val["image"])) {
                foreach ($val["image"] as $k => $v) {
                    $cci = $this->checkImage($v);
                    if ($cci) return $cci;
                    // if ($this->checkImage($val["image"])) return $this->checkImage($val["image"]);   
                }
            }
        }
        return true;
    }

    /** getRequestArray()
     *  取得轉換後的request
     */
    public function getRequestArray()
    {
        return $this->request_array;
    }

    /** checkValiDate()
     *  確認錯誤訊息，如果有錯誤訊息，則回傳錯誤訊息
     */
    public function checkValiDate($validate)
    {
        if ($validate->fails()) {
            list($code, $message) = explode(" ", $validate->errors()->first());
            return Service::response($code, $message);
        }
    }

    /** checkImageFile
     *  檢查是否有圖片， 並檢查圖片副檔名、檔案寬高
     */
    protected function checkImage($image)
    {
        $image_service = new ImageUploadService();
        if (!$image_service->checkImageExtension($image)) return Service::response("300", "image");
    }

    public function create()
    {
        $store_id = $this->request_array['store'];
        $data = $this->request_array['data'];
        $user_id = Auth::user()->id;
        $pcl_id = [];
        $pcl_id_arr = [];
        $petty_cash_list_ids = [];
        $requisition_item_id = collect();
        foreach ($data as $key => $val) {
            // 查詢統一編號 `firms`.gui_number 是否存在
            $firm = firm::where("gui_number", "=", $val['firm'])->first();
            // 如果不存在 新增一筆 `firm`資料
            $firm_id = (!$firm) ? $this->createFirm($val['firm']) : $firm->id;
            // 建立 零用金主表 資料
            $pcl = new PettyCashList;
            $pcl->number = (!empty($val['number'])) ? str_replace("-", '', $val['number']) : '00';
            $pcl->date = $val['date'];
            $pcl->firm_id = $firm_id;
            $pcl->memo = $val['note'];
            $pcl->store_id = $store_id;
            $pcl->user_id = $user_id;
            $pcl->save();


            if (empty($val['number'])) $pcl->number = str_pad($pcl->id, 10, '0', STR_PAD_LEFT);
            $pcl->save();
            $perry_list_id = $pcl->id;
            array_push($pcl_id_arr, $pcl->id);
            array_push($pcl_id, $pcl->id);
            array_push($petty_cash_list_ids, $perry_list_id);

            foreach ($val['product'] as $key2 => $val2) {
                (!empty($val2['id'])) && $requisition_item_id->push($val2['id']);
                $product_input = [
                    "summary" => $val2['summary'],
                    "qty" => (!empty($val2['qty'])) ? $val2['qty'] : 1,
                    "price" => $val2['price'],
                    "petty_cash_list_id" => $pcl->id,
                ];
                // 新增細項
                $pcli = new PettyCashListItem();
                foreach ($product_input as $key3 => $val3) {
                    $pcli->$key3 = $val3;
                }
                $pcli->save();
            }

            // 圖片上傳
            if (!empty($val['image'])) {
                foreach ($val['image'] as $k => $v) {
                    $image_source = $v;
                    // base64解碼
                    $image_service = new ImageUploadService();
                    $image_data = $image_source;
                    $image_service->addImage($image_data, "PettyCashList", $perry_list_id);
                    $image_id = $image_service->getId();
                    $image = Image::find($image_id);
                }
            }
        }
        // dd($petty_cash_list_ids);
        // 已經有請購單子像id $requisition_item_id
        $requisition_item_id = $requisition_item_id->toArray();
        $requisition_purchase_order = RequisitionPurchaseOrder::select('requisition_item_id', 'purchase_order_id', 'purchase_order_item_id')
            ->whereIn('requisition_item_id', $requisition_item_id)->get()->toArray();
        $purchase_order_item_id = array_column($requisition_purchase_order, 'purchase_order_item_id');
        $purchase_order_id = array_unique(array_column($requisition_purchase_order, 'purchase_order_id'));
        if (!empty($requisition_item_id)) {
            // 取得採購單-零用金明細欄位
            $purchase_order = PurchaseOrder::select('id', 'petty_cash_list_id')->find($purchase_order_id[0]);
            // 資料結合
            $temp_list = collect(json_decode($purchase_order['petty_cash_list_id'], true));
            $temp_list = $temp_list
                ->merge($petty_cash_list_ids)
                ->unique()
                ->toJson();
            // 儲存變更
            $purchase_order->petty_cash_list_id = $temp_list;
            $purchase_order->save();

            // 變更採購單狀態+零用金明細
            $purchase_order_item = PurchaseOrderItem::select('id', 'petty_cash_list_id')
                ->whereIn('id', $purchase_order_item_id)
                ->get()->toArray();
            $purchase_order_item = collect($purchase_order_item);
            $purchase_order_item->map(function ($item, $key) use ($petty_cash_list_ids) {
                $temp_list = collect(json_decode($item['petty_cash_list_id'], true));
                $temp_list = $temp_list
                    ->merge($petty_cash_list_ids)
                    ->unique()
                    ->toJson();
                // 更新資料表:狀態、零用金明細、簽收人
                PurchaseOrderItem::where('id', '=', $item['id'])
                    ->update([
                        'status' => 14,
                        'petty_cash_list_id' => $temp_list,
                        'signee_id' => auth()->user()->id

                    ]);
            });
            // 變更採購單狀態
            RequisitionItem::whereIn('id', $requisition_item_id)
                ->update(['status' => 4]);

            $images = Image::select('*')
                ->where('type', '=', 'PettyCashList')
                ->whereIn('fk_id', $pcl_id_arr)->get();

            foreach ($images as $key => $val) {
                // 請購單id
                $createImage = new Image();
                $createImage->image_name = $val->image_name;
                $createImage->file_name = $val->file_name;
                $createImage->path = $val->path;
                $createImage->url = $val->url;
                $createImage->user_id = $val->user_id;
                $createImage->extension = $val->extension;
                // $createImage->fk_id = $requisition_id[0]['id'];
                $createImage->fk_id = $purchase_order_id[0];
                $createImage->type = 'PurchaseOrder';
                $createImage->created_at = $val->created_at;
                $createImage->updated_at = $val->updated_at;
                $createImage->save();
            }
        }

        // if (!empty($purchase_order_item_id)) {
        //     // 查詢採購單子項目
        //     $purchase_order_items = PurchaseOrderItem::select(
        //         'id',
        //         'purchase_order_id',
        //         'petty_cash_list_id',
        //         DB::raw('(select requisition_item_id from requisition_purchase_orders where requisition_purchase_orders.`purchase_order_item_id`  = purchase_order_items.id) as requisition_item_id')
        //     )
        //         ->whereIn('id', $purchase_order_item_id)
        //         ->get()->toArray();
        //     dd($purchase_order_items);
        //     $purchase_order_id = $purchase_order_items[0]['purchase_order_id'];

        //     // 更新採購單 petty_cash_list_id
        //     // 1.取得舊資料
        //     $purchase_order = PurchaseOrder::select('petty_cash_list_id')->where('id', '=', $purchase_order_id)->get()->toArray();

        //     $purchase_order_petty = collect(json_decode($purchase_order[0]['petty_cash_list_id'], true));
        //     // 2.資料合併
        //     $purchase_order_petty = $purchase_order_petty
        //         ->merge($petty_cash_list_ids)
        //         ->unique()
        //         ->toJson();
        //     // 3.儲存變更
        //     $purchase_order = PurchaseOrder::where('id', '=', $purchase_order_id)
        //         ->update(['petty_cash_list_id' => $purchase_order_petty]);

        //     // 更新採購單子表 
        //     foreach ($purchase_order_items as $key => $item) {
        //         // 採購單子表 更新狀態+零用金列表
        //         // 建立集合、合併陣列、濾除重複，轉成json
        //         $temp_arr = collect(json_decode($item['petty_cash_list_id'], true));
        //         $temp_arr = $temp_arr->merge($petty_cash_list_ids)->unique()->toJson();
        //         // 更新採購單子表狀態
        //         PurchaseOrderItem::where('id', '=', $item['id'])->update([
        //             'status' => 14,     // 變更狀態
        //             'petty_cash_list_id' => $temp_arr, //零用金id
        //             'signee_id' => auth()->user()->id //簽收人
        //         ]);
        //         // 更新請購單子表狀態
        //         RequisitionItem::where('id', '=', $item['requisition_item_id'])
        //             ->update(['status' => 4]);
        //     }

        //     // // 採購單 狀態變更
        //     // $purchase_order_data = PurchaseOrder::select('id')->where('code', '=', $this->request_array['purchaseOrderNumber'])->first();
        //     // $purchase_id = $purchase_order_data->id;

        //     // $update_PurchaseOrderItem = PurchaseOrderItem::where('purchase_order_id', $purchase_id)
        //     //     ->update(['status' => 14]);

        //     // $update_RequisitionItem = RequisitionItem::wherein(
        //     //     'id',
        //     //     function ($query) use ($purchase_id) {
        //     //         $query->select('requisition_item_id')
        //     //             ->from('requisition_purchase_orders')
        //     //             ->whereRaw("`purchase_order_id`={$purchase_id}");
        //     //     }
        //     // )->update(['status' => 4]);

        //     // 圖片處理

        //     $images = Image::select('*')
        //         ->where('type', '=', 'PettyCashList')
        //         ->whereIn('fk_id', $pcl_id_arr)->get();
        //     dd($purchase_order_id);
        //     foreach ($images as $key => $val) {
        //         // 請購單id
        //         $createImage = new Image();
        //         $createImage->image_name = $val->image_name;
        //         $createImage->file_name = $val->file_name;
        //         $createImage->path = $val->path;
        //         $createImage->url = $val->url;
        //         $createImage->user_id = $val->user_id;
        //         $createImage->extension = $val->extension;
        //         $createImage->fk_id = $purchase_order_id;
        //         $createImage->type = 'PurchaseOrder';
        //         $createImage->created_at = $val->created_at;
        //         $createImage->updated_at = $val->updated_at;
        //         $createImage->save();
        //     }
        // }

        return Service::response("00", "ok");
    }

    public function validateDetail()
    {
        $rules = collect($this->detialVali)
            ->put('store', 'required|integer|exists:stores,id')
            ->put(
                "date",
                [
                    'start' => 'present|string|date',
                    'end' => 'present|string|date'
                ]
            )->put('id', ['*' => 'present|integer'])->toArray();

        $validate = Validator::make(
            $this->request_array,
            Arr::dot($rules),
            $this->detail_error_message
        );
        if ($this->checkValiDate($validate)) return $this->checkValiDate($validate);
    }


    public function searchList($store = false)
    {
        $req = $this->request_array;
        // ------------------------ 日期 ---------------------------------------
        // 起
        $start_date = (!empty($req["date"]["start"])) ? $req["date"]["start"] : "2020/1/1";
        // 迄
        $end_date = (!empty($req["date"]["end"])) ? $req["date"]["end"] : "2150/1/1";
        // ------------------------ 分店id -------------------------------------
        $store_id = empty(($req["store"])) ? '' : $req["store"];
        $user_groups_id = auth()->user()->user_groups_id;

        // 2.店長 3.經理 4.儲備店長
        ($user_groups_id != '0') && $store_id = auth()->user()->store_id;
        // if ($user_groups_id == '4' | $user_groups_id == '2' | $user_groups_id == '3') {
        //     $store_id = auth()->user()->store_id;
        // }
        // 每頁資料筆數
        $limit = (!empty($req["count"])) ? $req["count"] : 20;

        $petty_list = PettyCashList::select(
            "id",
            "date",
            "number",
            "firm_id",
            DB::raw("(select `firm` from firms where firms.id = petty_cash_lists.firm_id) as firm"),
            DB::raw("(select store from stores where stores.id = petty_cash_lists.store_id) as store_name"),
            "store_id",
            "user_id",
            "memo"
        )
            ->where("date", ">=", "{$start_date} 00:00:00")
            ->where("date", "<=", "{$end_date} 23:59:59");

        (!empty($store_id)) && $petty_list = $petty_list->where("store_id", "=", $store_id);
        (!empty($req['id'])) && $petty_list = $petty_list->whereIn('id', $req['id']);
        // ------------------------ 排序 ---------------------------------------
        $petty_list = $petty_list->orderBy('created_at', 'desc');
        // ------------------------ 分頁 ---------------------------------------
        $petty_list = $petty_list->paginate($limit);
        $current_page = $petty_list->lastPage();    //總頁數
        $total = $petty_list->total();              //總筆數
        $sn = $petty_list->currentPage();
        // ------------------------ 顯示資料 (data) -----------------------------
        $data = [];
        foreach ($petty_list as $key => $val) {
            $data[$key]["id"] = $val['id'];
            $data[$key]["date"] = Service::dateFormat($val['date']);
            $data[$key]["number"] = $val['number'];
            $data[$key]["firm"] = $val['firm'];

            $images = Image::select(DB::raw('GROUP_CONCAT(url) AS link'))
                ->where("type", "=", 'PettyCashList')
                ->where("fk_id", "=", $val['id'])
                ->get()
                ->toArray();
            $data[$key]["link"] = ($images) ? explode(",", $images[0]['link']) : [];

            $data[$key]["note"] = $val['memo'];

            if ($store === true) {
                $data[$key]["store"]['id'] = $val['store_id'];
                $data[$key]["store"]['name'] = $val['store_name'];
            }

            $data[$key]["products"] = [];
            $items = PettyCashListItem::select("id", "summary", 'qty', "price")
                ->where("petty_cash_list_id", "=", $val["id"])
                ->get()->toArray();
            for ($i = 0; $i < count($items); $i++) {
                // 字串數字 轉 數字
                $items[$i]['qty'] = floatval($items[$i]['qty']);
                $items[$i]['price'] = floatval($items[$i]['price']);
            }
            $data[$key]["products"] = $items;
        }
        $info = [];
        // ------------------------------- info --------------------------------
        $perrt_list = PettyCashList::select("id")
            ->where("date", ">=", "{$start_date} 00:00:00")
            ->where("date", "<=", "{$end_date} 23:59:59")
            ->where("store_id", "=", $store_id)->get()->toArray();

        $petty_id = array_column($perrt_list, "id");
        $price_total = PettyCashListItem::select("price")
            ->whereIn("petty_cash_list_id", $petty_id)
            ->get();

        $info = [
            "sum" => $price_total->sum("price"),
            // 加總 發票數量
            "count" => ($data) ? count($data) : 0
            // 加總 品項數量
            // "count" => ($price_total) ? count($price_total) : 0
        ];

        $data2 = [
            "info" => $info,
            "list" => $data
        ];

        $res = [
            "code" => "00",
            "msg" => "ok",
            "page" => [
                "total" => $current_page,          // 總頁數
                "countTotal" => $total,            // 總筆數
                "page" => $req["page"],            // 頁次
            ],
            "data" => $data2,
        ];
        $headers = array('Content-Type' => 'application/json; charset=utf-8');
        return response()->json($res, 200, $headers, JSON_UNESCAPED_UNICODE);
    }

    public function updateVali($req)
    {
        $vali_arr = [
            'id' => 'required|numeric|exists:petty_cash_lists,id',
            'product' => 'array',
        ];
        // 選填加入
        $vali_arr['note'] = ($req['note'] !== null & $req['note'] !== '') ? 'string' : '';
        // if ($req['note'] !== null & $req['note'] !== '') $vali_arr['note'] = 'string';

        if (!empty($req["product"])) {
            // $vali_arr['product.*.id'] = 'numeric|exists:petty_cash_list_items,id';
            (!empty($req[0]['summary'])) && $vali_arr['product.*.summary'] = 'string';
            (!empty($req[0]['qty'])) && $vali_arr['product.*.qty'] = 'integer';
            (!empty($req[0]['price'])) && $vali_arr['product.*.price'] = 'string|numeric|between:0,99999999.9999';
        }

        // 錯誤訊息
        $vali_msg = [
            "id.required" => "01 id",
            "id.numeric" => "01 id",
            "id.exists" => "03 id",
            'note.string' => '01 note',
            'product.array' => '01 array',
            'product.*.id.numeric' => '01 id',
            'product.*.id.exists' => '02 id',
            'product.*.summary.' => '01 summary',
            'product.*.price.string' => '01 price',
            'product.*.price.numeric' => '01 price',
            'product.*.price.between' => '01 price',
            'product.*.qty.integer' => '01 qty'
        ];

        $vali = Service::validatorAndResponse($req, $vali_arr, $vali_msg);
        if ($vali) return $vali;
        $summary_emsg = false;
        if (!empty($req['product'])) {
            foreach ($req['product'] as $val) {
                if (!empty($val['id'])) {
                    $petty_cash_list_item = PettyCashListItem::find($val['id']);
                    // 輸入之id查詢不到
                    if (empty($petty_cash_list_item)) return Service::response('01', 'id');
                    // 資料主表 id 與輸入之主表id 不一致
                    if ($petty_cash_list_item->petty_cash_list_id !== $req['id']) return Service::response('01', 'id');
                }
                if ($val['summary'] === null | $val['summary'] == '') {
                    return Service::response('01', 'summary');
                }
            }
        }
    }

    public function update($req)
    {
        // 檢查是否送空直，空或null則刪除所有資料，包含子表
        // product = []刪除主表資料+子表資料
        // product[*]['id'] =null 刪除子表資料

        if (empty($req['product'])) {
            // 刪除資料表相關資料
            $this->deleteData('petty_cash_lists', ['where' => [['id', '=', $req['id']]]]);
            $this->deleteData('petty_cash_list_items', ['where' => [['petty_cash_list_id', '=', $req['id']]]]);
            // 刪除images的資料及檔案
            $image = new ImageUploadService();
            $image->deleteImageFile($req['id'], 'PettyCashList');

            return Service::response("00", "ok");
        }
        // 刪除請求內沒有的資料
        $this->deleteData('petty_cash_list_items', [
            'where' => [['petty_cash_list_id', '=', $req['id']]],
            'whereNotIn' => [['id',  array_column($req['product'], 'id')]]
        ]);

        $petty_cash_list = PettyCashList::find($req['id']);
        $petty_cash_list->memo = $req['note'];

        $petty_cash_list->save();
        if (!empty($req['product'])) {
            foreach ($req['product'] as $val) {
                // id 存在 則修改，不存在新增。
                (!empty($val['id'])) ? $this->updatePettyCashListItem($val) : $this->addPettyCashListItem($req['id'], $val);
            }
        }
        return Service::response("00", "ok");
    }

    public function createFirm($gui_number)
    {
        $company = Service::getFrimInfo($gui_number, 2);
        $firm = new firm();
        $firm->user_id = auth()->user()->id;
        $firm->gui_number = $gui_number;

        $firm->firm = (empty($company)) ? "" : $company[0]['Company_Name'];
        $firm->address = (empty($company)) ? "" : $company[0]['Company_Location'];
        $firm->representative = (empty($company)) ? "" : $company[0]['Responsible_Name'];

        // if (empty($company)) {
        //     // 工商登記，查無資料，直接新增統編至`firm`
        //     $firm->firm = "";
        //     $firm->address = "";
        //     $firm->representative = "";
        // } else {
        //     // 有資料則將相關資料新增至 `firm`
        //     $firm->firm = $company[0]['Company_Name'];
        //     $firm->address = $company[0]['Company_Location'];
        //     $firm->representative = $company[0]['Responsible_Name'];
        // }

        $firm->phone = "";
        $firm->contact_name = "";
        $firm->contact_phone = "";
        $firm->save();
        return $firm->id;
    }

    public function addPettyCashListItem($id, $val)
    {
        $pcli = new PettyCashListItem();
        $pcli->petty_cash_list_id = $id;
        $pcli->summary = (!empty($val['summary'])) ?  $val['summary'] : '';
        (!empty($val['qty'])) && $pcli->qty = $val['qty'];
        ($val['price'] !== null) && $pcli->price = $val['price'];
        $pcli->save();
    }

    public function updatePettyCashListItem($val)
    {
        $petty_cash_list_item = PettyCashListItem::find($val['id']);
        (!empty($val['summary'])) && $petty_cash_list_item->summary = $val['summary'];
        (!empty($val['qty'])) && $petty_cash_list_item->qty = $val['qty'];
        ($val['price'] !== null) && $petty_cash_list_item->price = $val['price'];
        $petty_cash_list_item->save();
    }


    /** deleteData($table_name,$where_array)
     *  @brief 刪除資料
     *  @param string $table_name 資料表名稱
     *  @param array $where_array 查詢條件，目前可使用where、orWhere、whereIn、whereNotIn 例:['where'=>[['id','=','2'],['name','like','aa']...]]
     *  @return void
     */
    public function deleteData($table_name, $where_array)
    {
        $table = DB::table($table_name);
        // ->where('id','=','9')
        $addwhere = function ($condition, $where_type) use (&$table) {
            foreach ($condition as $val) {
                $table = $table->$where_type($val[0], $val[1], $val[2]);
            }
        };
        // ->whereIn('id,[1,2,3,4])
        $addwhereIn = function ($condition, $whereType) use (&$table) {
            foreach ($condition as $val) {
                $table = $table->$whereType($val[0], array_filter($val[1]));
            }
        };
        foreach ($where_array as $key => $val) {
            switch ($key) {
                case "where":
                    $addwhere($val, $key);
                    break;
                case "whereIn":
                    $addwhereIn($val, $key);
                    break;
                case "orWhere":
                    $addwhere($val, $key);
                    break;
                case "whereNotIn":
                    $addwhereIn($val, $key);
                    break;
            }
        }
        $table->delete();
    }
}
