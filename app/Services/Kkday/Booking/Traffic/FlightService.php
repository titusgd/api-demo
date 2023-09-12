<?php

namespace App\Services\Kkday\Booking\Traffic;

use App\Services\Kkday\Booking\Traffic\AnalyzeService;

class FlightService extends AnalyzeService
{
    private $rules = [
        'traffic_type' => ['string'],
        'arrival_airport' => ['string'],
        'arrival_flightType' => ['string'],
        'arrival_airlineName' => ['string'],
        'arrival_flightNo' => ['string'],
        'arrival_terminalNo' => ['boolean'],
        'arrival_visa' => ['string'],
        'arrival_date' => ['string'],
        'arrival_time' => ['string'],
        'departure_airport' => ['string'],
        'departure_flightType' => ['string'],
        'departure_airlineName' => ['string'],
        'departure_flightNo' => ['string'],
        'departure_terminalNo' => ['string'],
        'departure_date' => ['string'],
        'departure_time' => ['string'],
    ];
    public function __construct()
    {
        parent::__construct();
    }

    public function setRules($rules)
    {
        $this->rules = $rules;
    }

    public function getRulesDefault(): array
    {
        return $this->rules;
    }

    private function setRulesIsRequired()
    {
        $is_required = $this->getIsRequire();
        $ref_source = $this->getRefSource();
        $data = self::getData();

        $rules = $this->rules;
        foreach ($rules as $key => $value) {
            // 必填
            if (in_array($key, $is_required)) {
                $rules[$key] = array_merge(['required'], $rules[$key]);
            }else{
                $rules[$key] = array_merge(['nullable'], $rules[$key]);
            }
            // // 依賴選項
            // if (!empty($ref_source[$key])) {
                
            //     $temp_key = collect(explode('.', $ref_source[$key]));
            //     // dd(
            //     //     $temp_key,
            //     //     $is_required,
            //     //     $ref_source,
            //     //     $data,
            //     // );
            //     if(!empty($data[$temp_key[0]]['list'])){
            //         $temp_list = collect($data[$temp_key[0]]['list']);    
            //     }else{
            //         $temp_list = collect($data[$key]['list']);
            //     }
                
            //     $temp_str = 'in:';
            //     if ($temp_key->count() == 2) {
            //         $temp_list->map(function ($item, $key) use (&$temp_key, &$temp_str) {
            //             if (!empty($item[$temp_key[1]])) {
            //                 $temp_str .= $item[$temp_key[1]] . ',';
            //             }
            //         });
            //         if (substr($temp_str, -1) === ',') {
            //             $temp_str = substr($temp_str, 0, strlen($temp_str) - 1);
            //         }
            //         $rules[$key][] = $temp_str;
            //     }
            // }
        }
        return $rules;
    }
    public function getRules()
    {
        return self::settingRules('', $this->setRulesIsRequired());
    }
}
