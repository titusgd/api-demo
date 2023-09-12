<?php

namespace App\Services\Kkday;

use App\Services\Kkday\KkdayService;
use App\Traits\ResponseTrait;
use App\Models\Kkday\CountryCode;
use App\Traits\RulesTrait;

class CountryCodeService extends KkdayService
{
    use ResponseTrait;
    use RulesTrait;
    private $response;
    private $request;
    private $changeErrorName, $dataId;
    public function __construct($request, $dataId = null)
    {
        $this->dataId = $dataId;
        $this->request = collect($request);
    }
    public function countryCodeList()
    {
        if (!empty($this->response)) return $this;
        $list = CountryCode::select(
            'country_code as code',
            'name_ch as name'
        )->get();
        $list->map(function ($item, $key) use (&$list) {
            ($item['code'] === null) && $list->forget($key);
        });
        $data = $list->toArray();
        $this->response = KkdayService::response(
            '00',
            'ok',
            [
                'count' => $list->count(),
                'data' => $data
            ]
        );
        return $this;
    }

    public function runImport()
    {
        $data = $this->getStatuesFromApi();
        if ($data['result'] == "00") {
            foreach ($data['CountryAreas'] as $key => $state) {
                CountryCode::updateOrCreate(
                    ['code' => $state['Country_Code']],
                    [
                        'tel_area' => $state['Tel_Area'],
                        'name_ch' => $state['Country_Name']
                    ]
                );
            }
            $this->response = KkdayService::response('00', 'ok');
        } else {
            $this->response = KkdayService::response('999', 'api 呼叫失敗!');
        }

        return $this;
    }
    private function getStatuesFromApi()
    {
        return $this->callApi('get', 'v3/Product/QueryCountryCode')->getBody();
    }

    public function getCountryCityCode()
    {
        $country_selects = collect(['id', 'country_code as code', 'name_ch as name']);
        $city_selects = collect(['id', 'country_id', 'code', 'name']);
        // 可取得使用者資訊的人，則顯示出使用狀態設定。
        if (!empty(auth()->user()->id)) {
            $country_selects
                ->push('id as country_id')
                ->push('use');
            $city_selects
                ->push('id as city_id')
                ->push('use');
        }

        $country_selects = $country_selects->toArray();
        $city_selects = $city_selects->toArray();

        $data = CountryCode::select($country_selects)
            ->with(['cityLists' => function ($query) use (&$city_selects) {
                $query->select($city_selects);
                ($this->request['city_state'] === false) && $query->where('use', '=', 0);
                ($this->request['city_state'] === true) && $query->where('use', '=', 1);
                (empty(auth()->user()->id)) && $query->where('use', '=', 1);
                $query->orderBy('sort');
            }]);
        $data->where('country_code', '!=', null);
        ($this->request['country_state'] === false) && $data = $data->where('use', '=', 0);
        ($this->request['country_state'] === true) && $data = $data->where('use', '=', 1);
        (empty(auth()->user()->id)) && $data->where('use', '=', 1);
        $data->orderBy('sort');
        $data = $data->get()->toArray();
        foreach ($data as $country_key => $country) {
            foreach ($country['city_lists'] as $city_key => $city) {

                unset($data[$country_key]['city_lists'][$city_key]['country_id']);
                unset($data[$country_key]['city_lists'][$city_key]['id']);
            }
            // 移除不必要參數
            unset($data[$country_key]['id']);
        }
        $this->response = KkdayService::response('00', 'ok', $data);
        return $this;
    }

    public function runValidate($method)
    {
        // DB::enableQueryLog();
        switch ($method) {
            case 'settingUse':
                $rules = [
                    'country_id' => 'required|exists:*.kkday_country_codes,id',
                    'use' => 'required|boolean',
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

    public function settingUse()
    {
        if (!empty($this->response)) return $this;
        $country = CountryCode::find($this->request['country_id']);
        $country->use = $this->request['use'];
        $country->save();
        $this->response = KkdayService::response('00', 'ok');
        return $this;
    }

    public function settingSort()
    {
        if (!empty($this->response)) return $this;
        $count = CountryCode::count();
        // 全部變更為0
        CountryCode::where('id', '!=', 0)->update(['sort' => $count]);
        // DB::table('kkday_cat_main_keys')->update(['sort' => '0']);
        // 開始更新
        $data = $this->request['data'];
        foreach ($data as $key => $value) {
            $main_key = CountryCode::find($value);
            $main_key->sort = ($key + 1);
            $main_key->save();
        }
        $this->response = KkdayService::response('00', 'ok');
        return $this;
    }
}
