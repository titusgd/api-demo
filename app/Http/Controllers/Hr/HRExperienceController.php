<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ValidatorTrait;

use App\Models\Hr\HumanResourceExperience;
use App\Services\Hr\HRExperienceService;

class HRExperienceController extends Controller
{
    private $service;

    use ValidatorTrait;

    function __construct()
    {
        $this->service = new HRExperienceService;
    }

    public function update(Request $request, $id){

        $req = $request->all();

        $asset = ['startDate', 'endDate', 'introduction'];

        $req = $this->request_key_exists($req, $asset);


        // 驗證資料
        $valid = $this->validData($req, $this->service->validationRules, $this->service->validationMsg);
        if ($valid) {
            return $valid;
        }

        return $this->service->update($req, $id);

    }

}
