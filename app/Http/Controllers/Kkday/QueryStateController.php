<?php

namespace App\Http\Controllers\Kkday;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Traits\ValidatorTrait;

use App\Services\Kkday\QueryStateService;

class QueryStateController extends Controller
{
    private $service;

    use ValidatorTrait;

    public function __construct()
    {
        $this->service = new QueryStateService();
    }

    public function index()
    {
        return $this
            ->service
            ->stateList()
            ->getResponse();
        // return $this->service->index();
    }

    public function importData()
    {
        return $this
            ->service
            ->runImport()
            ->getResponse();
    }
}
