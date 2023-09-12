<?php

namespace App\Http\Controllers\Apidoc;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Apidoc;
use App\Services\Apidoc\ApidocService;
use App\Traits\ValidatorTrait;

class ApidocController extends Controller
{

    private $service;

    use ValidatorTrait;

    function __construct()
    {
        $this->service = new ApidocService;
    }

    public function index()
    {
        return $this->service->index();
    }

    public function store(Request $request){


        $req = $request->all();
        // 驗證資料
        $valid = $this->validData($req, $this->service->validationRules, $this->service->validationMsg);
        // $valid = $this->validData($req['content'], $this->service->validationRules2, $this->service->validationMsg2);
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

    public function sort(Request $request){

        $req = $request->all();

        $data = $req['data'];

        return $this->service->sort($data);
    }
}
