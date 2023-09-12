<?php

namespace App\Http\Controllers\Kkday;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Kkday\FlightClassService;

class FlightClassController extends Controller
{
    public function index(Request $request)
    {
        return (new FlightClassService($request))
            ->list()
            ->getResponse();
    }
    public function store(Request $request)
    {
        return (new FlightClassService($request))
            ->runValidate('store')
            ->store()
            ->getResponse();
    }
    public function show()
    {
    }
    public function update(Request $request, $dataId)
    {
        return (new FlightClassService($request, $dataId))
            ->runValidate('update')
            ->update()
            ->getResponse();
    }
    public function destroy(Request $request, $dataId)
    {
        return (new FlightClassService($request, $dataId))
            ->runValidate('destroy')
            ->destroy()
            ->getResponse();
    }
    public function importData(Request $request)
    {
        return (new FlightClassService($request))
            ->import()
            ->getResponse();
    }
}
