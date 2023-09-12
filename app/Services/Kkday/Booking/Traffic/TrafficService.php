<?php

namespace App\Services\Kkday\Booking\Traffic;

use App\Services\Kkday\Booking\Traffic\CarService;
use App\Services\Kkday\Booking\Traffic\QtyService;
use App\Services\Kkday\Booking\Traffic\FlightService;
use App\Traits\RulesTrait;
use Illuminate\Support\Arr;

class TrafficService
{
    use RulesTrait;
    protected $data;
    private $resData;
    private $order_data, $booking_field;
    function __construct(array $order_data = null, $booking_field = null)
    {
        // 初始化
        $this->resData = collect();
        $this->order_data = $order_data['traffic'];
        $this->booking_field = $booking_field;
        $this->data = collect($this->booking_field['traffic']);
        $this->data->map(function ($item, $key) {
            $this->analyze($key, $item);
        });
    }

    public function runTrafficValidate()
    {
        $response = null;
        foreach ($this->order_data as $order_key => $order_item) {
            foreach ($order_item as $sub_key => $sub_item) {
                $response = self::validate(
                    [$sub_key => $sub_item],  // validator data
                    Arr::Dot([$sub_key => $this->resData[$sub_key]['rules']]) // rules
                );
                // $response = self::validate($sub_item,$this->resData[$sub_key]['rules']);

                if (!empty($response)) {
                    break 2;
                }
            }
        }
        return $response;
    }


    public function getResData(): array
    {
        return $this->resData->toArray();
    }

    public function setResData($resData): self
    {
        $this->resData = $resData;
        return $this;
    }

    private function analyze($key_word, $data)
    {
        switch ($key_word) {
            case 'car':
                $service = new CarService($data);
                break;
            case 'qty':
                $service = new QtyService($data);
                break;
            case 'flight':
                $service = new FlightService($data);
                break;
        }
        foreach ($data as $key => $item) {
            $service->defaultAnalyze($key, $item);
        }
        

        $this->resData->put($key_word, [
            'is_require' => $service->getIsRequire(),    // 取得 全部 is_require
            'is_pickup_used'=> $service->getIsPickupUsed(),
            // 'data' => $service->getData(),               // 取得 完整資料
            'rules' => $service->getRules()
        ]);
    }

    public function getRuleData()
    {
        return $this->resData;
    }
}
