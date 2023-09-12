<?php

namespace App\Http\Controllers\Invoice;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Invoice\SearchInvoiceService;

class SearchInvoiceController extends Controller
{
    /**
     * Invoice Index
     */

    public function searchInvoice(Request $request)
    {
        return (new SearchInvoiceService($request))
            // ->validateStore()
            ->runValidate('store')
            ->store()
            ->getResponse();
    }

}
