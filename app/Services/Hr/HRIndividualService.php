<?php

namespace App\Services\Hr;

use Illuminate\Http\Request;

use App\Services\Service;

use App\Models\Hr\HumanResource;
use App\Models\Hr\HumanResourceIndividual;

/**
 * Class HRIndividualService.
 */
class HRIndividualService extends Service
{

    public $validationRules = [
        'code' => 'required|string',
        'name.chinese' => 'required|string'
    ];

    public $validationMsg = [
        'code.required' => '01 code',
        'code.string' => '01 code',
        'name.chinese.required'   => "01 name",
        'name.chinese.string'     => "01 name",
    ];

    public function update($req, $id)
    {

        $hr = HumanResource::find($id);
        if (!$hr) {
            return Service::response('999', '', 'hr not exist');
        }

        try {

            $hr_id = $id;
            $code = $req['code'];
            $chinese_name = $req['name']['chinese'] ?? '';
            $english_name = $req['name']['english'] ?? '';
            $uid = $req['identityCard'] ?? '';
            $blood_type = $req['bloodType'] ?? '';
            $phone = $req['phone'] ?? '';
            $contact_tel = $req['contact']['tel'] ?? '';
            $contact_address = $req['contact']['address'] ?? '';
            $home_tel = $req['householdRegistration']['tel'] ?? '';
            $home_address = $req['householdRegistration']['address'] ?? '';
            $family = $req['family'] ?? false;
            $note = $req['note'] ?? '';
            $birthday = '';
            if(!empty($req['birthday'])){
                $birthday = $this->convertDateFormat($req['birthday']);
            }

            $hr->chinese_name =  $chinese_name;
            $hr->english_name = $english_name;
            $hr->save();

            $res = HumanResourceIndividual::updateOrCreate(
                ['hr_id' => $hr_id],
                [
                    "code" => $code,
                    "uid" => $uid,
                    "blood_type" => $blood_type,
                    "birthday" => $birthday,
                    "phone" => $phone,
                    "contact_tel" => $contact_tel,
                    "contact_address" => $contact_address,
                    "home_tel" => $home_tel,
                    "home_address" => $home_address,
                    "family" => $family,
                    "note" => $note,
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
