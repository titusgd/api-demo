<?php

namespace App\Services\Inventory;

use App\Services\Service;
// ----- models -----
use App\Models\Item;
use App\Models\ItemSetting;
use App\Models\ItemCollection;
use App\Models\RequisitionItem;
// ----- methods -------
use Illuminate\Support\Facades\DB;

class ItemService extends Service
{
    private $response;
    private $page_count = 20;
    private $page_info = [
        "total"      => 0, //總頁數
        "countTotal" => 0, // 總筆數
        "page"       => 0, // 頁次
    ];

    public function setPageCount($page_count)
    {
        $this->page_count = $page_count;
        return $this;
    }
    public function getPageCount()
    {
        return $this->page_count;
    }

    public function getResponse()
    {
        return $this->response;
    }
    public function setResponse($response)
    {
        $this->response = $response;
        return $this;
    }

    public function validAdd($request)
    {
        $rules = [
            'group'   => 'required|integer',
            'firm'    => 'required|integer',
            'name'    => 'required|string|max:100',
            'price'   => 'required|regex:/^\d*(\.\d{1,4})?$/',
            'dealer'  => 'regex:/^\d*(\.\d{1,2})?$/',
            'unit'    => 'required|string',
            'stock'   => 'required|regex:/^\d*(\.\d{1,1})?$/',
            'favorite' => 'required|boolean',
        ];
        $message = [
            'group.required'   => "01 group",
            'group.integer'    => "01 group",
            'firm.required'    => "01 firm",
            'firm.integer'     => "01 firm",
            'name.required'    => "01 name",
            'name.string'      => "01 name",
            'name.max'         => "07 name",
            'price.required'   => "01 price",
            'price.regex'      => "01 price",
            'dealer.regex'     => "01 dealer",
            'unit.required'    => "01 unit",
            'unit.string'      => "01 unit",
            'stock.required'   => "01 stock",
            'stock.regex'      => "01 stock",
            'favorite.required' => "01 favorite",
            'favorite.boolean' => "01 favorite",
        ];
        $this->response = Service::validatorAndResponse($request->toArray(), $rules, $message);
        return $this;
    }

    public function validUpdate($request)
    {
        $rules = [
            'id' => 'required|exists:items,id',
            'group' => 'required|integer',
            'firm'  => 'required|integer',
            'name'  => 'required|string|max:100',
            'price' => 'required|regex:/^\d*(\.\d{1,4})?$/',
            'dealer' => 'regex:/^\d*(\.\d{1,2})?$/',
            'unit'  => 'required|string',
            'stock' => 'required|regex:/^\d*(\.\d{1,1})?$/'
        ];
        $message = [
            'id.required' => '01 id',
            'id.exists' => '01 id',
            'group.required' => "01 group",
            'group.integer' => "01 group",
            'firm.required' => "01 firm",
            'firm.integer'  => "01 firm",
            'name.required' => "01 name",
            'name.string'   => "01 name",
            'name.max'      => "07 name",
            'price.required' => "01 price",
            'price.regex'   => "01 price",
            'dealer.regex'  => "01 dealer",
            'unit.required' => "01 unit",
            'unit.string'   => "01 unit",
            'stock.required' => "01 stock",
            'stock.integer' => "01 stock",
        ];
        $this->response = Service::validatorAndResponse($request->toArray(), $rules, $message);
        return $this;
    }

    public function createItem($request)
    {
        $store_id = Service::getUserStoreId();
        $user_id = Service::getUserId();
        // $store_id =  auth()->user()->store_id;
        // $user_id = auth()->user()->id;
        $request = $request->toArray();
        $request = self::arrayReplaceKey($request, "group", "item_groups_id");
        $request = self::arrayReplaceKey($request, "firm", "firms_id");
        $request = self::arrayReplaceKey($request, "use", "use_flag");
        $request = self::arrayReplaceKey($request, "dealer", "dealer_price");
        $request["user_id"]  = $user_id;
        $request["store_id"] = $store_id;

        // 寫入資料庫
        $create = Item::create($request);
        $insert_id = $create->id;

        // 寫入到分店設定檔
        $create = ItemSetting::create([
            "store_id"    => $store_id,
            "item_id"     => $insert_id,
            "safety_stock" => $request['stock'],
        ]);

        // 寫入到收藏庫
        if ($request["favorite"] == 1 || $request["favorite"] == True) {
            $create = ItemCollection::create([
                "user_id" => $user_id,
                "item_id" => $insert_id,
                "sort_by" => 1,
            ]);
        }
        $this->response = Service::response('00', 'ok');
        return $this;
    }
    
