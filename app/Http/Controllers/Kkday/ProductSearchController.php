<?php

namespace App\Http\Controllers\Kkday;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Traits\ValidatorTrait;

use App\Services\Kkday\ProductSearchService;

class ProductSearchController extends Controller
{
    private $service;

    use ValidatorTrait;

    public function __construct(){
        $this->service = new ProductSearchService();
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
