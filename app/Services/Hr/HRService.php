<?php

namespace App\Services\Hr;

use Illuminate\Http\Request;

use App\Services\Service;
use App\Models\Hr\Organize;
use App\Models\Hr\Position;
use App\Models\Hr\HumanResource;
use App\Models\Hr\HumanResourceAborigine;
use App\Models\Hr\HumanResourceCertificate;
use App\Models\Hr\HumanResourceEducation;
use App\Models\Hr\HumanResourceEmergencyContact;;
use App\Models\Hr\HumanResourceExperience;
use App\Models\Hr\HumanResourceForeignPhone;
use App\Models\Hr\HumanResourceIndividual;
use App\Models\Hr\HumanResourceInsurance;
use App\Models\Hr\HumanResourceOffice;
use App\Models\Hr\HumanResourceOther;
use App\Models\Hr\HumanResourcePosition;
use App\Models\Hr\HumanResourceTest;;


use Exception;
use Illuminate\Contracts\Pipeline\Hub;

/**
 * Class HumanResourceService.
 */
class HRService extends Service
{
    public $validationRules = [
        'organize_id' => 'required|integer',
        'name'   => 'required|string',
        'code'    => 'required|string',
        'position_id' => 'required|integer',
    ];

    public $validationMsg = [
        'organize_id.integer' => '01 organize_id',
        'organize_id.required' => '01 organize_id',
        'name.required'   => "01 name",
        'name.string'     => "01 name",
        'code.required'  => "01 code",
        'code.string'    => "01 code",
        'position_id.required'   => "01 position_id",
        'position_id.ingeter'     => "01 position_id",
    ];

    public $validationRules_posiion = [
        'position_id' => 'required|integer',
    ];

    public $validationMsg_position = [
        'position_id.integer' => '01 position_id',
        'position_id.required' => "01 position_id",
    ];

    public function index($data)
    {
        if($data){
            $hr = HumanResource::select('human_resources.*')->where('organize_id', $data['id'])->join('positions as p', 'p.id', '=', 'human_resources.position_id')->get();
        } else {
            $hr = HumanResource::select('human_resources.*')->join('positions as p', 'p.id', '=', 'human_resources.position_id')->get();
        }

        $res = [];
        foreach ($hr as $key => $value) {

            $position = Position::where('id', $value['position_id'])->first();
            $title = '';
            if ($position) {
                $title = $position->name;
            }

            $res[$key] = [
                'id' => $value['id'],
                'code' => $value['code'],
                'name' => $value['chinese_name'],
                'title' => $title
            ];
        }

        return Service::response('00', 'OK', $res);
    }

    public function create($req)
    {

        $org = Organize::find($req['organize_id']);
        if (!$org) {
            return Service::response('999', '', 'organize id can not found');
        }

        $rank = Position::where('id', $req['position_id'])->first();;
        if (!$rank) {
            return Service::response('999', '', 'position id can not found');
        }

        try {


            $hr = HumanResource::create(
                [
                    'organize_id' => $req['organize_id'],
                    'code' => $req['code'],
                    'chinese_name' => $req['name'],
                    'english_name' => '',
                    'position_id' => $rank->id,
                    'flag' => '',
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

    public function update_position($req, $id)
    {

        try {

            // 檢查 position 是否存在
            $position = Position::find($req['position_id']);
            if (!$position) {
                return Service::response('999', '', 'position id not found');
            }

            // 檢查 hr 是否存在
            $hr = HumanResource::find($id);
            if ($hr) {
                $hr->position_id = $req['position_id'];
                $hr->save();
                return Service::response('00', 'OK', '');
            } else {
                return Service::response('999', '', 'hr not found');
            }
        } catch (\Exception $e) {
            $message  = 'Exception Message: '   . $e->getMessage();
            $message .= '<br>Exception Code: '  . $e->getCode();
            $message .= '<br>Exception String: ' . $e->__toString();

            return Service::response('999', '', $message);
        }
    }

    public function detail($id){

        $res = HumanResource::get_detail($id);

        return Service::response('00', 'OK', $res);
    }

}