    // 更新項目
    public function updateItem($request)
    {
        $request = $request->toArray();
        $store_id = Service::getUserStoreId();
        $user_id = Service::getUserId();
        // $store_id = auth()->user()->store_id;
        // $user_id = auth()->user()->id;
        // 更新
        $Item = Item::find($request['id']);
        $Item->firms_id       = $request['firm'];
        $Item->item_groups_id = $request['group'];
        $Item->name           = $request['name'];
        $Item->price          = $request['price'];
        $Item->dealer_price   = $request['dealer'];
        $Item->unit           = $request['unit'];
        $Item->note           = $request['note'];
        $Item->use_flag       = $request['use'];
        // $Item->moq            = $req['minOrderQty'];
        // $Item->mpq            = $req['minPackageQty'];
        $Item->save();

        $update = ItemSetting::firstOrCreate(
            [
                'item_id' => $request['id'],
                'store_id' => $store_id
            ],
            [
                'store_id'    => $store_id,
                "item_id"     => $request['id'],
                "safety_stock" => $request['stock']
            ]
        );

        // 寫入到收藏庫
        if ($request["favorite"] == 1 || $request["favorite"] == True) {
            $update = ItemCollection::firstOrCreate(
                [
                    "user_id" => $user_id,
                    "item_id" => $request['id'],
                ],
                [
                    "user_id" => $user_id,
                    "item_id" => $request['id'],
                    "sort_by" => 1,
                ]
            );
        } else {
            ItemCollection::where([
                ['item_id', '=', $request['id']],
                ['user_id', '=', $user_id]
            ])
                ->delete();
        }
        $this->response = Service::response("00", "ok");
        return $this;
    }

    // 電商匯入
    public function import($request)
    {

        $req     = $request->all();
        $store_id = Service::getUserStoreId();
        $user_id = Service::getUserId();

        // 寫入資料庫
        foreach ($req['data'] as $k => $v) {
            // 檢查是否存在
            $Item = Item::where('code', $v['sku'])->first();
            if (!$Item) {
                $create = Item::create([
                    "item_groups_id" => $req['group'],
                    "firm_id"       => 2,
                    "name"          => $v['name'],
                    "price"         => $v['price'],
                    "code"          => $v['sku'],
                    "unit"          => '',
                    "stock"         => 0,
                    "note"          => '',
                    "use_flag"      => 1,
                    "dealer_price"  => 0,
                    "user_id"       => $user_id,
                    "store_id"      => $store_id
                ]);
                $insert_id = $create->id;

                // 寫入到分店設定檔
                $create = ItemSetting::create([
                    "store_id"    => $store_id,
                    "item_id"     => $insert_id,
                    "safety_stock" => 0,
                ]);
            }
        }

        $this->response = Service::response("00", "ok");
        return $this;
    }

    // 確認使用狀態
    public function checkItemUse($request)
    {
        $req     = $request->all();
        // 檢查品項是否有使用
        $chk = RequisitionItem::where('items_id', '=', $req['id'])->first();
        if ($chk) {
            $this->response =  Service::response("05", "");
            return true;
        } else {
            return false;
        }
    }

