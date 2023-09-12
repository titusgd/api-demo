<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Accounting\LedgerService;

class LedgerController extends Controller
{
    public function index(Request $request)
    {
        return (new LedgerService())
            ->validIndex($request->all())
            ->getList($request)
            ->getResponse();

        // 原始程式碼
        // $service = (new LedgerService())->validIndex($request->all());
        // $response = (!empty($service->getResponse())) ? $service->getResponse() : $service->getList($request)->getResponse();
        // return $response;
        
        // $service = new LedgerService();
        // $service->validIndex($request->all());
        // if(!empty($service->res)) return $service->res;
        // return $service->getList($request);
        // dd($request->all());
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
