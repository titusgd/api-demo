<?php

namespace App\Services\Apidoc;

use App\Services\Service;
use Illuminate\Support\Facades\DB;
use Exception;

use App\Models\Apidoc;
use App\Models\ApidocProject;
use App\Models\ApidocApi;

use Illuminate\Support\Carbon;

class ApidocProjectService extends Service
{
    public $validationRules = [
        'id' => 'required|integer',
        'name'    => 'required|string',
        'api'    => 'required|string',
        'method'    => 'required|string',
    ];

    public $validationMsg = [
        'id.required' => '01 apidoc_id',
        'id.integer' => '01 apidoc_id',
        "name.required"   => "01 name",
        "name.string"     => "01 name",
        "api.required" => "01 api",
        "api.string" => "01 api",
        "method.required" => "01 method",
        "method.string" => "01 method",
    ];

    public $validationRules_index = [
        'id' => 'required|integer',
    ];

    public $validationMsg_index = [
        'id.required' => '01 apidoc_id',
        'id.integer' => '01 apidoc_id',
    ];

    public function index($req){

        $apidoc_id = $req['id'];

        $res = ApidocProject::where('apidoc_id', $apidoc_id);

        $res = $res->get();
        $result = [];
        foreach($res as $key => $value){
            $c_datetime = new Carbon($value['created_at']);
            $date_time = $c_datetime->toDateTimeString();
            $explode_created_datetime = explode(' ', $date_time);

            $data = [
                "id" => $value['id'],
                'apidoc_id' => $value['apidoc_id'],
                'sn' => $value['id'],
                'group' => $value['group'],
                'api' => $value['api'],
                'method' => $value['method'],
                'date' => [$explode_created_datetime[0], $explode_created_datetime[1]]
            ];

            array_push($result, $data);
        }

        return Service::response('00', 'OK', $result);
    }

    public function create($req){

        $apidoc_id = $req['id'];

        $apidoc = Apidoc::find($apidoc_id);

        if(!$apidoc){

            return Service::response('999', '', 'apidoc_id error has occur');

        } else {
            try {

                $sn = $req['sn'] ?? 0;
                $group = $req['group'] ?? '';
                $name = $req['name'];
                $api = $req['api'] ?? [];
                $method = $req['method'];

                ApidocProject::create([
                    'apidoc_id' => $apidoc_id,
                    'name' => $name,
                    'group' => $group,
                    'sn' => $sn,
                    'api' => $api,
                    'method' => $method
                ]);

                return Service::response('800', 'OK', '');

            } catch (Exception $e){
                $message  = 'Exception Message: '   . $e->getMessage();
                $message .= '<br>Exception Code: '  . $e->getCode();
                $message .= '<br>Exception String: ' . $e->__toString();

                return Service::response('999', '', $message);
            }
        }


    }

    public function update($req, $id){

        try {
            $sn = $req['sn'] ?? 0;
            $group = $req['group'] ?? '';
            $name = $req['name'];
            $api = $req['api'] ?? [];
            $method = $req['method'];

            $apidoc_project = ApidocProject::find($id);
            $apidoc_project->name = $name;
            $apidoc_project->sn = $sn;
            $apidoc_project->group = $group;
            $apidoc_project->name = $name;
            $apidoc_project->api = $api;
            $apidoc_project->method = $method;
            $apidoc_project->save();

            return Service::response('800', 'OK', '');

        } catch (Exception $e){
            $message  = 'Exception Message: '   . $e->getMessage();
            $message .= '<br>Exception Code: '  . $e->getCode();
            $message .= '<br>Exception String: ' . $e->__toString();

            return Service::response('999', '', $message);
        }
    }

    public function delete($id){

        $project = ApidocProject::find($id);
        if($project){
            ApidocProject::where('id', $id)->delete();

            return Service::response('800', 'OK', '');
        } else {
            return Service::response('999', '', 'project id not exists');
        }


    }


}
