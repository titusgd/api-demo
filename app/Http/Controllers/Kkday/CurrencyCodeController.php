<?php

namespace App\Http\Controllers\Kkday;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Kkday\CurrencyCodeService;

class CurrencyCodeController extends Controller
{
    public function index(Request $request)
    {
        return (new CurrencyCodeService($request))
            ->list()
            ->getResponse();
    }
    public function store(Request $request)
    {
        return (new CurrencyCodeService($request))
            ->runValidate('store')
            ->store()
            ->getResponse();
    }
    public function show()
    {
    }
    public function update(Request $request, $dataId)
    {
        return (new CurrencyCodeService($request, $dataId))
            ->runValidate('update')
            ->update()
            ->getResponse();
    }
    public function destroy(Request $request, $dataId)
    {
        return (new CurrencyCodeService($request, $dataId))
            ->runValidate('destroy')
            ->destroy()
            ->getResponse();
    }
    public function importData(Request $request)
    {
        return (new CurrencyCodeService($request))
            ->import()
            ->getResponse();
    }
}
