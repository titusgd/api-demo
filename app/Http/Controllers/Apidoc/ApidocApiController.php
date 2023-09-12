<?php

namespace App\Http\Controllers\Apidoc;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Apidoc;
use App\Services\Apidoc\ApidocApiService;
use App\Traits\ValidatorTrait;

use Illuminate\Support\Arr;

class ApidocApiController extends Controller
{
    private $service;

    use ValidatorTrait;

    function __construct()
    {
        $this->service = new ApidocApiService;
    }

    public function index(Request $request)
    {
        return $this->service->index($request);
    }

    public function store(Request $request){


        $req = $request->all();

        // 驗證資料
        $valid = $this->validData($req, Arr::dot($this->service->validationRules), $this->service->validationMsg);
        if ($valid) {
            return $valid;
        }

        // 新增資料
        return $this->service->create($req);
    }

    public function update(Request $request, $id){

        $req = $request->all();

        // 新增資料
        return $this->service->update($req, $id);
    }

    public function destroy($id){

        return $this->service->delete($id);
    }

}
