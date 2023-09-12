<?php

namespace App\Services\Hr;

use App\Services\Service;

use App\Models\Hr\HumanResource;
use App\Models\Hr\HumanResourceCertificate;

/**
 * Class HRCertificateService.
 */
class HRCertificateService extends Service
{
    public $validationRules = [
        'id' => 'integer',
        'name' => 'string',
        'number' => 'string',
        'place' => 'string',
        'note' => 'string',
        'type.id' => 'integer',
        'type.code' => 'string',
        'type.name' => 'string',
        'expiry_date.start' => 'date',
        'expiry_date.end' => 'date',
    ];

    public $validationMsg = [
        'id.integer' => '01 id',
        'name.string'     => "01 name",
        'number.string'   => "01 number",
        'place.string'    => "01 place",
        'note.string'     => "01 note",
        'type.id.integer' => "01 type id",
        'type.code.string' => "01 type code",
        'type.name.string' => "01 type name",
        'expiry_date.start.date' => "01 expiry_start_ date",
        'expiry_date.end.date' => "01 expiry_end_date",
    ];

    public function index($data){

        if($data){
            $items = HumanResourceCertificate::where('hr_id', (int)$data['id'])->get();
        } else {
            $items = HumanResourceCertificate::all();
        }

        $res = [];
        $idx = 0;
        foreach($items as $key => $value){

            $start = '';
            if(!empty($value['expiry_sdate'])){
                $start = $this->convertDateFormat($value['expiry_sdate']);
            }

            $end = '';
            if(!empty($value['expiry_edate'])){
                $end = $this->convertDateFormat($value['expiry_edate']);
            }


            $res[$idx] = [
                "id" => $value['id'] ?? 0,
                "name" => $value['name'] ?? '',
                "type" => [
                    "id" => $value['type_id'] ?? 0,
                    "code" => $value['type_code'] ?? '',
                    "name" => $value['type_name'] ?? '',
                ],
                "place" => $value['place'] ?? '',
                "number" => $value['number'] ?? '',
                "note" => $value['note'] ?? '',
                "expiryDate" => [
                    "start" => $start,
                    "end" => $end,
                ]
            ];

            $idx++;
        }

        return Service::response('00', 'OK', $res);
    }

    public function create($req){

        try {
            $hr = HumanResource::find($req['id']);
            if($hr){

                $id = $req['id'];
                $name = $req['name'] ?? '';
                $number = $req['number'] ?? '';
                $place = $req['place'] ?? '';
                $note = $req['note'] ?? '';
                $type_id = $req['type']['id'] ?? '';
                $type_code = $req['type']['code'] ?? '';
                $type_name = $req['type']['name'] ?? '';
                $expiry_sdate = $req['expiryDate']['start'] ?? '';
                $expiry_edate = $req['expiryDate']['end'] ?? '';

                $data = HumanResourceCertificate::create(
                    [
                        'hr_id' => $id,
                        'name' => $name,
                        "number" => $number,
                        "place" => $place,
                        "note" => $note,
                        "type_id" => $type_id,
                        "type_code" => $type_code,
                        "type_name" => $type_name,
                        "expiry_sdate" => $expiry_sdate,
                        "expiry_edate" => $expiry_edate,
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

    public function update($req, $id){

        try {

            $hr = HumanResource::find($id);

            $check_id = [];
            foreach($req['data'] as $key => $value) {
                array_push($check_id, $value['id']);
            }

            if(count($check_id) > 0){
                HumanResourceCertificate::where('hr_id', $hr->id)
                    ->whereNotIn('id', $check_id)
                    ->delete();

                $cer = HumanResourceCertificate::where('hr_id', $hr->id)
                    ->whereIn('id', $check_id)
                    ->get();

            } else {
                $cer = HumanResourceCertificate::where('hr_id', $hr->id)->get();
            }

            if($cer){
                foreach($req['data'] as $key => $value) {
                    $data = HumanResourceCertificate::updateOrCreate(
                        [
                            'id' => $value['id']],
                        [
                            'hr_id' => $hr->id,
                            'name' => $value['name'],
                            "number" => $value['number'],
                            "place" => $value['place'],
                            "note" => $value['note'],
                            "type_id" => $value['type']['id'],
                            "type_code" => $value['type']['code'],
                            "type_name" => $value['type']['name'],
                            "expiry_sdate" => $value['expiryDate']['start'],
                            "expiry_edate" => $value['expiryDate']['end'],
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

        HumanResourceCertificate::where('id', $id)->delete();

        return Service::response('00', 'OK', '');
    }

}
