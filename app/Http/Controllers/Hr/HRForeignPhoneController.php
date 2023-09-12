<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ValidatorTrait;

use App\Models\Hr\HumanResourceForeignPhone;
use App\Services\Hr\HRForeignPhoneService;

class HRForeignPhoneController extends Controller
{
    private $service;

    use ValidatorTrait;

    function __construct()
    {
        $this->service = new HRForeignPhoneService;
    }

    public function index(Request $request){

        $data = json_decode($request->get('data') ?? '', true);

        $data = (new HRForeignPhoneService())->index($data);

        return $data;
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

    public function update(Request $request, $id){

        $req = $request->all();
        // 驗證資料
        $valid = $this->validData($req, $this->service->validationRules_update, $this->service->validationMsg_update);
        // $valid = $this->validData($req['content'], $this->service->validationRules2, $this->service->validationMsg2);
        if ($valid) {
            return $valid;
        }

        return $this->service->update($req, $id);
    }

    // public function destroy($id){

    //     return $this->service->delete($id);
    // }
}
