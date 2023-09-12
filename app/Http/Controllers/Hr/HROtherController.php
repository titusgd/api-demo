<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ValidatorTrait;

use App\Models\Hr\HumanResourceOther;
use App\Services\Hr\HROtherService;

class HROtherController extends Controller
{
    private $service;

    use ValidatorTrait;

    function __construct()
    {
        $this->service = new HROtherService;
    }

    public function update(Request $request, $id){

        $req = $request->all();

        if(empty($req['serviceArea']['id'])){
            unset($req['serviceArea']['id']);
        }
        if(empty($req['serviceArea']['code'])){
            unset($req['serviceArea']['code']);
        }
        if(empty($req['serviceArea']['name'])){
            unset($req['serviceArea']['name']);
        }

        // 驗證資料
        $valid = $this->validData($req, $this->service->validationRules, $this->service->validationMsg);
        if ($valid) {
            return $valid;
        }

        return $this->service->update($req, $id);
    }
}
