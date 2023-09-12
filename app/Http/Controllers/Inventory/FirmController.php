<?php

namespace App\Http\Controllers\Inventory;
// ---------- methods ----------------------------------------------------------
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
// use Illuminate\Support\MessageBag;
// use Symfony\Component\HttpFoundation\Response;

use App\Services\Service;
use App\Services\Inventory\FirmService;
// ---------- models -----------------------------------------------------------
use App\Models\firm;


class FirmController extends Controller
{
    public function list(Request $request)
    {
        return (new FirmService)->getList($request)->getResponse();
    }

    public function add(Request $request)
    {
        $service = (new FirmService())->validAdd($request->all());
        if (!empty($service->getResponse())) return $service->getResponse();
        return $service->addFirm($request->all())->getResponse();
    }

    public function show(firm $firm)
    {
        //
    }


    public function update(Request $request, firm $firm)
    {   
        $service = (new FirmService)->isFirm($request['id']);
        if (!empty($service->getResponse())) return $service->getResponse();
        if (!empty($service->validUpdate($request)->getResponse())) return $service->getResponse();
        return $service->updateFirm($request)->getResponse();
    }


    public function getApiData(Request $request)
    {
        $service = (new FirmService)->validGetApiData($request->all());
        return (!empty($service->getResponse())) ? $service->getResponse() : $service->getFirmInfo($request['guiNumber'])->getResponse();

    }


    // -------------------- 舊的 ------------------------------------------------
    
    // public $Service;
    // function __construct()
    // {
    //     $this->Service = new Service();
    // }

    // public function list(Request $request)
    // {
    //     $req = $request->all();
    //     $store_id = $this->Service->getUserStoreId();

    //     //預設筆數
    //     $count = (!empty($req['count'])) ? $req['count'] : 20;

    //     $where = array();
    //     if (isset($req['store']) && $req['store'] != 0) {
    //         array_push($where, ['stores_id', '=', $req['store']]);
    //     }
    //     if (!empty($req['search']) && isset($req['search']) && $req['search'] != '') {
    //         array_push($where, ['firm', 'like', '%' . $req['search'] . '%']);
    //     }
    //     if (isset($req['firmType']) && $req['firmType'] != 0) {
    //         array_push($where, ['type', '=', $req['firmType']]);
    //     }


    //     // 使用Eloquent 查詢並取得資料
    //     $results = firm::select(
    //         "id",
    //         "gui_number as guiNumber",
    //         "firm",
    //         "type as firmType",
    //         "representative",
    //         "address",
    //         "phone",
    //         "contact_name",
    //         "contact_phone"
    //     )
    //         ->where($where)
    //         ->when(
    //             $req['all'] != 1,
    //             function ($query) use ($store_id) {
    //                 $query->whereIn(
    //                     'id',
    //                     function ($query) use ($store_id) {
    //                         $query->select('firms_id')
    //                             ->distinct()
    //                             ->from('items')
    //                             ->whereRaw("`store_id`={$store_id}");
    //                     }
    //                 );
    //             }

    //         )
    //         ->orderBy('created_at', 'desc');
    //     // ->paginate($count);
    //     $results = ($req['count'] != 0) ? $results->paginate($count) : $results->get();
    //     $results = $results->toArray();


    //     // 製作page回傳格式
    //     $total      = (!empty($results['last_page']))    ? $results['last_page'] : 1;     //總頁數
    //     $countTotal = (!empty($results['total']))        ? $results['total'] : 1;         //總筆數
    //     $page       = (!empty($results['current_page'])) ? $results['current_page'] : 1;  //當前頁次

    //     $pageinfo = [
    //         "total"     => $total,      // 總頁數
    //         "countTotal" => $countTotal, // 總筆數
    //         "page"      => $page,       // 頁次
    //     ];

    //     $data = ($req['count'] != 0) ? $results['data'] : $results;
    //     foreach ($data as $k => $v) {
    //         $data[$k]['phone'] = (!empty($data[$k]['phone'])) ? $data[$k]['phone'] : "";
    //         $data[$k]['contact']['name']  = (!empty($v['contact_name'])) ? $v['contact_name'] : "";
    //         $data[$k]['contact']['phone'] = (!empty($v['contact_phone'])) ? $v['contact_phone'] : "";
    //         unset($data[$k]['contact_name']);
    //         unset($data[$k]['contact_phone']);
    //     }

    //     return $this->Service->response_paginate("00", 'ok', $data, $pageinfo);
    // }


