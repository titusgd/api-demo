<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Services\Accounting\CorrespondService;
use Illuminate\Http\Request;
use App\Traits\ValidatorTrait;


class CorrespondController extends Controller
{
    private $service;

    use ValidatorTrait;

    function __construct()
    {
        $this->service = new CorrespondService;
    }

    public function store(Request $request)
    {
        // 驗證資料
        $valid = $this->validData($request->all(), $this->service->validationRules, $this->service->validationMsg);
        if ($valid) {
            return $valid;
        }        
        
        // 新增
        return $this->service->create($request->all());
    }

    public function update(Request $request, $id)
    {
        // 驗證資料
        $valid = $this->validData($request->all(), $this->service->validationRules, $this->service->validationMsg);
        if ($valid) {
            return $valid;
        }        

        //  修改 
        return $this->service->update($id, $request->all());
      
    }

    public function show(Request $request)
    {
        // 
    }

    public function index()
    {
        return $this->service->getlist();
    }

    public function destroy($id)
    {
        return $this->service->delete($id);
    }
}
