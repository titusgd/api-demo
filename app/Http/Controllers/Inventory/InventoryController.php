<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Inventory;
use App\Services\Service;
use App\Services\Inventory\ImportService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;


class InventoryController extends Controller
{
    
    function __construct()
    {
        $this->Service = new Service();
    }

    public function list(Request $request)
    {
        $req = $request->all();

        //預設筆數
        $count = (!empty($req['count'])) ? $req['count'] : 20;

        $store_id = ( $req['store'] <> '' ) ? $req['store'] : $this->Service->getUserStoreId();
        // $store_id = $this->Service->getUserStoreId();
        // $store_id = 1;

        $where = array();
        array_push($where,['item_groups_id', '=', $req['group']]);
        // array_push($where,['store_id', '=', $store_id]);

        // 使用Eloquent 查詢並取得資料
        $results = Item::select(
            "items.id",
            "name",
            "unit",
            "stock as safety",
            DB::raw("0 as notinstock"),
            DB::raw("ifnull((select qty from inventories where item_id = items.id and store_id = {$store_id}),0) as qty"),
            DB::raw("(select created_at from inventories where item_id = items.id and store_id = {$store_id}) as import_date"),
        )
        ->where($where)
        ->where(function ($results) use ($store_id) {
            $results->where('store_id', '=', $store_id);
            $results->orwhere('store_id', '=', 0);
        })
        ->paginate($count)
        ->toArray();
   
        // 製作page回傳格式
        $total      =$results['last_page'];    //總頁數
        $countTotal =$results['total'];        //總筆數
        $page       =$results['current_page'];  //當前頁次

        $pageinfo = [
            "total"     =>$total,      // 總頁數
            "countTotal"=>$countTotal, // 總筆數
            "page"      =>$page,       // 頁次
        ];

        foreach ( $results['data'] as $k => $v ) {
            $results['data'][$k]['import_date'] = ( $v['import_date'] != '' ) ? $this->Service->dateFormat($v['import_date']) : [];
        }
        
        return $this->Service->response_paginate("00","ok",$results['data'],$pageinfo);
        
    }

    public function import(Request $request) {

        $req = $request->all();

        $service = new ImportService($req);
        return $service->create();

    }

    public function taking(Request $request)
    {
        $req = $request->all();

        $user_id  = $this->Service->getUserId();
        $store_id = $this->Service->getUserStoreId();

        foreach ( $req['list'] as $k => $v ) {
            $req['list'][$k]['user_id']  = $user_id;
            $req['list'][$k]['store_id'] = $store_id;
            $req['list'][$k]['created_at'] = date ("Y-m-d H:i:s");
            $req['list'][$k] = $this->Service->array_replace_key($req['list'][$k], "id", "item_id");
        }

        $res = Inventory::insert($req['list']);

        return $this->Service->response("00","OK");      
    }

    public function update(Request $request)
    {
        $req = $request->all();

        $res = Inventory::create(
            [
                "item_id" => $req['id'],
                "qty"     => $req['qty'],
                "type"    => $req['type'],
                "store_id"=> $this->Service->getUserStoreId(),
                "user_id" => $this->Service->getUserId(),
            ]
        );

        return $this->Service->response("00","OK");      
    }

}
