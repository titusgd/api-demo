<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemSetting;
use App\Models\ItemCollection;
use App\Models\RequisitionItem;
use App\Services\Service;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Constraint\IsTrue;
use Symfony\Component\HttpFoundation\Response;
use App\Services\Inventory\ItemService;
class ItemController extends Controller
{
    // ---------- old ----------
    // function __construct()
    // {
    //     $this->Service = new Service();
    // }

    public function list(Request $request)
    {
        return (new ItemService())->list($request)->getResponse();
        // //---------- old ----------
        // $req = $request->all();

        // //預設筆數
        // $count   = (!empty($req['count'])) ? $req['count'] : 20;
        // $store_id= $this->Service->getUserStoreId();
        // $user_id = $this->Service->getUserId();
        // // $store_id = 0;
        // if ( $req['id'] == '' ) {
        //     return $this->Service->response("01","id");
        // }

        // $where = array();
        // // array_push($where,['store_id', '=', $store_id]);
        // array_push($where,['item_groups_id', '=', $req['id']]);
        // if ( isset($req['search']) && $req['search'] != '' ) {
        //     array_push($where,['name', 'like', '%' .$req['search'].'%']);
        // }

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
        // ->where($where)
        // ->where(function ($results) use ($store_id) {
        //     $results->where('store_id', '=', $store_id);
        //     $results->orwhere('store_id', '=', 0);
        // })
        // ->orderByRaw("case when exists(select id from item_collections where item_id = items.id and user_id = {$user_id}) then 0 else 1 end")
        // ->paginate($count)
        // ->toArray();
  
        // // 製作page回傳格式
        // $total      =$results['last_page'];    //總頁數
        // $countTotal =$results['total'];        //總筆數
        // $page       =$results['current_page'];  //當前頁次

        // $pageinfo = [
        //     "total"     =>$total,      // 總頁數
        //     "countTotal"=>$countTotal, // 總筆數
        //     "page"      =>$page,       // 頁次
        // ];
        
        // foreach ( $results['data'] as $k => $v ) {
        //     $results['data'][$k]['use']       = (boolean)$v['use'];
        //     $results['data'][$k]['favorite']  = (boolean)$v['favorite'];
        //     $results['data'][$k]['date']      = $this->Service->dateFormat($v['date']);
        //     $results['data'][$k]['firm']      = [
        //         'id'   => $v['firms_id'],
        //         'name' => $v['firm']
        //     ];
        //     unset($results['data'][$k]['firms_id']);
        // }

        // return $this->Service->response_paginate("00","ok",$results['data'],$pageinfo);
    }

    public function add(Request $request)
    {
        $service = (new ItemService())->validAdd($request);
        if(!empty($service->getResponse())) return $service->getResponse();
        return $service->createItem($request)->getResponse();
        // ---------- old ----------
        // $req     = $request->all();
        // $store_id= $this->Service->getUserStoreId();
        // $user_id = $this->Service->getUserId();
        // // $store_id= 1;
        // // $user_id = 1;

        // // 檢查
        // $validator = Validator::make($req,[
        //     'group'   => 'required|integer',
        //     'firm'    => 'required|integer',
        //     'name'    => 'required|string|max:100',
        //     'price'   => 'required|regex:/^\d*(\.\d{1,4})?$/',
        //     'dealer'  => 'regex:/^\d*(\.\d{1,2})?$/',
        //     'unit'    => 'required|string',
        //     'stock'   => 'required|regex:/^\d*(\.\d{1,1})?$/',
        //     'favorite'=> 'required|boolean',
        // ],[
        //     'group.required'   =>"01 group",
        //     'group.integer'    =>"01 group",
        //     'firm.required'    =>"01 firm",
        //     'firm.integer'     =>"01 firm",
        //     'name.required'    =>"01 name",
        //     'name.string'      =>"01 name",
        //     'name.max'         =>"07 name",
        //     'price.required'   =>"01 price",
        //     'price.regex'      =>"01 price",
        //     'dealer.regex'     =>"01 dealer",
        //     'unit.required'    =>"01 unit",
        //     'unit.string'      =>"01 unit",
        //     'stock.required'   =>"01 stock",
        //     'stock.regex'      =>"01 stock",
        //     'favorite.required'=>"01 favorite",
        //     'favorite.boolean' =>"01 favorite",
        // ]);
    
        // // 檢測出現錯誤
        // if($validator->fails()){
        //     // 取得第一筆錯誤
        //     $message = explode(" ",$validator->errors()->first());
        //     return $this->Service->response($message[0],$message[1]);
        // }

        // // 陣列名稱調整
        // $req = $this->Service->array_replace_key($req, "group"        , "item_groups_id");
        // $req = $this->Service->array_replace_key($req, "firm"         , "firms_id");
        // $req = $this->Service->array_replace_key($req, "use"          , "use_flag");
        // $req = $this->Service->array_replace_key($req, "dealer"       , "dealer_price");
        // // $req = $this->Service->array_replace_key($req, "minOrderQty"  , "moq");
        // // $req = $this->Service->array_replace_key($req, "minPackageQty", "mpq");
        // $req["user_id"]  = $user_id;
        // $req["store_id"] = $store_id;

        // // 寫入資料庫
        // $create = Item::create($req);
        // $insert_id = $create->id;

        // // 寫入到分店設定檔
        // $create = ItemSetting::create([
        //     "store_id"    => $store_id,
        //     "item_id"     => $insert_id,
        //     "safety_stock"=> $req['stock'],
        // ]);

        // // 寫入到收藏庫
        // if (  $req["favorite"] == 1 || $req["favorite"] == True ) {
        //     $create = ItemCollection::create([
        //         "user_id"=>$user_id,
        //         "item_id"=> $insert_id,
        //         "sort_by"=> 1,
        //     ]);
        // }

        // return $this->Service->response("00","ok");  
    }


