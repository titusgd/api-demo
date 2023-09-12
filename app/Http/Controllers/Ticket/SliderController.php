<?php

namespace App\Http\Controllers\Ticket;

use App\Models\Ticket\Slider;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Ticket\SliderService;

class SliderController extends Controller
{

    public function index(Request $request)
    {
        return (new SliderService($request))
            // ->runValidate('index')
            ->index()
            ->getResponse();
    }

    public function store(Request $request)
    {
        return (new SliderService($request))
            ->runValidate('store')
            ->store()
            ->getResponse();
    }

    public function update(Request $request, $id)
    {
        return (new SliderService($request, $id))
            ->runValidate('update')
            ->update()
            ->getResponse(); //
    }

    public function destroy(Request $request, $id)
    {
        return (new SliderService($request, $id))
            ->runValidate('destroy')
            ->destroy()
            ->getResponse(); //
    }

    public function settingSort(Request $request)
    {
        return (new SliderService($request))
            ->runValidate('settingSort')
            ->settingSort()
            ->getResponse();
    }

    public function settingUse(Request $request)
    {
        return (new SliderService($request))
            ->runValidate('settingUse')
            ->settingUse()
            ->getResponse();
    }

    public function publicIndex(Request $request)
    {
        return (new SliderService($request))
            ->publicIndex()
            ->getResponse();
    }
}
