<?php

namespace App\Http\Controllers\Ticket;

use App\Models\Ticket\HotCity;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Ticket\HotCityService;

class HotCityController extends Controller
{
    public function index(Request $request)
    {
        return (new HotCityService($request))
            // ->runValidate('index')
            ->index()
            ->getResponse();
    }

    public function store(Request $request)
    {
        return (new HotCityService($request))
            ->runValidate('store')
            ->store()
            ->getResponse();
    }

    public function destroy(Request $request, $id)
    {
        return (new HotCityService($request, $id))
            ->runValidate('destroy')
            ->destroy()
            ->getResponse();
    }
    public function settingStor(Request $request)
    {
        return (new HotCityService($request))
            ->runValidate('settingStor')
            ->settingStor()
            ->getResponse();
    }

    public function uploadImage(Request $request)
    {
        return (new HotCityService($request))
            ->runValidate('uploadImage')
            ->uploadImage()
            ->getResponse();
    }

    public function publicIndex(Request $request){
        return (new HotCityService($request))
        ->publicIndex()
        ->getResponse();
    }
}
