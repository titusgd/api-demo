<?php

namespace App\Services\Hr;

use App\Services\Service;

use App\Models\Hr\HumanResource;
use App\Models\Hr\HumanResourceTest;


/**
 * Class HRTestService.
 */
class HRTestService extends Service
{
    public $validationRules = [
        'D' => 'integer',
        'I' => 'integer',
        "S" => 'integer',
        "C" => 'integer',
    ];

    public $validationMsg = [
        'D.integer' => '01 D',
        'I.integer' => "01 I",
        'S.integer' => "01 S",
        "C.integer" => "01 C",
    ];

    public function update($req, $id)
    {
        try {
            $hr = HumanResource::find($id);
            if($hr){

                $dominance = $req['D'] ?? 0;
                $influence = $req['I'] ?? 0;
                $steady = $req['S'] ?? 0;
                $caution = $req['C'] ?? 0;

                $data = HumanResourceTest::updateOrCreate(
                    [
                        'hr_id' => $id,
                    ],
                    [
                        'dominance' => $dominance,
                        'influence' => $influence,
                        'steady' => $steady,
                        'caution' => $caution,
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
