<?php

namespace App\Http\Controllers\importcontrollers;

use Ds\Set;

use App\Http\Controllers\Controller;
use App\Models\ebd_ekbd\dictionaries\DicZapovednikCategory;
use App\Models\ebd_gis\ZapovednikiLn;
use App\Models\ebd_gis\ZapovednikiPln;
use App\Models\ebd_gis\ZapovednikiPt;
use Illuminate\Support\Facades\Log;

class DicZapovednikCategoryImportController extends Controller
{
    public static function import() {
        
        $newCount = 0;

        foreach(self::getUniqueZapCategory() as $zc) {
            $ob = DicZapovednikCategory::firstOrCreate([
                'value' => $zc
            ]);

            if($ob->wasRecentlyCreated) $newCount++;
        }
        
        $mes = ("DicZapovednikCategory: added $newCount, total: " . DicZapovednikCategory::count());
        echo "\t".$mes."\r\n";
        Log::channel('importlog')->info($mes);
    }

    /** 
    * Категории заповедников из 3 таблиц zapovedniki_*
    */
    private static function getUniqueZapCategory() : array {
        $zcs = new Set();
        
        foreach(ZapovednikiLn::distinct("категория_")->pluck("категория_")->flatten() as $zc)
            if($zc != null ) $zcs->add($zc);
        foreach(ZapovednikiPln::distinct("категория_")->pluck("категория_")->flatten() as $zc)
            if($zc != null ) $zcs->add($zc);
        foreach(ZapovednikiPt::distinct("категория_")->pluck("категория_")->flatten() as $zc)
            if($zc != null ) $zcs->add($zc);
        
        return $zcs->toArray();
    }
}