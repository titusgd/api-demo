<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Web\HomeSliderShowService;
use App\Traits\ValidatorTrait;


class HomeSliderShowController extends Controller
{

    private $service;

    use ValidatorTrait;

    function __construct()
    {
        $this->service = new HomeSliderShowService;
    }

    public function add(Request $request)
    {
        $req = $request->all();

        // 驗證資料
        $valid = $this->validData($req, $this->service->validationRules, $this->service->validationMsg);
        if ( $valid ) { return $valid; }
       
        // 新增資料
        return $this->service->createData($req);
    }

    public function update(Request $request)
    {
        $req = $request->all();

        // 驗證資料
        $valid = $this->validData($req, $this->service->validationRules, $this->service->validationMsg);
        if ( $valid ) { return $valid; }

        // 更新資料
        $create = $this->service->updateData($req);
        return $create;        
    }

    public function list()
    {
        // 取列表
        return $this->service->getlist();
    }


    public function sort(Request $request)
    {
        // 改排序
        return $this->service->updateSort($request);
    }

    public function show(Request $request)
    {
        // 改顯示狀態        
        return $this->service->updateFlag($request);
    }
}
