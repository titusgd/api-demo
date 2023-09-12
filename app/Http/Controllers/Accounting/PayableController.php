<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Traits\ValidatorTrait;
use App\Services\Accounting\PayableService;
use Illuminate\Http\Request;

class PayableController extends Controller
{

    private $service;

    use ValidatorTrait;

    function __construct()
    {
        $this->service = new PayableService;
    }

    public function store(Request $request)
    {
        // 入賬
        return $this->service->create($request->all());
    }

    public function update(Request $request)
    {

        
    }

    public function show(Request $request)
    {
        // 取明細
        // return $this->service->updateFlag($request);
    }

    public function index(Request $request)
    {
        // 取列表
        return $this->service->getlist($request->all());
    }

    public function destory(Request $request)
    {
    //     // 取明細
    //     return $this->service->getDetail($request);
    }

}
