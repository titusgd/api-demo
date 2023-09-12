<?php

namespace App\Services\Inventory;

use App\Services\Service;
use App\Models\Item;
use App\Models\Inventory;
use Illuminate\Support\Facades\DB;

class ImportService extends Service
{
    private $req;
    private $user_id;
    private $store_id;
    private $import_status = [
        "0" => "報廢",
        "1" => "盤點",
        "2" => "進貨",
        "3" => "銷貨",
        "4" => "消耗",
    ];

    function __construct($req)
    {
        $this->req = $req;
        $this->user_id = Service::getUserId();
        $this->store_id = Service::getUserStoreId();
    }

    public function create()
    {   

        // 匯入前清空，只保留最新資料
        // $res=Inventory::truncate();
        $res=Inventory::where('store_id', $this->store_id)->delete();

        // 檢查欄位
        $chk = ['庫存類別','總計','平均',''];

        foreach ($this->req['data'] as $k => $v) {

            if ( in_array($this->req['data'][$k][0],$chk) ) {
                continue;
            }

            // 檢查商品是否存在，若存在則新增紀錄
            $item = Item::select('id')->where('name','=',$this->req['data'][$k][1])->first();

            if ( $item ) {
                // echo $item->id . $this->req['data'][$k][1] . ',';
                
                $inventory = Inventory::create(
                    [
                        "store_id"=>$this->store_id,
                        "user_id" =>$this->user_id,
                        "item_id" =>$item->id,
                        "type"    =>$this->getTypeCode($this->req['data'][$k][6]),
                        "qty"     =>$this->req['data'][$k][3]
                    ]
                );

            }
   
        }

        return Service::response("00", "ok");
    }


    public function getTypeCode($str)
    {
        return array_search($str, $this->import_status);
    }
    public function getTypeStr($sum)
    {
        return $this->import_status[$sum];
    }
}
