<?php

namespace App\Services\Hr;


use App\Services\Service;

use App\Models\Hr\HumanResource;
use App\Models\Hr\HumanResourceOther;

/**
 * Class HROtherService.
 */
class HROtherService extends Service
{
    public $validationRules = [
        'disabilityIdentification' => 'boolean',
        'salesPerformance' => 'boolean',
        'punchIn' => 'boolean',
        'serviceArea.id' => 'integer',
        'serviceArea.code' => 'string',
        'serviceArea.name' => 'string',
    ];

    public $validationMsg = [
        'disabilityIdentification.boolean' => '01 disabilityIdentification',
        'salesPerformance.boolean' => '01 salesPerformance',
        'punchIn.boolean' => '01 punchIn',
        'serviceArea.id.integer' => '01 serviceArea_id',
        'serviceArea.code.string' => '01 serviceArea_code',
        'serviceArea.mame.string' => '01 serviceArea_mame',
    ];

    public function update($req, $id){

        try {
            $hr = HumanResource::find($id);
            if($hr){

                $disabilityIdentification = $req['disabilityIdentification'];
                $salesPerformance = $req['salesPerformance'];
                $punchIn = $req['punchIn'];
                $serviceArea_id = $req['serviceArea']['id'] ?? '';
                $serviceArea_code = $req['serviceArea']['code'] ?? '';
                $serviceArea_name = $req['serviceArea']['name'] ?? '';

                $data = HumanResourceOther::updateOrCreate(
                    [
                        'hr_id' => $id,
                    ],
                    [
                        'disability_identification' => $disabilityIdentification,
                        'sales_performance' => $salesPerformance,
                        'service_area_id' => $serviceArea_id,
                        'service_area_name' => $serviceArea_name,
                        'service_area_code' => $serviceArea_code,
                        'punch_in' => $punchIn,
                    ]);

                return Service::response('00', 'OK', '');

            } else {

                return Service::response('999', '', 'hr not exist');
            }
        } catch (\Exception $e) {
            $message  = 'Exception Message: '   . $e->getMessage();
            $message .= '<br>Exception Code: '  . $e->getCode();
            $message .= '<br>Exception String: ' . $e->__toString();

            return Service::response('999', '', $message);
        }
    }

}
