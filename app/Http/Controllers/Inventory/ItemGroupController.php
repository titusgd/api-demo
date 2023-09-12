<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Inventory\ItemGroupService;
// ---------- old ----------
// use App\Http\Controllers\Controller;
// use App\Models\ItemGroup;
// use App\Services\Service;
// use Illuminate\Support\Facades\Validator;
// use Illuminate\Http\Request;
// use Illuminate\Support\MessageBag;
// use Illuminate\Support\Facades\DB;
// use Symfony\Component\HttpFoundation\Response;
// use App\Services\Inventory\ItemGroupService;

class ItemGroupController extends Controller
{

    
    // function __construct()
    // {
    //     $this->Service = new Service();
    // }

    public function index(Request $request)
    {   
        return (new ItemGroupService())->list($request)->getResponse();
        // ---------- old ----------
        // // $store_id = 1;
        // $store_id = $this->Service->getUserStoreId();

        // // 使用Eloquent 查詢並取得資料
        // $results = ItemGroup::select(
        //     "id",
        //     "name",
        //     DB::raw("
        //         (select count(id) from `items` 
        //         where item_groups_id = `item_groups`.id
        //         and store_id in (0, {$store_id})
        //         and stock >= (
        //             ifnull((
        //                 select sum(qty) from `inventories` 
        //                 where store_id = {$store_id} 
        //                 and item_id = `items`.`id`
        //             ),0))
        //         ) as notinstock
        //     "),
        //     DB::raw("ifnull((select count(id) from items where item_groups_id = `item_groups`.id and store_id in (0, {$store_id})),0) as count"),
        // )
        // ->orderBy('sort_by')
        // ->get()
        // ->toArray();
       
        // return $this->Service->response("00","ok",$results);
    }

    
    public function store(Request $request)
    {
        $service = (new ItemGroupService())->validCreate($request);
        if(!empty($service->getResponse())) return $service->getResponse();
        return $service->create($request)->getResponse();
        // ---------- old ----------
        // // 將請求轉成陣列
        // $req = $request->all();
        // $store_id = $this->Service->getUserStoreId();

        // // 檢查
        // $validator = Validator::make($req,[
        //     'name' => 'required|unique:item_groups,name'
        // ],[
        //     'name.required' =>"01 name",
        //     'name.unique'   =>"03 name",
        // ]);
    
        // // 檢測出現錯誤
        // if($validator->fails()){
        //     // 取得第一筆錯誤
        //     $message = explode(" ",$validator->errors()->first());
        //     return $this->Service->response($message[0],$message[1]);
        // }

        // // 取排序
        // $sortby = ItemGroup::max('sort_by');
        // $sortby +=1;

        // // 寫入資料庫
        // ItemGroup::create([
        //     "name"    =>$req['name'],
        //     "store_id"=>$store_id,
        //     "sort_by" =>$sortby
        // ]);
        // return $this->Service->response("00","ok");  

    }
 
    public function update(Request $request)
    {
        return (new ItemGroupService())->update($request)->getResponse();

        // $req = $request->all();

        // foreach ( $req['data'] as $k => $v ) {
        //     // 檢查
        //     $validator = Validator::make($req['data'][$k],[
        //         'name'=> 'required|unique:item_groups,name,'.$v['id'],
        //     ],[
        //         'name.required'=>"01 name",
        //         'name.unique'=>"03 name",
        //     ]);

        //     // 檢測出現錯誤
        //     if($validator->fails()){
        //         // 取得第一筆錯誤
        //         $message = explode(" ",$validator->errors()->first());
        //         return $this->Service->response($message[0],$message[1]);
        //     }

        //     // 更新
        //     $ItemGroup = ItemGroup::find($v['id']);
        //     $ItemGroup->name = $v['name'];
        //     $ItemGroup->sort_by = $k;
        //     $ItemGroup->save();
        // }


        // return $this->Service->response("00","ok");       
        
    }

    public function del(Request $request)
    {
        $service = (new ItemGroupService())->del($request);
        return $service->getResponse();
        // ---------- old ----------
        // $req = $request->all();
        // ItemGroup::where('id',$req['id'])
        // ->delete(); 

        // return $this->Service->response("00","ok");    
    }
}
