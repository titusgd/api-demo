<?php

namespace App\Http\Controllers\Kkday\Booking;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Traits\ValidatorTrait;

use App\Services\Kkday\Booking\QueryAmountService;

class QueryAmountController extends Controller
{

    private $service;

    use ValidatorTrait;

    public function __construct(){
        $this->service = new QueryAmountService();
    }

    public function index(Request $request){

        $req = $request->all();
        // 驗證資料

        $valid = $this->validData($req, $this->service->validationRules, $this->service->validationMsg);
        // $valid = $this->validData($req['content'], $this->service->validationRules2, $this->service->validationMsg2);
        if ($valid) {
            return $valid;
        }

        return $this->service->index($req);

    }
}
