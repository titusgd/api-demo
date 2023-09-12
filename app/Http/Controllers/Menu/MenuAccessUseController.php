<?php

namespace App\Http\Controllers\Menu;

use App\Http\Controllers\Controller;
use App\Services\Menu\MenuAccessUseService;
use Illuminate\Http\Request;

class MenuAccessUseController extends Controller
{
    public function index()
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

    public function update(Request $request, $id)
    {
        $service = new MenuAccessUseService();
        $valid = $service->validateUpdate($request,$id);
        if($valid) return $valid;

        return $service->update($request,$id);
    }

    public function destroy($id)
    {
        //
    }
}
