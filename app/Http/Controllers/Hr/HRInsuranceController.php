<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ValidatorTrait;

use App\Models\Hr\HumanResourceInsurance;
use App\Services\Hr\HRInsuranceService;

class HRInsuranceController extends Controller
{
    private $service;

    use ValidatorTrait;

    function __construct()
    {
        $this->service = new HRInsuranceService;
    }

    public function update(Request $request, $id){
        $req = $request->all();

        if(empty($req['change']['enrollment'])){
            unset($req['change']['enrollment']);
        }
        if(empty($req['change']['withdrawal'])){
            unset($req['change']['withdrawal']);
        }
        if(empty($req['group']['enrollment'])){
            unset($req['group']['enrollment']);
        }
        if(empty($req['group']['withdrawal'])){
            unset($req['group']['withdrawal']);
        }
        if(empty($req['laborHealth']['enrollment'])){
            unset($req['laborHealth']['enrollment']);
        }
        if(empty($req['laborHealth']['withdrawal'])){
            unset($req['laborHealth']['withdrawal']);
        }
        if(empty($req['labor']['amount'])){
            unset($req['labor']['amount']);
        }
        if(empty($req['labor']['deductible'])){
            unset($req['labor']['deductible']);
        }
        if(empty($req['labor']['pensio'])){
            unset($req['labor']['pensio']);
        }
        if(empty($req['health']['amount'])){
            unset($req['health']['amount']);
        }
        if(empty($req['health']['deductible'])){
            unset($req['health']['deductible']);
        }
        if(empty($req['health']['family'])){
            unset($req['health']['family']);
        }
        if(empty($req['appropriation']['company'])){
            unset($req['appropriation']['company']);
        }
        if(empty($req['appropriation']['self'])){
            unset($req['appropriation']['self']);
        }

        // 驗證資料
        $valid = $this->validData($req, $this->service->validationRules, $this->service->validationMsg);
        if ($valid) {
            return $valid;
        }

        return $this->service->update($req, $id);
    }
}
