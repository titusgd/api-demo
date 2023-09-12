<?php

namespace App\Http\Controllers\Invoice;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Invoice\InvoiceEditService;

class EditController extends Controller
{
    /**
     * Invoice Index
     */

    public function edit(Request $request)
    {
        return (new InvoiceEditService($request))
            // ->validateStore()
            ->runValidate('store')
            ->store()
            ->getResponse();
    }

}
