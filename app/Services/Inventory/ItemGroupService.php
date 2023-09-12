<?php

namespace App\Services\Inventory;
// ---------- models ----------
use App\Models\ItemGroup;

// ---------- methods ----------
use App\Services\Service;
use Illuminate\Support\Facades\DB;

class ItemGroupService extends Service
{
    private $response;
    public function setResponse($response)
    {
        $this->response = $response;
        return $this;
    }
    public function getResponse()
    {
        return $this->response;
    }
    public function list($request)
    {
        $store_id = Service::getUserStoreId();
        $user_id = Service::getUserId();
        $results = ItemGroup::select(
            "id",
            "name",
            DB::raw("
                (select count(id) from `items` 
                where item_groups_id = `item_groups`.id
                and store_id in (0, {$store_id})
                and stock >= (
                    ifnull((
                        select sum(qty) from `inventories` 
                        where store_id = {$store_id} 
                        and item_id = `items`.`id`
                    ),0))
                ) as notinstock
            "),
            DB::raw("ifnull((select count(id) from items where item_groups_id = `item_groups`.id and store_id in (0, {$store_id})),0) as count"),
        )
            ->orderBy('sort_by')
            ->get()
            ->toArray();
        $this->response = Service::response("00", "ok", $results);
        return $this;
    }
    public function validCreate($request)
    {
        $rules = ['name' => 'required|unique:item_groups,name'];
        $message = [
            'name.required' => "01 name",
            'name.unique'   => "03 name",
        ];
        $this->response = Service::validatorAndResponse($request->toArray, $rules, $message);
        return $this;
    }
    public function create($request)
    {
        $store_id = Service::getUserStoreId();
        $user_id = Service::getUserId();
        $req = $request->all();

        // 取排序
        $sortby = ItemGroup::max('sort_by');
        $sortby += 1;

        // 寫入資料庫
        ItemGroup::create([
            "name"    => $req['name'],
            "store_id" => $store_id,
            "sort_by" => $sortby
        ]);
        $this->response = Service::response("00", "ok");
        return $this;
    }
    // public function validUpdate($request)
    // {
    //     $req = $request->all();
    //     foreach ($req['data'] as $k => $v) {
    //         // 檢查
    //         $validator = Validator::make($req['data'][$k], [
    //             'name' => 'required|unique:item_groups,name,' . $v['id'],
    //         ], [
    //             'name.required' => "01 name",
    //             'name.unique' => "03 name",
    //         ]);

    //         // 檢測出現錯誤
    //         if ($validator->fails()) {
    //             // 取得第一筆錯誤
    //             $message = explode(" ", $validator->errors()->first());
    //             return $this->Service->response($message[0], $message[1]);
    //         }

    //         // 更新
    //         $ItemGroup = ItemGroup::find($v['id']);
    //         $ItemGroup->name = $v['name'];
    //         $ItemGroup->sort_by = $k;
    //         $ItemGroup->save();
    //     }
    //     dd($this->response);
    //     dump($request->toArray());
    // }
    public function update($request)
    {
        $req = $request->all();

        foreach ($req['data'] as $k => $v) {
            // 檢查
            $this->response = Service::validatorAndResponse(
                $req['data'][$k],
                ['name' => 'required|unique:item_groups,name,' . $v['id']],
                [
                    'name.required' => "01 name",
                    'name.unique' => "03 name",
                ]
            );
            if (!empty($this->response)) break;

            // 更新
            $ItemGroup = ItemGroup::find($v['id']);
            $ItemGroup->name = $v['name'];
            $ItemGroup->sort_by = $k;
            $ItemGroup->save();
        }

        if (empty($this->response)) $this->response = Service::response("00", "ok");
        return $this;
    }

    public function del($request){
        $req = $request->all();
        ItemGroup::where('id',$req['id'])
        ->delete(); 

        $this->response = Service::response("00","ok");   
        return $this;
    }
}
