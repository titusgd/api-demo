<?php

namespace App\Services\Apidoc;

use App\Services\Service;
use Illuminate\Support\Facades\DB;
use Exception;

use App\Models\Apidoc;
use App\Models\ApidocProject;
use App\Models\ApidocApi;

class ApidocApiService extends Service
{

    public $validationRules = [
        'id' => 'required|integer',
        'req' => 'required|array',
        'req.*.id' => 'required|integer',
        'req.*.apidocid' => 'required|string',
        'req.*.parameter' => 'required|string',
        'req.*.level' => 'required|integer',
        'req.*.type' => 'required|string',
        'req.*.required' => 'required|boolean',
        'req.*.name' => 'required|string',
        'req.*.note' => 'required|string',
        'res' => 'required|array',
        'res.*.id' => 'required|integer',
        'res.*.apidocid' => 'required|string',
        'res.*.parameter' => 'required|string',
        'res.*.level' => 'required|integer',
        'res.*.type' => 'required|string',
        'res.*.required' => 'required|boolean',
        'res.*.name' => 'required|string',
        'res.*.note' => 'required|string',
        'reqjson'    => 'required|string',
        'resjson'    => 'required|string',
    ];

    public $validationMsg = [
        'id.required' => '01 id',
        'id.integer' => '01 id',
        "req.required"   => "01 req",
        "req.array"     => "01 req",
        "res.required"   => "01 res",
        "res.array"     => "01 res",
        "reqjson.required" => "01 resjson",
        "reqjson.string" => "01 resjson",
        "resjson.required" => "01 reqjson",
        "resjson.string" => "01 reqjson",
    ];

    public $validationRules_index = [
        'id' => 'required|integer',
    ];

    public $validationMsg_index = [
        'id.required' => '01 id',
        'id.integer' => '01 id',
    ];

    public function index($req){

        $project_id = $req['id'];

        $res = ApidocApi::where('project_id', $project_id)->get();
        $result = [];
        foreach($res as $key => $value){
            $data = [
                'req' => json_decode($value['req']),
                'res' => json_decode($value['res']),
                'reqjson' => $value['reqjson'],
                'resjson' => $value['resjson'],
            ];

            array_push($result, $data);
        }

        return Service::response('00', 'OK', $result);
    }

    public function create($request){

        $project_id = $request['id'];

        $project = ApidocProject::find($project_id);

        if(!$project){

            return Service::response('999', '', 'project_id error has occur');

        } else {
            try {

                $res = $request['res'];
                $req = $request['req'];
                $resjson = $request['resjson'];
                $reqjson = $request['reqjson'];

                $res = json_encode($res);
                $req = json_encode($req);

                ApidocApi::create([
                    'project_id' => $project_id,
                    'res' => $res,
                    'req' => $req,
                    'resjson' => $resjson,
                    'reqjson' => $reqjson
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

    public function update($request, $id){

        try {

            $res = $request['res'];
            $req = $request['req'];
            $resjson = $request['resjson'];
            $reqjson = $request['reqjson'];

            $res = json_encode($res);
            $req = json_encode($req);

            $apidoc_project = ApidocApi::find($id);
            $apidoc_project->res = $res;
            $apidoc_project->req = $req;
            $apidoc_project->resjson = $resjson;
            $apidoc_project->reqjson = $reqjson;
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

        $project = ApidocApi::find($id);
        if($project){
            ApidocApi::where('id', $id)->delete();

            return Service::response('800', 'OK', '');
        } else {
            return Service::response('999', '', 'project id not exists');
        }
    }

}
