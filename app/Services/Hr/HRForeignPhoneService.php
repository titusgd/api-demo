<?php

namespace App\Services\Hr;


use App\Services\Service;

use App\Models\Hr\HumanResource;
use App\Models\Hr\HumanResourceForeignPhone;

/**
 * Class HRForeignPhoneService.
 */
class HRForeignPhoneService extends Service
{
    public $validationRules = [
        'id' => 'integer|required',
        'country' => 'string|required',
        'phone' => 'string|required'
    ];

    public $validationMsg = [
        'id.integer' => '01 id',
        'id.required' => '01 id',
        'country.string'     => "01 country",
        'country.required'   => "01 country",
        'phone.string'    => "01 phone",
        'phone.required'     => "01 phone",
    ];

    public $validationRules_update = [
        'country' => 'string',
        'phone' => 'string'
    ];

    public $validationMsg_update = [
        'country.string'     => "01 country",
        'phone.string'    => "01 phone",
    ];

    public function index($data){

        if($data){
            $data = HumanResourceForeignPhone::where('hr_id', $data['id'])->get();
        } else {
            $data = HumanResourceForeignPhone::all();
        }

        $res = [];
        foreach($data as $key => $value){
            $res[$key] = [
                "id" => $value['id'],
                "country" => $value['country'],
                "phone" => $value['phone'],

            ];
        }

        return Service::response('00', 'OK', $res);
    }

    public function create($req){

        $hr = HumanResource::find($req['id']);
        if(!$hr){
            return Service::response('999', '', 'hr not exist');
        }

        try {

            $id = $req['id'];
            $country = $req['country'] ?? '';
            $phone = $req['phone'] ?? '';

            HumanResourceForeignPhone::create(
                [
                    'hr_id' => $id,
                    'country' => $country,
                    "phone" => $phone,
                ]);

            return Service::response('00', 'OK', '');


        } catch (\Exception $e) {
            $message  = 'Exception Message: '   . $e->getMessage();
            $message .= '<br>Exception Code: '  . $e->getCode();
            $message .= '<br>Exception String: ' . $e->__toString();

            return Service::response('999', '', $message);
        }
    }

    public function update($req, $id){

        try {

            $hr = HumanResource::find($id);

            $check_id = [];
            foreach($req['data'] as $key => $value) {
                array_push($check_id, $value['id']);
            }

            if(count($check_id) > 0){
                HumanResourceForeignPhone::where('hr_id', $hr->id)
                    ->whereNotIn('id', $check_id)
                    ->delete();

                $cer = HumanResourceForeignPhone::where('hr_id', $hr->id)
                    ->whereIn('id', $check_id)
                    ->get();

            } else {
                $cer = HumanResourceForeignPhone::where('hr_id', $hr->id)->get();
            }

            $cer = HumanResourceForeignPhone::where('hr_id', $hr->id)->get();;
            if($cer){

                foreach($req['data'] as $value){
                    HumanResourceForeignPhone::updateOrCreate(
                        [
                            'id' => $value['id']
                        ],
                        [
                        'country' => $value['country'],
                        'phone' => $value['phone']
                    ]);
                }
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

    public function delete($id){

        HumanResourceForeignPhone::where('id', $id)->delete();

        return Service::response('00', 'OK', '');
    }

}
