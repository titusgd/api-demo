<?php

namespace App\Http\Controllers\Invoice;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Invoice\InvalidService;

class InvalidController extends Controller
{
    /**
     * Invoice Index
     */

    public function invalid(Request $request)
    {
        return (new InvalidService($request))
            // ->validateStore()
            ->runValidate('store')
            ->store()
            ->getResponse();
    }

}
