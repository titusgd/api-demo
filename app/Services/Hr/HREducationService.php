<?php

namespace App\Services\Hr;

use Illuminate\Http\Request;

use App\Services\Service;

use App\Models\Hr\HumanResource;
use App\Models\Hr\HumanResourceEducation;

/**
 * Class HREducationService.
 */
class HREducationService extends Service
{
    public $validationRules = [
        'highest' => 'string',
        'department' => 'string'
    ];

    public $validationMsg = [
        'highest.string' => '01 highest',
        'department.string'     => "01 department",
    ];

    public function update($req, $id){

        $hr = HumanResource::find($id);
        if(!$hr){
            return Service::response('999', '', 'hr not exist');
        }

        try {

            HumanResourceEducation::updateOrCreate(
                [
                    'hr_id' => $id,
                ],
                [
                    'highest' => $req['highest'] ?? '',
                    'department' => $req['department'] ?? '',
                ]);

            return Service::response('00', 'OK', '');
            
        } catch (\Exception $e) {
            $message  = 'Exception Message: '   . $e->getMessage();
            $message .= '<br>Exception Code: '  . $e->getCode();
            $message .= '<br>Exception String: ' . $e->__toString();

            return Service::response('999', '', $message);
        }
    }
}
