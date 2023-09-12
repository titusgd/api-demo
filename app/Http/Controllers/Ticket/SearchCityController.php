<?php

namespace App\Http\Controllers\Ticket;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Ticket\SearchCityService;

class SearchCityController extends Controller
{
    public function index(Request $request)
    {
        return (new SearchCityService($request))
            // ->runValidate('index')
            ->index()
            ->getResponse();
    }
    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        return (new SearchCityService($request))
            ->runValidate('store')
            ->store()
            ->getResponse();
    }

    public function show(Request $request)
    {
        //
    }

    public function edit(Request $request)
    {
        //
    }

    public function update(Request $request, $data_id)
    {
        //
    }
    public function destroy(Request $request, $id)
    {
        return (new SearchCityService($request, $id))
            ->runValidate('destroy')
            ->destroy()
            ->getResponse();
    }

    public function settingStor(Request $request)
    {
        return (new SearchCityService($request))
            ->runValidate('settingStor')
            ->settingStor()
            ->getResponse();
    }

    public function publicIndex(Request $request){
        return (new SearchCityService($request))
        ->publicIndex()
        ->getResponse();
    }
}
