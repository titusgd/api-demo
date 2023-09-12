<?php

namespace App\Http\Controllers\Apidoc;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\ApidocProject;
use App\Services\Apidoc\ApidocProjectService;
use App\Traits\ValidatorTrait;

class ApidocProjectController extends Controller
{

    private $service;

    use ValidatorTrait;

    function __construct()
    {
        $this->service = new ApidocProjectService;
    }

    public function index(Request $request)
    {
        $req = $request->all();
        // 驗證資料
        $valid = $this->validData($req, $this->service->validationRules_index, $this->service->validationMsg_index);
        // $valid = $this->validData($req['content'], $this->service->validationRules2, $this->service->validationMsg2);
        if ($valid) {
            return $valid;
        }

        return $this->service->index($req);
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
}
