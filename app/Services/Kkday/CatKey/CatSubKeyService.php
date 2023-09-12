<?php

namespace App\Services\Kkday\CatKey;
// models
use App\Models\Kkday\CatKey\CatSubKey;
// methods
use App\Services\Kkday\KkdayService;
use Illuminate\Http\Request;
use App\Traits\RulesTrait;
use Illuminate\Support\Facades\DB;

class CatSubKeyService extends KkdayService
{
    use RulesTrait;

    private $response;
    private $request;
    private $changeErrorName, $dataId;

    public function __construct($request, $dataId = null)
    {
        $this->dataId = $dataId;
        $this->request = collect($request);
        $this->request
            ->put('description_ch', $request->input('descriptionCh', ''))
            ->put('description_en', $request->input('descriptionEn', ''));

        $this->request
            ->forget('descriptionCh')
            ->forget('descriptionEn');

        $this->changeErrorName = [
            'description_ch' => 'descriptionCh',
            'description_en' => 'descriptionEn',
        ];
    }

    public function runValidate($method)
    {
        switch ($method) {
            case 'store':
                $rules = [
                    'type' => 'required|string'
                ];
                (!empty($this->request['description_ch'])) && $rules['description_ch'] = 'required|string';
                (!empty($this->request['description_en'])) && $rules['description_en'] = 'required|string';
                $data = $this->request->toArray();
                break;
            case 'update':
                $rules = [
                    'id' => 'required|exists:*.kkdays_airport_type_codes,id',
                    'type' => 'required|string'
                ];
                (!empty($this->request['description_ch'])) && $rules['description_ch'] = 'required|string';
                (!empty($this->request['description_en'])) && $rules['description_en'] = 'required|string';
                $data = $this->request->toArray() + ['id' => $this->dataId];
                break;
            case 'destroy':
                $rules = [
                    'id' => 'required|exists:*.kkdays_airport_type_codes,id',
                ];
                $data = ['id' => $this->dataId];
                break;
            case 'settingStatus':
                $rules = [
                    'cat_sub_key_id' => 'required|exists:*.kkday_cat_sub_keys,id',
                    'use' => 'required'
                ];
                $data = $this->request->toArray();
                break;
            case 'settingSort':
                $rules = [
                    'data.*' => 'required|integer|exists:*.kkday_cat_sub_keys,id',
                ];
                $data = $this->request->toArray();
                break;
        }

        $this->response = self::validate($data, $rules, $this->changeErrorName);

        return $this;
    }

    public function list()
    {
        $columns = [
            'id',
            'type',
            'description_ch',
            'description_en',
        ];
        $this->response = KkdayService::response(
            '00',
            'ok',
            CatSubKey::select($columns)->get()
                ->map(function ($item) {
                    $item['descriptionEn'] = ($item->descriptionEn === null) ? '' : $item->descriptionEn;
                    $item['descriptionCh'] = ($item->descriptionCh === null) ? '' : $item->descriptionCh;
                    return $item;
                })
                ->toArray()
        );
        return $this;
    }
    public function store()
    {
        if (!empty($this->response)) return $this;
        CatSubKey::create($this->request->toArray());
        $this->response = KkdayService::response('00', 'ok');
        return $this;
    }

    public function update()
    {
        if (!empty($this->response)) return $this;
        CatSubKey::where('id', '=', $this->dataId)
            ->update($this->request->toArray());
        $this->response = KkdayService::response('00', 'ok');
        return $this;
    }

    public function destroy()
    {
        if (!empty($this->response)) return $this;
        CatSubKey::where('id', '=', $this->dataId)->delete();
        $this->response = KkdayService::response('00', 'ok');
        return $this;
    }

    public function import()
    {
        // dd(file_exists(base_path() . '/app/Services/Kkday/CatSubKey.json'));
        $data = KkdayService::loadJsonData(base_path() . '/app/Services/Kkday/CatKey/CatSubKey.json');
        $now = date('Y-m-d H:i:s');
        foreach ($data as $key => $value) {
            $data[$key]['description_ch'] = $value['description'];
            $data[$key]['created_at'] = $now;
            $data[$key]['updated_at'] = $now;
            unset($data[$key]['description']);
        }
        CatSubKey::insert($data);
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
    public function settingStatus()
    {
        if (!empty($this->response)) return $this;
        $sub_key = CatSubKey::find($this->request['cat_sub_key_id']);
        $sub_key->use = $this->request['use'];
        $sub_key->save();
        $this->response = KkdayService::response('00', 'ok');
        return $this;
    }

    public function settingSort()
    {
        if (!empty($this->response)) return $this;
        // 取得第一筆資料的main_id
        $sub_key = CatSubKey::find($this->request['data'][0]);
        $sub_key = $sub_key['main_id'];
        // 重置排序
        CatSubKey::where('main_id', '=', $sub_key)->update(['sort' => '999']);
        foreach ($this->request['data'] as $key => $item) {
            $cat_sub_key = CatSubKey::where('id', $item)->first();
            if (!empty($cat_sub_key)) {
                CatSubKey::find($item)->update(['sort' => ($key + 1)]);
            }
        }
        $this->response = KkdayService::response('00', 'ok');
        return $this;
    }
}
