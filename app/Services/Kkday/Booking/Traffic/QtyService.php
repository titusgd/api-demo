<?php

namespace App\Services\Kkday\Booking\Traffic;

use App\Services\Kkday\Booking\Traffic\AnalyzeService;

class QtyService extends AnalyzeService
{
    private $rules = [
        'traffic_type' => ['string'],
        'CarPsg_adult' => ['string'],
        'CarPsg_child' => ['string'],
        'CarPsg_infant' => ['string'],
        'SafetySeat_sup_child' => ['string'],
        'SafetySeat_sup_infant' => ['string'],
        'SafetySeat_self_child' => ['string'],
        'SafetySeat_self_infant' => ['string'],
        'Luggage_carry' => ['string'],
        'Luggage_check' => ['string'],
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
            //     $temp_list = collect($data[$temp_key[0]]['list']);
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
            //         $rules[$key][]=$temp_str;
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
