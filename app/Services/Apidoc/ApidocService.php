<?php

namespace App\Services\Apidoc;

use App\Services\Service;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

use App\Models\Apidoc;
use App\Models\ApidocProject;
use App\Models\ApidocApi;

use Illuminate\Support\Carbon;

class ApidocService extends Service
{

    public $validationRules = [
        'name'    => 'required|string',
    ];

    public $validationMsg = [
        'name.required'   => "01 name",
        'name.string'     => "01 name",
    ];

    public function index(){

        $res = Apidoc::all();
        $result = [];
        foreach($res as $key => $value){
            $c_datetime = new Carbon($value['created_at']);
            $date_time = $c_datetime->toDateTimeString();
            $explode_created_datetime = explode(' ', $date_time);
            $data = [
                'id' => $value['id'],
                'sn' => $value['sn'],
                'name' => $value['name'],
                'link' => $value['link'],
                'owner' => $value['owner'],
                'view' => json_decode($value['view']),
                'edit' =>json_decode($value['edit'])
            ];

            array_push($result, $data);
        }

        return Service::response('00', 'ok', $result);
    }

    public function create($req){

        try {

            $sn = $req['sn'] ?? 0;
            $link = $req['link'] ?? '';
            $name = $req['name'];
            $owner = auth()->user()->id;
            $view = $req['view'] ?? [];
            $edit = $req['edit'] ?? [];

            $view = json_encode($view);
            $edit = json_encode($edit);

            Apidoc::create([
                'name' => $name,
                'sn' => $sn,
                'link' => $link,
                'owner' => $owner,
                'view' => $view,
                'edit' => $edit
            ]);

            return Service::response('800', 'OK', '');

        } catch (Exception $e){
            $message  = 'Exception Message: '   . $e->getMessage();
            $message .= '<br>Exception Code: '  . $e->getCode();
            $message .= '<br>Exception String: ' . $e->__toString();

            return Service::response('999', '', $message);
        }
    }

    public function update($req, $id){

        try {

            $apidoc = Apidoc::find($id);

            $name = $req['name'] ?? '';
            $sn = $req['sn'] ?? 0;
            $link = $req['link'] ?? '';
            $owner = auth()->user()->id;
            $view = $req['view'] ?? [];
            $edit = $req['edit'] ?? [];

            $view = json_encode($view);
            $edit = json_encode($edit);

            if($name){
                $apidoc->name = $name;
            }
            $apidoc->sn = $sn;
            $apidoc->owner = $owner;
            $apidoc->link = $link;
            $apidoc->view = $view;
            $apidoc->edit = $edit;
            $apidoc->save();

            return Service::response('800', 'OK', '');

        } catch (Exception $e){
            $message  = 'Exception Message: '   . $e->getMessage();
            $message .= '<br>Exception Code: '  . $e->getCode();
            $message .= '<br>Exception String: ' . $e->__toString();

            return Service::response('999', '', $message);
        }
    }

    public function delete($id){

        $project = Apidoc::find($id);
        if($project){
            Apidoc::where('id', $id)->delete();

            return Service::response('800', 'OK', '');
        } else {
            return Service::response('999', '', 'Apidoc ID not exists');
        }


    }

    public function sort($data){

        foreach($data as $key => $value){
            $apidoc = Apidoc::where('id', $value)->where('deleted_at', null)->update(['sn' => $key]);
        }

        return  Service::response('800', 'OK', '');
    }
}
