<?php

namespace App\Services\Hr;

use App\Services\Service;

use App\Models\Hr\HumanResource;
use App\Models\Hr\HumanResourceOffice;

/**
 * Class HROfficeService.
 */
class HROfficeService extends Service
{

    public $validationRules = [
        'email' => 'string',
        'tel' => 'string',
        "extension" => 'string',
        "fax" => 'string',
    ];

    public $validationMsg = [
        'email.string' => '01 email',
        'tel.string'     => "01 tel",
        'extension.string' => "01 extension",
        "fax.string" => "01 fax",
    ];

    public function update($req, $id)
    {

        try {
            $hr = HumanResource::find($id);
            if($hr){

                $email = $req['email'] ?? '';
                $tel = $req['tel'] ?? '';
                $extension = $req['extension'] ?? '';
                $fax = $req['fax']?? '';

                $data = HumanResourceOffice::updateOrCreate(
                    [
                        'hr_id' => $id,
                    ],
                    [
                        'email' => $email,
                        'tel' => $tel,
                        'extension' => $extension,
                        'fax' => $fax,
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
