<?php

namespace App\Http\Controllers\Kkday;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Kkday\AirportTypeCodeService;

class AirportTypeCodeController extends Controller
{
    public function index(Request $request)
    {
        return (new AirportTypeCodeService($request))
            ->list()
            ->getResponse();
    }
    public function store(Request $request)
    {
        return (new AirportTypeCodeService($request))
            // ->validateStore()
            ->runValidate('store')
            ->store()
            ->getResponse();
    }
    public function show(){

    }
    public function update(Request $request,$dataId)
    {
        return (new AirportTypeCodeService($request,$dataId))
            // ->validateUpdate()
            ->runValidate('update')
            ->update()
            ->getResponse();
    }
    public function destroy(Request $request,$dataId){
        return (new AirportTypeCodeService($request,$dataId))
            // ->validateDestroy()
            ->runValidate('destroy')
            ->destroy()
            ->getResponse();
    }
    public function importData(Request $request)
    {
        return (new AirportTypeCodeService($request))
            ->import()->getResponse();
    }
}
