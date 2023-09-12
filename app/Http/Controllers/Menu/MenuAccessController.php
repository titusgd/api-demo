<?php

namespace App\Http\Controllers\Menu;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Menu\MenuAccessService;
class MenuAccessController extends Controller
{
    public function index(Request $request)
    {
        $req = collect(json_decode($request->data,true));
        
        $service = new MenuAccessService($req->all());
        
        $valid = $service->validateAccessList();
        if ($valid) return $valid;

        return $service->getAccessList();
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