    // public function add(Request $request)
    // {
    //     $req = $request->all();

    //     // 檢查
    //     $validator = Validator::make($req, [
    //         'guiNumber' => 'required|regex:/^[0-9]{8}$/|unique:firms,gui_number',
    //         'firm' => 'required|string',
    //     ], [
    //         'guiNumber.required' => "01 guiNumber",
    //         'guiNumber.regex'    => "01 guiNumber",
    //         'guiNumber.unique'   => "03 guiNumber",
    //         'firm.required'      => "01 firm",
    //         'firm.string'        => "01 firm",
    //     ]);

    //     // 檢測出現錯誤
    //     if ($validator->fails()) {
    //         // 取得第一筆錯誤
    //         $message = explode(" ", $validator->errors()->first());
    //         return $this->Service->response($message[0], $message[1]);
    //     }

    //     // 陣列調整
    //     $req = $this->Service->array_replace_key($req, "guiNumber", "gui_number");
    //     $req = $this->Service->array_replace_key($req, "firmType", "type");
    //     $req["contact_name"] = $req['contact']['name'];
    //     $req["contact_phone"] = $req['contact']['phone'];
    //     unset($req['contact']);

    //     $user_id = Auth::user()->id;
    //     $req["user_id"] = $user_id;

    //     // 寫入資料庫
    //     firm::create($req);
    //     return $this->Service->response("00", "ok");
    // }

    // public function show(firm $firm)
    // {
    //     //
    // }


    // public function update(Request $request, firm $firm)
    // {
    //     $req = $request->all();

    //     // 檢查資源是否存在
    //     $chk = firm::where("id", "=", $req['id'])->first();
    //     if (!$chk) {
    //         return $this->Service->response("02", "id");
    //     }

    //     // 檢查
    //     $validator = Validator::make($req, [
    //         'guiNumber' => 'required|regex:/^[0-9]{8}$/|unique:firms,gui_number,' . $req['id'],
    //         'firm'      => 'required|string',
    //     ], [
    //         'guiNumber.required' => "01 guiNumber",
    //         'guiNumber.regex'    => "01 guiNumber",
    //         'guiNumber.unique'   => "03 guiNumber",
    //         'firm.required'      => "01 firm",
    //         'firm.string'        => "01 firm",
    //     ]);

    //     // 檢測出現錯誤
    //     if ($validator->fails()) {
    //         // 取得第一筆錯誤
    //         $message = explode(" ", $validator->errors()->first());
    //         return $this->Service->response($message[0], $message[1]);
    //     }

    //     // 修改資料
    //     $firm = firm::find($request->id);
    //     $firm->firm           = $request->firm;
    //     $firm->gui_number     = $request->guiNumber;
    //     $firm->type           = $request->firmType;
    //     $firm->address        = $request->address;
    //     $firm->phone          = $request->phone;
    //     $firm->representative = $request->representative;
    //     $firm->contact_name   = $request->contact['name'];
    //     $firm->contact_phone  = $request->contact['phone'];
    //     // $firm->user_id        = 0;
    //     $firm->save();

    //     return $this->Service->response("00", "ok");
    // }


    // public function getApiData(Request $request)
    // {
    //     $req = $request->all();

    //     // 檢查
    //     $validator = Validator::make($req, [
    //         'guiNumber' => 'required|regex:/^[0-9]{8}$/|unique:firms,gui_number,' . $req['guiNumber'],
    //     ], [
    //         'guiNumber.required' => "01 guiNumber",
    //         'guiNumber.regex'    => "01 guiNumber",
    //         'guiNumber.unique'   => "03 guiNumber",
    //     ]);

    //     // 檢測出現錯誤
    //     if ($validator->fails()) {
    //         // 取得第一筆錯誤
    //         $message = explode(" ", $validator->errors()->first());
    //         return $this->Service->response($message[0], $message[1]);
    //     }

    //     $res = $this->Service->getFrimInfo($req['guiNumber'], 2);

    //     if ($res) {
    //         $data = [
    //             "guiNumber"     => $res[0]['Business_Accounting_NO'],
    //             "name"          => $res[0]['Company_Name'],
    //             "representative" => $res[0]['Responsible_Name'],
    //             "address"       => $res[0]['Company_Location'],
    //         ];
    //         return $this->Service->response("00", 'ok', $data);
    //     } else {
    //         return $this->Service->response("02", 'guiNumber');
    //     }
    // }
}
