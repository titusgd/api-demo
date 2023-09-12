<?php

namespace App\Http\Controllers\Accountant;

use App\Models\Accountant\CurrencyExchange;
use Illuminate\Http\Request;
use App\Services\Accountants\CurrencyExchangeService;
use App\Http\Controllers\Controller;


class CurrencyExchangeController extends Controller
{
    public function index(Request $request)
    {
        return (new CurrencyExchangeService($request))
        ->list()->getResponse();
    }

    public function store(Request $request)
    {
        return (new CurrencyExchangeService($request))
            ->validateStore()
            ->add()
            ->getResponse();
    }
    
    public function show(Request $request,$currencyExchangeId){
        return (new CurrencyExchangeService($request,$currencyExchangeId))
            ->validateShow()
            ->show()
            ->getResponse();
    }

    public function update(Request $request, $currencyExchangeId)
    {
        
        return (new CurrencyExchangeService($request,$currencyExchangeId))
            ->validateUpdate()
            ->update()
            ->getResponse();
    }

    public function destroy(Request $request, $currencyExchangeId)
    {
        return (new CurrencyExchangeService($request,$currencyExchangeId))
        ->validateDestroy()
        ->delete()
        ->getResponse();
    }
}
