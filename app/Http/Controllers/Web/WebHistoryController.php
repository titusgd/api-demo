<?php

namespace App\Http\Controllers\Web;

use App\Models\Web\WebHistory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Web\WebHistoryService;
use App\Traits\ValidatorTrait;


class WebHistoryController extends Controller
{
    private $service;

    use ValidatorTrait;

    function __construct()
    {
        $this->service = new WebHistoryService;
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

    public function list(Request $request)
    {
        // 取列表
        return $this->service->getlist($request->language);
    }


    public function show(Request $request)
    {
        // 改排序
        return $this->service->updateFlag($request);
    }

    public function sort(Request $request)
    {
        // 改排序
        return $this->service->updateSort($request);
    }

}
