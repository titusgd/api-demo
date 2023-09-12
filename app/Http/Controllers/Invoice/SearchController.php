<?php

namespace App\Http\Controllers\Invoice;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Invoice\SearchService;

class SearchController extends Controller
{
    public function searchAll(Request $request)
    {
        $request=$request->toArray();
        $request['search_type']='1';
        return (new SearchService($request))
            ->runValidate("searchAll")
            ->runSearchAll()
            ->getResponse();
    }

    public function invalid(Request $request)
    {
        return (new SearchService($request->toArray()))
            ->runValidate("search", 3)
            ->runSearch(3)
            ->getResponse();
    }
    public function allowance(Request $request)
    {
        return (new SearchService($request->toArray()))
            ->runValidate("search", 2)
            ->runSearch(2)
            ->getResponse();
    }
    public function searchAllInvalid(Request $request)
    {
        $request = $request->toArray();
        $request['search_type']='3';
        return (new SearchService($request))
            ->runValidate("searchAll")
            ->runSearchAll()
            ->getResponse();
    }

    public function searchAllAllowance(Request $request)
    {
        $request = $request->toArray();
        $request['search_type']='2';
        return (new SearchService($request))
            ->runValidate("searchAll")
            ->runSearchAll()
            ->getResponse();
    }
}
