<?php

namespace App\Services\Hr;

use Illuminate\Http\Request;

use App\Services\Service;
use Illuminate\Support\Facades\Http;

use App\Models\Hr\Position;
use App\Models\Hr\HumanResource;

use Exception;

/**
 * Class Position.
 */
class PositionService extends Service
{

    public $validationRules = [
        'rank'    => 'required|integer',
        'name'   => 'required|string',
    ];

    public $validationMsg = [
        'rank.required'   => "01 rank",
        'rank.integer'     => "01 rank",
        'name.required'  => "01 name",
        'name.string'    => "01 name",
    ];

    public function index()
    {
        $result = Position::orderBy('rank', 'asc')->get();
        foreach($result as $key => $value) {

            $result[$key]['count'] = HumanResource::get_position_count($value['id']);
        }

        return Service::response('00', 'OK', $result);
    }

    public function create($req){

        try {
            $create = Position::create([
                "rank"   => $req["rank"],
                "name"  => $req["name"],
            ]);

            return Service::response('00', 'OK', '');

        } catch (Exception $e){
            $message  = 'Exception Message: '   . $e->getMessage();
            $message .= '<br>Exception Code: '  . $e->getCode();
            $message .= '<br>Exception String: ' . $e->__toString();

            return Service::response('999', '', $message);
        }

    }

    public function update($req, $id)
    {

        try {

            $data = Position::where('id', $id)->update($req);

            return Service::response('00', 'OK', '');

        } catch (Exception $e){
            $message  = 'Exception Message: '   . $e->getMessage();
            $message .= '<br>Exception Code: '  . $e->getCode();
            $message .= '<br>Exception String: ' . $e->__toString();

            return Service::response('999', '', $message);
        }

    }

    public function delete($id)
    {
        $data = Position::find($id);
        if($data){
            Position::where('id', $id)->delete();

            return Service::response('00', 'OK', '');
        } else {
            return Service::response('999', '', 'position id not exists');
        }
    }
}
