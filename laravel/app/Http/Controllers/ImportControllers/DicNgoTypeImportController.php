<?php

namespace App\Http\Controllers\ImportControllers;

use Ds\Set;

use App\Http\Controllers\Controller;
use App\Models\ebd_ekbd\dictionaries\DicNgoType;
use App\Models\ebd_gis\Ngo2019;
use Illuminate\Support\Facades\Log;

class DicNgoTypeImportController extends Controller
{
    public static function import() {
        
        $newCount = 0;

        foreach(self::getUniqueTypes() as $t) {
            $ob = DicNgoType::firstOrCreate([
                'value' => $t
            ]);

            if($ob->wasRecentlyCreated) $newCount++;
        }

        $mes = ("DicNgoType: added $newCount, total: " . DicNgoType::count());
        echo "\t".$mes."\r\n";
        Log::channel('importlog')->info($mes);
    }

    /** 
    * ngo type выдачи из ngp_2019
    */
    private static function getUniqueTypes() : array {
        $ts = new Set();
        
        foreach(Ngo2019::distinct("type_ngo")->pluck("type_ngo")->flatten() as $t)
            if($t != null ) $ts->add($t);
        
        return $ts->toArray();
    }
}