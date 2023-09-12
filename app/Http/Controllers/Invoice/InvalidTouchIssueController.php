<?php

namespace App\Http\Controllers\Invoice;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Invoice\InvalidTouchIssueService;

class InvalidTouchIssueController extends Controller
{

    public function invalidTouchIssue(Request $request)
    {
        return (new InvalidTouchIssueService($request))
            // ->validateStore()
            ->runValidate('store')
            ->store()
            ->getResponse();
    }

}
