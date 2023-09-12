<?php

namespace App\Services\Kkday;

use App\Services\Kkday\KkdayService;
use App\Traits\ResponseTrait;
use App\Models\Kkday\CityCode;
use App\Models\Kkday\CountryCode;
use App\Traits\RulesTrait;
use Illuminate\Support\Facades\DB;
class CityService extends KkdayService
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
    public function cityList()
    {
        $columns = [
            // 'id',
            'name',
            'code',
            'country_code',
            // 'country_id'
        ];
        $this->response = CityCode::select($columns)
            ->get()
            ->toArray();
        return $this;
    }

    public function runImport()
    {
        if (!empty($this->response)) return $this;
        $data = $this->getStatuesFromApi();

        if ($data['result'] == "00") {
            foreach ($data['Countries'] as $key => $country) {
                $country_id = CountryCode::select('id')
                    ->where('name_ch', '=', $country['Country_Name'])
                    ->first();

                if (!empty($country_id)) {
                    CountryCode::where('id', '=', $country_id['id'])
                        ->update(['country_code' => $country['Country_Code']]);
                } else {
                    $country_id = new CountryCode();
                    $country_id->tel_area = '';
                    $country_id->code = '';
                    $country_id->name_ch = $country['Country_Name'];
                    $country_id->country_code = $country['Country_Code'];
                    $country_id->save();
                }
                foreach ($country['Cities'] as $key2 => $city) {
                    CityCode::updateOrCreate(
                        ['code' => $city['City_Code']],
                        [
                            'name' => $city['City_Name'],
                            'country_code' => $country['Country_Code'],
                            'country_id' => $country_id['id'],
                        ]
                    );
                }
            }
            $this->response = KkdayService::response('00', 'ok');
        } else {
            $this->response = KkdayService::response('999', 'api 呼叫失敗!');
        }

        return $this;
    }
    private function getStatuesFromApi()
    {
        return $this->callApi('get', 'v3/Product/QueryCityList')->getBody();
    }

    public function runValidate($method)
    {
        // DB::enableQueryLog();
        switch ($method) {
            case 'settingUse':
                $rules = [
                    'city_id' => 'required|exists:*.kkday_country_codes,id',
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
        $city = CityCode::find($this->request['city_id']);
        $city->use = $this->request['use'];
        $city->save();
        $this->response = KkdayService::response('00', 'ok');
        return $this;
    }

    public function settingSort()
    {
        if (!empty($this->response)) return $this;
        // dd($this->request['country_id']);
        // DB::
        $count = CityCode::where('country_id','=',$this->request['country_id'])->count();
        // 全部變更為0
        CityCode::where('id', '!=', $this->request['country_id'])->update(['sort' => $count]);
        // DB::table('kkday_cat_main_keys')->update(['sort' => '0']);
        // 開始更新
        $data = $this->request['data'];
        foreach ($data as $key => $value) {
            $main_key = CityCode::find($value);
            $main_key->sort = ($key + 1);
            $main_key->save();
        }
        $this->response = KkdayService::response('00', 'ok');
        return $this;
    }
}
