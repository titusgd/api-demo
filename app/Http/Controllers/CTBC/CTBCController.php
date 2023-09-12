<?php

namespace App\Http\Controllers\CTBC;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ValidatorTrait;

use App\Services\CTBC\CTBCService;

class CTBCController extends Controller
{

    private $service;

    use ValidatorTrait;

    function __construct()
    {
        $this->service = new CTBCService;
    }

    public function index(Request $request)
    {

        $req = $request->all();
        // 驗證資料
        $valid = $this->validData($req, $this->service->validationRules, $this->service->validationMsg);
        if ($valid) {
            return $valid;
        }
        
        return $this->service->index($request);

    }

}
