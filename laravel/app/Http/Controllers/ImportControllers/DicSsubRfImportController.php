<?php

namespace App\Http\Controllers\ImportControllers;

use App\Http\Controllers\Controller;
use App\Imports\DicSsubRfImport;
use App\Models\ebd_ekbd\dictionaries\DicSsubRf;
use Maatwebsite\Excel\Facades\Excel;

class DicSsubRfImportController extends Controller
{
    public static function import() {
        
        Excel::import(new DicSsubRfImport, 'ekbd_sub.xlsx');

        echo("\tDicSsubRf: total " . DicSsubRf::count() . "\r\n");
    }
}