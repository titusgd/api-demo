<?php

namespace App\Http\Controllers\Invoice;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Invoice\IssueService;
use App\Services\Invoice\InvoiceCheckService;

class IssueController extends Controller
{

    public function issue(Request $request)
    {

        return (new IssueService($request))
            // ->validateStore()
            ->runValidate('store')
            ->store()
            ->getResponse();
    }

    public function touchInvoiceIssue(Request $request)
    {
        (new InvoiceCheckService())->touchInvoiceIssue();

    }

}
