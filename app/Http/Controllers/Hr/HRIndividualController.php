<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ValidatorTrait;

use App\Models\Hr\HumanResource;
use App\Models\Hr\HumanResourceIndividual;

use App\Services\Hr\HRIndividualService;

class HRIndividualController extends Controller
{
    private $service;

    use ValidatorTrait;

    function __construct()
    {
        $this->service = new HRIndividualService;
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

}
