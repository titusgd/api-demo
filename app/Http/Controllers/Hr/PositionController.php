<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ValidatorTrait;

use App\Models\Hr\Position;
use App\Services\Hr\PositionService;

class PositionController extends Controller
{
    private $service;

    use ValidatorTrait;

    function __construct()
    {
        $this->service = new PositionService;
    }

    public function index(){

        return (new PositionService())->index();
    }

    public function store(Request $request)
    {

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
        $valid = $this->validData($req, $this->service->validationRules, $this->service->validationMsg);
        if ($valid) {
            return $valid;
        }

        return $this->service->update($req, $id);
    }

    public function destroy($id){

        return $this->service->delete($id);
    }

}
