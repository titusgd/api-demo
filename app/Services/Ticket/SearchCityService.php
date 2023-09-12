<?php

namespace App\Services\Ticket;

use App\Services\Service;
use App\Traits\ResponseTrait;
use App\Traits\RulesTrait;
use App\Models\Ticket\SearchCity;
use Illuminate\Support\Facades\DB;
use App\Traits\TableMethodTrait;
class SearchCityService extends Service
{
    use ResponseTrait;
    use RulesTrait;
    use TableMethodTrait;
    private $response;
    private $request;
    private $changeErrorName, $dataId;

    public function __construct($request, $dataId = null)
    {
        $this->dataId = $dataId;
        $this->request = collect($request);
    }

    public function runValidate($method)
    {
        switch ($method) {
            case "store":
                $rules = [
                    'data' => 'required|array',
                    'data.*' => 'integer|exists:*.kkday_citys,id|unique:*.ticket_search_cities,kkday_city_id',
                ];
                $data = $this->request->toArray();
                self::setMessages([
                    'data.*.exists' => '02 data.*.city_id',
                    'data.*.unique' => '03 data.*.city_id',
                ]);
                break;
            case "destroy":
                $rules = [
                    'city_id' => 'required|exists:*.ticket_search_cities,kkday_city_id',
                ];
                // $data = $this->request->toArray();

                $data['city_id'] = $this->dataId;
                break;
            case 'settingStor':
                $rules = [
                    'data' => 'required|array',
                    'data.*' => 'integer|exists:*.ticket_search_cities,kkday_city_id',
                ];
                $data = $this->request->toArray();
                self::setMessages([
                    'data.*.exists' => '02 data.*.search_city_id',
                ]);
                break;
            case 'method':
                $rules = [
                    // code
                ];
                $data = $this->request->toArray();
                break;
        }
        $this->response = self::validate($data, $rules, $this->changeErrorName);
        if ($method == "store" && (!empty($this->response))) {
            // 重複的資料
            // if ($this->response->original['code'] == '03') {
            //     $in_city_id = SearchCity::whereIn('kkday_city_id', $this->request['data'])
            //         ->get()
            //         ->pluck('kkday_city_id')
            //         ->toArray();
            //     //$in_city_id已經存在的資料。
            //     self::setErr('03', 'data.*',['data'=>$in_city_id]);
            // }
        }
        return $this;
    }

    public function index()
    {
        if (!empty(self::getResponse())) return $this;
        //code ... ...
        $data = SearchCity::with(['kkdayCity'])
            ->orderBy('sort')
            ->get();
        $data = $data->map(function ($item) {
            return $item->formate();
        });
        self::setOk($data);
        return $this;
    }

    public function store()
    {
        if (!empty(self::getResponse())) return $this;
        //code ... ...
        $data = $this->request['data'];
        foreach ($data as $key => $value) {
            $model = new SearchCity();
            $model->kkday_city_id = $value;
            $model->sort = 0;
            $model->save();
        }
        $count = SearchCity::count();
        SearchCity::where('sort', '=', 0)->update(['sort' => $count]);
        self::setOk();
        return $this;
    }

    public function update()
    {
        if (!empty(self::getResponse())) return $this;
        //code ... ...
        return $this;
    }

    public function destroy()
    {
        if (!empty(self::getResponse())) return $this;
        //code ... ...
        // 取得之後的排序
        self::delAndReSort(
            (new SearchCity),
            $this->dataId,
            'kkday_city_id'
        );
        self::setOk();
        return $this;
    }

    public function settingStor()
    {
        if (!empty(self::getResponse())) return $this;
        self::sort((new SearchCity),$this->request['data'],'kkday_city_id');
        self::setOk();
        return $this;
    }

    public function publicIndex()
    {

        if (!empty(self::getResponse())) return $this;
        $data = SearchCity::with(['kkdayCity'])
            ->orderBy('sort')
            ->get();
        $data = $data->map(function ($item) {
            return $item->formate();
        });
        self::setOk($data);
        return $this;
    }
}
