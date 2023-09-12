<?php

namespace App\Http\Controllers\Ticket;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Ticket\ThemeProductService;

class ThemeProductController extends Controller
{
    public function store(Request $request)
    {
        return (new ThemeProductService($request))
            ->runValidate('store')
            ->store()
            ->getResponse();
    }

    public function destroy(Request $request, $data_id)
    {
        return (new ThemeProductService($request, $data_id))
            ->runValidate('destroy')
            ->destroy()
            ->getResponse();
    }
}
