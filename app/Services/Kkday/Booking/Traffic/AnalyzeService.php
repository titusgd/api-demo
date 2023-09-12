<?php

namespace App\Services\Kkday\Booking\Traffic;

class AnalyzeService
{

    private $data;

    private $is_require = [];
    private $ref_source = [];
    private $is_pickup_used = [];
    function __construct()
    {
        $this->data = collect();
        $this->is_require = collect();
        $this->ref_source = collect();
        $this->is_pickup_used = collect();
    }
    public function getData(): array
    {
        return $this->data->toArray();
    }
    public function setData($data): self
    {
        $this->data = $data;
        return $this;
    }
    // 接送資料需要此欄位
    public function setIsPickupUsed($is_pickup_used): self
    {
        $this->is_pickup_used = $is_pickup_used;
        return $this;
    }
    public function getIsPickupUsed(): array
    {
        return $this->is_pickup_used->toArray();
    }
    // 必填欄位
    public function getIsRequire(): array
    {
        return $this->is_require->toArray();
    }
    public function setIsRequire($is_require): self
    {
        $this->is_require = $is_require;
        return $this;
    }

    // 參考依賴選項
    public function setRefSource($ref_source): self
    {
        $this->ref_source = $ref_source;
        return $this;
    }

    public function getRefSource(): array
    {
        return $this->ref_source->toArray();
    }
    // 欄位解析
    public function defaultAnalyze(string $key_word, array $data): array
    {
        $temp = [];
        (!empty($data['type'])) && $temp['type'] = $data['type'];

        (!empty($data['list_option'])) && $temp['list'] = $data['list_option'];

        if (!empty($data['ref_source'])) {
            $temp['type'] = 'string';
            $temp['ref_source'] = $data['ref_source'];
            $this->ref_source->put($key_word, $data['ref_source']);
        };
        $this->data->put($key_word, $temp);
        if($key_word==['location_list']||$key_word==['time_list']){
            return $temp;
        }
        if (!empty($data['is_require'])) {
            ($data['is_require'] == 'True' || $data['is_require'] == 'true') && $this->is_require->push($key_word);
        }
        if (!empty($data['is_pickup_used'])) {
            ($data['is_pickup_used'] == 'True' || $data['is_pickup_used'] == 'true') && $this->is_pickup_used->push($key_word);
        }
        return $temp;
    }

    public function settingRules(string $key_word = '', array $data)
    {
        $temp = collect([]);
        foreach ($data as $key => $value) {
            $temp->put(
                (!empty($key_word)) ? $key_word . '.*.' . $key : $key,
                implode('|', $value)
            );
        }

        return $temp->toArray();
    }
}
