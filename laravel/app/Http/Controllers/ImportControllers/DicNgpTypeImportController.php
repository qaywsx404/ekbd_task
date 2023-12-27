<?php

namespace App\Http\Controllers\ImportControllers;

use Ds\Set;

use App\Http\Controllers\Controller;
use App\Models\ebd_ekbd\dictionaries\DicNgpType;
use App\Models\ebd_gis\Ngp2019;
use Illuminate\Support\Facades\Log;

class DicNgpTypeImportController extends Controller
{
    public static function import() {
        
        $newCount = 0;

        foreach(self::getUniqueTypes() as $t) {
            $ob = DicNgpType::firstOrCreate([
                'value' => $t
            ]);

            if($ob->wasRecentlyCreated) $newCount++;
        }

        $mes = ("DicNgpType: added $newCount, total: " . DicNgpType::count());
        echo "\t".$mes."\r\n";
        Log::channel('importlog')->info($mes);
    }

    /** 
    * ngp type выдачи из ngp_2019
    */
    private static function getUniqueTypes() : array {
        $ts = new Set();
        
        foreach(Ngp2019::distinct("type_ngp")->pluck("type_ngp")->flatten() as $t)
            if($t != null ) $ts->add($t);
        
        return $ts->toArray();
    }
}