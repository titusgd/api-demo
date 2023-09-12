<?php

namespace App\Http\Controllers\Kkday;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\Kkday\ImportDataService;

class ImportProductController extends Controller
{


    public function index(){

        $import = (new ImportDataService())->importData();

    }
}
