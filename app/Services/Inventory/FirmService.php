<?php

namespace App\Services\Inventory;

//module
use App\Models\firm as Firm;
// method 
use App\Services\Service;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;

class FirmService extends Service
{
    private $response;
    public function getResponse()
    {
        return $this->response;
    }
    public function setResponse($code, $message, $data = [])
    {
        $this->response = Service::response($code, $message, $data);
        return $this;
    }

    public function getList($request)
    {

        $store_id = Service::getUserStoreId();
        //預設筆數
        $count = (!empty($request['count'])) ? $request['count'] : 20;

        $where = array();
        if (isset($request['store']) && $request['store'] != 0) {
            array_push($where, ['stores_id', '=', $request['store']]);
        }
        if (!empty($request['search']) && isset($request['search']) && $request['search'] != '') {
            array_push($where, ['firm', 'like', '%' . $request['search'] . '%']);
        }
        if (isset($request['firmType']) && $request['firmType'] != 0) {
            array_push($where, ['type', '=', $request['firmType']]);
        }


        // 使用Eloquent 查詢並取得資料
        $results = firm::select(
            "id",
            "gui_number as guiNumber",
            "firm",
            "type as firmType",
            "representative",
            "address",
            "phone",
            "contact_name",
            "contact_phone"
        )
            ->where($where)
            ->when(
                $request['all'] != 1,
                function ($query) use ($store_id) {
                    $query->whereIn(
                        'id',
                        function ($query) use ($store_id) {
                            $query->select('firms_id')
                                ->distinct()
                                ->from('items')
                                ->whereRaw("`store_id`={$store_id}");
                        }
                    );
                }

            )
            ->orderBy('created_at', 'desc');
        // ->paginate($count);
        $results = ($request['count'] != 0) ? $results->paginate($count) : $results->get();
        $results = $results->toArray();


        // 製作page回傳格式
        $total      = (!empty($results['last_page']))    ? $results['last_page'] : 1;     //總頁數
        $countTotal = (!empty($results['total']))        ? $results['total'] : 1;         //總筆數
        $page       = (!empty($results['current_page'])) ? $results['current_page'] : 1;  //當前頁次

        $page_info = [
            "total"     => $total,      // 總頁數
            "countTotal" => $countTotal, // 總筆數
            "page"      => $page,       // 頁次
        ];

        $data = ($request['count'] != 0) ? $results['data'] : $results;
        foreach ($data as $k => $v) {
            $data[$k]['phone'] = (!empty($data[$k]['phone'])) ? $data[$k]['phone'] : "";
            $data[$k]['contact']['name']  = (!empty($v['contact_name'])) ? $v['contact_name'] : "";
            $data[$k]['contact']['phone'] = (!empty($v['contact_phone'])) ? $v['contact_phone'] : "";
            unset($data[$k]['contact_name']);
            unset($data[$k]['contact_phone']);
        }
        $this->response = Service::response_paginate("00", 'ok', $data, $page_info);
        return $this;
        // return Service::response_paginate("00", 'ok', $data, $pageinfo);/
    }

    public function validAdd($request)
    {
        $rules = [
            'guiNumber' => 'required|regex:/^[0-9]{8}$/|unique:firms,gui_number',
            'firm' => 'required|string',
        ];
        $message = [
            'guiNumber' => [
                'required' => '01 guiNumber',
                'regex' => '01 guiNumber',
                'unique' => '01 guiNumber',
            ],
            [
                'firm' => [
                    'required' => '01 firm',
                    'string' => '01 firm',
                ]
            ]
        ];
        $this->response = Service::validatorAndResponse($request, $rules, Arr::dot($message));
        return $this;
    }

    public function addFirm($request)
    {
        // 陣列調整
        $request = Service::array_replace_key($request, "guiNumber", "gui_number");
        $request = Service::array_replace_key($request, "firmType", "type");
        $request["contact_name"] = $request['contact']['name'];
        $request["contact_phone"] = $request['contact']['phone'];
        unset($request['contact']);

        $request["user_id"] = auth()->user()->id;

        // 寫入資料庫
        firm::create($request);
        $this->response = Service::response("00", "ok");
        return $this;
    }

    public function validGetApiData($request)
    {
        $rules = [
            'guiNumber' => 'required|regex:/^[0-9]{8}$/|unique:firms,gui_number,' . $request['guiNumber'],
        ];
        $message = [
            'guiNumber.required' => "01 guiNumber",
            'guiNumber.regex'    => "01 guiNumber",
            'guiNumber.unique'   => "03 guiNumber",
        ];
        $this->response = Service::validatorAndResponse($request, $rules, $message);
        return $this;
    }

    public function getFirmInfo($gui_number)
    {
        $firm_info = Service::getFrimInfo($gui_number, 2);
        $this->response = ($firm_info) ? Service::response('00', 'ok', [
            "guiNumber"     => $firm_info[0]['Business_Accounting_NO'],
            "name"          => $firm_info[0]['Company_Name'],
            "representative" => $firm_info[0]['Responsible_Name'],
            "address"       => $firm_info[0]['Company_Location'],
        ]) : Service::response("02", 'guiNumber');
        return $this;
    }

    public function isFirm($firm_id)
    {
        $firm = Firm::select('id')->where('id', '=', $firm_id)->first();
        if (!$firm) $this->response = Service::response('02', 'id');
        return $this;
    }

    public function validUpdate($request)
    {
        $rules = [
            'guiNumber' => 'required|regex:/^[0-9]{8}$/|unique:firms,gui_number,' . $request['id'],
            'firm'      => 'required|string',
        ];
        $message = [
            'guiNumber.required' => "01 guiNumber",
            'guiNumber.regex'    => "01 guiNumber",
            'guiNumber.unique'   => "03 guiNumber",
            'firm.required'      => "01 firm",
            'firm.string'        => "01 firm",
        ];
        // 檢查
        // $this->response = Service::validatorAndResponse($request, $rules, $message);
        $this->response = Service::validatorAndResponse($request->toArray(), $rules, $message);
        return $this;
    }

    public function updateFirm($request)
    {
        $firm = firm::find($request->id);
        $firm->firm           = $request->firm;
        $firm->gui_number     = $request->guiNumber;
        $firm->type           = (!empty($request->firmType)) ? $request->firmType : 0;
        $firm->address        = $request->address;
        $firm->phone          = $request->phone;
        $firm->representative = $request->representative;
        $firm->contact_name   = $request->contact['name'];
        $firm->contact_phone  = $request->contact['phone'];
        // $firm->user_id        = 0;
        $firm->save();
        $this->response = Service::response('00', 'ok');
        return $this;
    }
}
