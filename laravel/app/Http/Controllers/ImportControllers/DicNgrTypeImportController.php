<?php

namespace App\Http\Controllers\ImportControllers;

use Ds\Set;

use App\Http\Controllers\Controller;
use App\Models\ebd_ekbd\dictionaries\DicNgrType;
use App\Models\ebd_gis\Ngr2019;
use Illuminate\Support\Facades\Log;

class DicNgrTypeImportController extends Controller
{
    public static function import() {
        
        $newCount = 0;

        foreach(self::getUniqueTypes() as $t) {
            $ob = DicNgrType::firstOrCreate([
                'value' => $t
            ]);

            if($ob->wasRecentlyCreated) $newCount++;
        }

        $mes = ("DicNgrType: added $newCount, total: " . DicNgrType::count());
        echo "\t".$mes."\r\n";
        Log::channel('importlog')->info($mes);
    }

    /** 
    * ngr type выдачи из ngp_2019
    */
    private static function getUniqueTypes() : array {
        $ts = new Set();
        
        foreach(Ngr2019::distinct("type_ngr")->pluck("type_ngr")->flatten() as $t)
            if($t != null ) $ts->add($t);
        
        return $ts->toArray();
    }
}