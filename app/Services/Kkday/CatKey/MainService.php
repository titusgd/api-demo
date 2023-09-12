<?php

namespace App\Services\Kkday\CatKey;

// models
use App\Models\Kkday\CatKey\Main as MainKey;
use App\Models\Kkday\CatKey\CatSubKey as SubKey;
// methods
use App\Services\Kkday\KkdayService;
use Illuminate\Http\Request;
use App\Traits\RulesTrait;
use Illuminate\Support\Facades\DB;

class MainService extends KkdayService
{
    use RulesTrait;
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
        // DB::enableQueryLog();
        switch ($method) {
            case 'store':
                $rules = [
                    'name' => 'required|string|unique:*.kkday_cat_main_keys,name',
                    'code' => 'required|string|unique:*.kkday_cat_main_keys,code',
                ];
                $data = $this->request->toArray();
                break;
            case 'update':
                $rules = [
                    'id' => 'required|exists:kkday_cat_main_keys,id',
                    'name' => 'required|string|unique:*.kkday_cat_main_keys,name,' . $this->dataId,
                    'code' => 'required|string|unique:*.kkday_cat_main_keys,code,' . $this->dataId,
                ];
                $data = $this->request->toArray() + ['id' => $this->dataId];
                break;
            case 'destroy':
                // $rules = [
                //     'id' => 'required|exists:kkdays_airport_type_codes,id',
                // ];
                // $data = ['id' => $this->dataId];
                break;
            case 'settingStatus':
                $rules = [
                    'cat_main_key_id' => 'required|exists:*.kkday_cat_main_keys,id',
                    'use' => 'required'
                ];
                $data = $this->request->toArray();
                // dd($data);
                // $data = $this->request->toArray() + ['id' => $this->request['cat_main_key_id']];
                break;
            case 'settingSort':
                $rules = [
                    'data.*' => 'required|integer|exists:*.kkday_cat_main_keys,id',
                ];
                $data = $this->request->toArray();
                // dd($data);
                // $data = $this->request->toArray() + ['id' => $this->request['cat_main_key_id']];
                break;
        }

        $this->response = self::validate($data, $rules, $this->changeErrorName);
        // dd(DB::getQueryLog());
        return $this;
    }

    public function store()
    {
        if (!empty($this->response)) return $this;
        $main_key = new MainKey();
        $main_key->name = $this->request['name'];
        $main_key->code = $this->request['code'];
        $main_key->sort = '999';
        $main_key->save();
        $this->response = KkdayService::response('00', 'ok');
        return $this;
    }

    public function update()
    {

        if (!empty($this->response)) return $this;
        $main_key = MainKey::find($this->dataId);
        $main_key->name = $this->request['name'];
        $main_key->code = $this->request['code'];
        $main_key->save();
        $this->response = KkdayService::response('00', 'ok');
        return $this;
    }

    public function list()
    {
        $sub_key_column = collect(['id', 'main_id', 'type as code', 'description_ch as description', 'sort']);
        $select_main = collect(['id', 'name', 'code', 'sort']);
        if (!empty(auth()->user()->id)) {
            $select_main->push('id as cat_main_key_id')->push('use');
            $sub_key_column->push('id as sub_key_id')->push('use');
        }
        $select_main  = $select_main->toArray();
        $sub_key_column = $sub_key_column->toArray();
        $data = MainKey::select($select_main)
            ->with(['sub_key_list' => function ($query) use ($sub_key_column) {
                $query->select($sub_key_column);
                ($this->request['sub_key_state'] === false) && $query->where('use', '=', 0);
                ($this->request['sub_key_state'] === true) && $query->where('use', '=', 1);
                (empty(auth()->user()->id)) && $query->where('use', '=', 1);
                $query->orderBy('sort');
            }]);
        ($this->request['cat_main_key_state'] === false) && $data->where('use', '=', 0);
        ($this->request['cat_main_key_state'] === true) && $data->where('use', '=', 1);
        (empty(auth()->user()->id)) && $data->where('use', '=', 1);
        $data = $data->orderBy('sort', 'asc')
            ->get()->toArray();

        // 未分類項目
        $main_id_0 = SubKey::select($sub_key_column)->where('main_id', '=', '0');
        ($this->request['sub_key_state'] === false) && $main_id_0->where('use', '=', 0);
        ($this->request['sub_key_state'] === true) && $main_id_0->where('use', '=', 1);
        $main_id_0 = $main_id_0->get()->toArray();
        $data[] = [
            'id' => 0,
            'name' => '未分類',
            'type' => '',
            'description_ch' => '', 'description_en' => '', 'sort' => 999,
            'sub_key_list' => $main_id_0
        ];

        // 移除不必要的欄位
        foreach ($data as $key => $value) {
            //如果沒有登入人員ID，則移除id
            if (!empty(auth()->user()->id)) {
                $data[$key]['cat_main_key_id'] = $data[$key]['id'];
            }
            unset($data[$key]['id']);

            foreach ($value['sub_key_list'] as $key2 => $item) {

                unset($data[$key]['sub_key_list'][$key2]['main_id']);
                //如果沒有登入人員ID，則移除id
                // if (!empty(auth()->user()->id)) {
                //     // dd($item,$data[$key]['sub_key_list']);
                //     $data[$key]['sub_key_list'][$key2]['sub_key_id'] = $item['id'];
                // }
                unset($data[$key]['sub_key_list'][$key2]['id']);
            }
        }
        $this->response = KkdayService::response('00', 'ok', $data);
        return $this;
    }


    public function settingSort()
    {
        if (!empty($this->response)) return $this;

        // 全部變更為0
        DB::table('kkday_cat_main_keys')->update(['sort' => '0']);

        // 開始更新
        $data = $this->request['data'];
        foreach ($data as $key => $value) {
            $is_main = MainKey::where('id', $value)->first();
            if (!empty($is_main)) {
                $main_key = MainKey::find($value);
                $main_key->sort = ($key + 1);
                $main_key->save();
            }
        }
        $this->response = KkdayService::response('00', 'ok');
        return $this;
    }

    public function settingStatus()
    {
        if (!empty($this->response)) return $this;
        $main_key = MainKey::find($this->request['cat_main_key_id']);
        $main_key->use = ($this->request['use'] == false) ? false : true;
        $main_key->save();
        $this->response = KkdayService::response('00', 'ok');
        return $this;
    }

    public function getResponse(): object
    {
        return $this->response;
    }
    public function setResponse($response): self
    {
        $this->response = $response;
        return $this;
    }
}
