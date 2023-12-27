<?php

namespace App\Http\Controllers\ImportControllers;

use App\Http\Controllers\Controller;
use App\Imports\DicSsubRfImport;
use App\Models\ebd_ekbd\dictionaries\DicSsubRf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class DicSsubRfImportController extends Controller
{
    public static function import() {
        
        Excel::import(new DicSsubRfImport, 'ekbd_sub.xlsx');

        $mes = ("DicSsubRf: total " . DicSsubRf::count());
        echo "\t".$mes."\r\n";
        Log::channel('importlog')->info($mes);
    }
}