    public function update(Request $request)
    {
        $service = (new ItemService())->validUpdate($request);
        if(!empty($service->getResponse())) return $service->getResponse();
        return $service->updateItem($request)->getResponse();
        // dd();
        // ---------- old ----------
        // // 將請求轉成陣列
        // $req     = $request->all();
        // $store_id= $this->Service->getUserStoreId();
        // $user_id = $this->Service->getUserId();

        // // 檢查
        // $validator = Validator::make($req,[
        //     'group' => 'required|integer',
        //     'firm'  => 'required|integer',
        //     'name'  => 'required|string|max:100',
        //     'price' => 'required|regex:/^\d*(\.\d{1,4})?$/',
        //     'dealer'=> 'regex:/^\d*(\.\d{1,2})?$/',
        //     'unit'  => 'required|string',
        //     'stock' => 'required|regex:/^\d*(\.\d{1,1})?$/'
        // ],[
        //     'group.required'=>"01 group",
        //     'group.integer' =>"01 group",
        //     'firm.required' =>"01 firm",
        //     'firm.integer'  =>"01 firm",
        //     'name.required' =>"01 name",
        //     'name.string'   =>"01 name",
        //     'name.max'      =>"07 name",
        //     'price.required'=>"01 price",
        //     'price.regex'   =>"01 price",
        //     'dealer.regex'  =>"01 dealer",
        //     'unit.required' =>"01 unit",
        //     'unit.string'   =>"01 unit",
        //     'stock.required'=>"01 stock",
        //     'stock.integer' =>"01 stock",
        // ]);
    
        // // 檢測出現錯誤
        // if($validator->fails()){
        //     // 取得第一筆錯誤
        //     $message = explode(" ",$validator->errors()->first());
        //     return $this->Service->response($message[0],$message[1]);
        // }

        // // 更新
        // $Item = Item::find($req['id']);
        // $Item->firms_id       = $req['firm'];
        // $Item->item_groups_id = $req['group'];
        // $Item->name           = $req['name'];
        // $Item->price          = $req['price'];
        // $Item->dealer_price   = $req['dealer'];
        // $Item->unit           = $req['unit'];
        // $Item->note           = $req['note'];
        // $Item->use_flag       = $req['use'];
        // // $Item->moq            = $req['minOrderQty'];
        // // $Item->mpq            = $req['minPackageQty'];
        // $Item->save();

        // $update = ItemSetting::firstOrCreate(
        //     [
        //         'item_id' => $req['id'], 
        //         'store_id'=> $store_id
        //     ],
        //     [
        //         'store_id'    => $store_id,
        //         "item_id"     => $req['id'],
        //         "safety_stock"=> $req['stock']
        //     ]
        // );

        // // 寫入到收藏庫
        // if (  $req["favorite"] == 1 || $req["favorite"] == True ) {
        //     $update = ItemCollection::firstOrCreate(
        //         [
        //             "user_id"=>$user_id,
        //             "item_id"=>$req['id'],
        //         ],
        //         [
        //             "user_id"=>$user_id,
        //             "item_id"=>$req['id'],
        //             "sort_by"=>1,
        //         ]
        //     );
        // } else {
        //     ItemCollection::where([
        //         ['item_id', '=', $req['id']],
        //         ['user_id','=', $user_id]
        //     ])
        //     ->delete();
        // }

        // return $this->Service->response("00","ok");  
    }

    public function import(Request $request)
    {
        return (new ItemService())->import($request)->getResponse();
        // ---------- old ----------
        // $req     = $request->all();
        // $store_id= $this->Service->getUserStoreId();
        // $user_id = $this->Service->getUserId();

        // // 寫入資料庫
        // foreach ($req['data'] as $k => $v) {
        //     // 檢查是否存在
        //     $Item = Item::where('code',$v['sku'])->first();
        //     if ( !$Item ) {
        //         $create = Item::create([
        //             "item_groups_id"=>$req['group'],
        //             "firm_id"       =>2,
        //             "name"          =>$v['name'],
        //             "price"         =>$v['price'],
        //             "code"          =>$v['sku'],
        //             "unit"          =>'',
        //             "stock"         =>0,
        //             "note"          =>'',
        //             "use_flag"      =>1,
        //             "dealer_price"  =>0,
        //             "user_id"       =>$user_id,
        //             "store_id"      =>$store_id
        //         ]);
        //         $insert_id = $create->id;
        
        //         // 寫入到分店設定檔
        //         $create = ItemSetting::create([
        //             "store_id"    => $store_id,
        //             "item_id"     => $insert_id,
        //             "safety_stock"=> 0,
        //         ]);
        //     }
        // }

        // return $this->Service->response("00","ok");  
    }


    public function del(Request $request)
    {
        $service = new ItemService();
        if($service->checkItemUse($request)){
            return $service->getResponse();
        }else{
            return $service->del($request)->getResponse();
        }
        // ---------- old ----------
        // $req     = $request->all();
        // // 檢查品項是否有使用
        // $chk = RequisitionItem::where('items_id','=',$req['id'])->first();
        // if ( $chk ) {
        //     return $this->Service->response("05","");
        // }

        // Item::destroy($req['id']);

        // return $this->Service->response("00","");
    }

    public function record(item $item)
    {
        // return $service->response("999","");
    }


}
