<?php

namespace App\Http\Controllers\Invoice;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Invoice\AllowanceIssueService;

class AllowanceIssueController extends Controller
{
    /**
     * Invoice Index
     */

    public function allowanceIssue(Request $request)
    {
        return (new AllowanceIssueService($request))
            // ->validateStore()
            ->runValidate('store')
            ->store()
            ->getResponse();
    }

}
