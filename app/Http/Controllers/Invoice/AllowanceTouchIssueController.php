<?php

namespace App\Http\Controllers\Invoice;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Invoice\AllowanceTouchIssueService;

class AllowanceTouchIssueController extends Controller
{
    /**
     * Invoice Index
     */

    public function allowanceTouchIssue(Request $request)
    {
        return (new AllowanceTouchIssueService($request))
            // ->validateStore()
            ->runValidate('store')
            ->store()
            ->getResponse();
    }

}
