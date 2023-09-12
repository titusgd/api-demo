<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ValidatorTrait;

use App\Services\Hr\HRService;


class HRController extends Controller
{
    private $service;

    use ValidatorTrait;

    function __construct()
    {
        $this->service = new HRService;
    }

    public function index(Request $request){

        $data = json_decode($request->get('data') ?? '', true);

        $hr = (new HRService())->index($data);

        return $hr;
    }

    public function store(Request $request){

        $req = $request->all();
        // 驗證資料
        $valid = $this->validData($req, $this->service->validationRules, $this->service->validationMsg);
        if ($valid) {
            return $valid;
        }

        return $this->service->create($req);
    }

    public function update(){


    }

    public function delete(){

    }

    public function update_position(Request $request, $id){

        $req = $request->all();
        // 驗證資料
        $valid = $this->validData($req, $this->service->validationRules_posiion, $this->service->validationMsg_position);
        if ($valid) {
            return $valid;
        }

        return (new HRService())->update_position($req, $id);

    }

    // Test
    function getHrDetail(){

        $hr = (new HRService())->detail(10);

        return response()->json(
            [
                'status' => 'success',
                'message' => $hr
            ]);
    }
}
