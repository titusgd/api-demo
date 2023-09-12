<?php

namespace App\Services\Hr;

use App\Services\Service;

use App\Models\Hr\HumanResource;
use App\Models\Hr\HumanResourceAborigine;

/**
 * Class HRAboriginrsService.
 */
class HRAboriginrsService extends Service
{
    public $validationRules = [
        'type' => 'boolean',
        'name' => 'string'
    ];

    public $validationMsg = [
        'type.boolean' => '01 type',
        'name.string'     => "01 name",
    ];


    public function update($req, $id)
    {

        $hr = HumanResource::find($id);
        if (!$hr) {
            return Service::response('999', '', 'hr not exist');
        }
        
        try {

            $data = HumanResourceAborigine::updateOrCreate(
                [
                    'hr_id' => $id,
                ],
                [
                    'type' => $req['type'] ?? '',
                    'name' => $req['name'] ?? '',
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
