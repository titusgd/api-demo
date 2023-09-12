<?php

namespace App\Http\Controllers\Invoice;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Invoice\TouchIssueService;

class TouchIssueController extends Controller
{

    public function touchIssue(Request $request)
    {
        return (new TouchIssueService($request))
            // ->validateStore()
            ->runValidate('store')
            ->store()
            ->getResponse();
    }

}
