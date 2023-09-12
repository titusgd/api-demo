<?php

namespace App\Services\Hr;

use App\Services\Service;

use App\Models\Hr\HumanResource;
use App\Models\Hr\HumanResourceEmergencyContact;

/**
 * Class HREmergencyContactService.
 */
class HREmergencyContactService extends Service
{
    public $validationRules = [
        'name' => 'string',
        'relation' => 'string',
        'model' => 'string',
        'tel' => 'string',
    ];

    public $validationMsg = [
        'name.string' => '01 name',
        'relation.string' => '01 relation',
        'model.string' => '01 model',
        'tel.string' => '01 tel',
    ];

    public function update($req, $id)
    {

        $hr = HumanResource::find($id);

        if (!$hr) {
            return Service::response('999', '', 'hr not exist');
        }
        try {

            $name = $req['name'] ?? '';
            $relation = $req['relation'] ?? '';
            $mobile = $req['mobile'] ?? '';
            $tel = $req['tel'] ?? '';

            HumanResourceEmergencyContact::updateOrCreate(
                [
                    'hr_id' => $id,
                ],
                [
                    'name' => $name,
                    'relation' => $relation,
                    'mobile' => $mobile,
                    'tel' => $tel,
                ]
            );

            return Service::response('00', 'OK', '');
        } catch (\Exception $e) {
            $message  = 'Exception Message: '   . $e->getMessage();
            $message .= '<br>Exception Code: '  . $e->getCode();
            $message .= '<br>Exception String: ' . $e->__toString();

            return Service::response('999', '', $message);
        }
    }
}