    // 刪除
    public function del($request)
    {
        $req = $request->all();
        Item::destroy($req['id']);
        $this->response = Service::response("00", "");
        return $this;
    }
    private function arrayReplaceKey($array, $search_key, $replace_key)
    {
        return parent::array_replace_key($array, $search_key, $replace_key);
    }
    // 列表
    public function list($request)
    {

        $req = $request->all();

        //預設筆數
        $count   = (!empty($req['count'])) ? $req['count'] : $this->getPageCount();
        $store_id = Service::getUserStoreId();
        $user_id = Service::getUserId();

        if ($req['id'] == '') {
            $this->response = Service::response("01", "id");
            return $this;
            // return Service::response("01", "id");
        }
        $where = collect();
        $where->push(['item_groups_id', '=', $req['id']]);
        if (isset($req['search']) && $req['search'] != '') {
            $where->push(['name', 'like', '%' . $req['search'] . '%']);
        }
        $where = $where->toArray();

        $results = Item::select(
            "items.id",
            "items.name",
            "items.store_id as store",
            "items.created_at as date",
            DB::raw('(select firm from firms where firms.`id` = items.`firms_id`) as firm'),
            "items.firms_id",
            DB::raw('0+cast(items.price as char) as price'),
            DB::raw('0+cast(items.dealer_price as char) as dealer'),
            "items.unit",
            DB::raw("ifnull((select 0+cast(safety_stock as char) from item_settings where item_id = items.id and store_id = {$store_id}),0) as stock"),
            "items.note",
            DB::raw('0+cast(items.moq as char) as moq'),
            DB::raw('0+cast(items.mpq as char) as mpq'),
            DB::raw("case when item_collections.item_id is not null then 1 else 0 end as favorite"),
            "items.use_flag as use"
        )
        ->leftJoin('item_collections', function ($join) use ($user_id) {
            $join->on('items.id', '=', 'item_collections.item_id')
                ->where('item_collections.user_id', '=', $user_id)
                ->where('item_collections.sort_by', '=', 1);
        })
        ->where($where)
        ->where(function ($results) use ($store_id) {
            $results->where('items.store_id', '=', $store_id);
            $results->orWhere('items.store_id', '=', 0);
        })
        ->orderByRaw("favorite DESC")
        ->orderBy('items.id')
        ->paginate($count)
        ->toArray();

        // // 使用Eloquent 查詢並取得資料
        // $results = Item::select(
        //     "id",
        //     "name",
        //     "store_id as store",
        //     "created_at as date",
        //     DB::raw('(select firm from firms where firms.`id` = items.`firms_id`) as firm'),
        //     "firms_id",
        //     DB::raw('0+cast(price as char) as price'),
        //     DB::raw('0+cast(dealer_price as char) as dealer'),
        //     "unit",
        //     DB::raw("ifnull((select 0+cast(safety_stock as char) from item_settings where item_id = items.id and store_id = {$store_id}),0) as stock"),
        //     "note",
        //     DB::raw('0+cast(moq as char) as moq'),
        //     DB::raw('0+cast(mpq as char) as mpq'),
        //     DB::raw("case when exists(select id from item_collections where item_id = items.id and user_id = {$user_id}) then 1 else 0 end as favorite"),
        //     "use_flag as use"
        // )
        //     ->where($where)
        //     ->where(function ($results) use ($store_id) {
        //         $results->where('store_id', '=', $store_id);
        //         $results->orwhere('store_id', '=', 0);
        //     })
        //     ->orderByRaw("case when exists(select id from item_collections where item_id = items.id and user_id = {$user_id}) then 0 else 1 end")
        //     ->paginate($count)
        //     ->toArray();

        $page_info = $this->setPageInfo($results)->getPageInfo();

        foreach ($results['data'] as $k => $v) {
            $results['data'][$k]['use']       = (bool)$v['use'];
            $results['data'][$k]['favorite']  = (bool)$v['favorite'];
            $results['data'][$k]['date']      = Service::dateFormat($v['date']);
            $results['data'][$k]['firm']      = [
                'id'   => $v['firms_id'],
                'name' => $v['firm']
            ];
            unset($results['data'][$k]['firms_id']);
        }
        $this->response = Service::response_paginate("00", "ok", $results['data'], $page_info);
        return $this;
        // return Service::response_paginate("00", "ok", $results['data'], $page_info);
    }
    // 頁數設定
    private function setPageInfo($data)
    {
        $this->page_info = [
            "total"      => $data['last_page'],     // 總頁數
            "countTotal" => $data['total'],         // 總筆數
            "page"       => $data['current_page'],  // 頁次
        ];
        return $this;
    }

    private function getPageInfo()
    {
        return $this->page_info;
    }
}
