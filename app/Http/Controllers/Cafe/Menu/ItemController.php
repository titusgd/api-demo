<?php

namespace App\Http\Controllers\Cafe\Menu;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Cafe\Menu\ItemService;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $service = (new ItemService())->validateIndex($request);
        if (!empty($service->getResponse())) return $service->getResponse();
        return $service->list($request)->getResponse();
    }
    public function store(Request $request)
    {
        $service = (new ItemService)->validateStore($request);
        if (!empty($service->getResponse())) return $service->getResponse();
        return $service->addStore($request)->getResponse();
    }
    public function update(Request $request, $item_id)
    {
        $service = (new ItemService())->validateUpdate($request, $item_id);
        if (!empty($service->getResponse())) return $service->getResponse();
        return $service->update($request, $item_id)->getResponse();
    }
    public function destroy(Request $request)
    {
    }
    public function sort(Request $request)
    {
        return (new ItemService())->sort($request)->getResponse();
    }
}
