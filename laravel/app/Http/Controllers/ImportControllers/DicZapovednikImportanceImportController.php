<?php

namespace App\Http\Controllers\importcontrollers;

use Ds\Set;

use App\Http\Controllers\Controller;
use App\Models\ebd_ekbd\dictionaries\DicZapovednikImportance;
use App\Models\ebd_gis\ZapovednikiLn;
use App\Models\ebd_gis\ZapovednikiPln;
use App\Models\ebd_gis\ZapovednikiPt;

class DicZapovednikImportanceImportController extends Controller
{
    public static function import() {
        
        $newCount = 0;

        foreach(self::getUniqueZapIm() as $zi) {
            $ob = DicZapovednikImportance::firstOrCreate([
                'value' => $zi
            ]);

            if($ob->wasRecentlyCreated) $newCount++;
        }
        
        //dump(self::getUniqueZapIm());

        dump("DicZapovednikiImportance: Added " . $newCount);
    }

    /** 
    * Значения заповедников из 3 таблиц zapovedniki_*
    */
    private static function getUniqueZapIm() : array {
        $zis = new Set();
        
        foreach(ZapovednikiLn::distinct("значение_о")->pluck("значение_о")->flatten() as $zi)
            if($zi != null ) $zis->add($zi);
        foreach(ZapovednikiPln::distinct("значение_о")->pluck("значение_о")->flatten() as $zi)
            if($zi != null ) $zis->add($zi);
        foreach(ZapovednikiPt::distinct("значение_о")->pluck("значение_о")->flatten() as $zi)
            if($zi != null ) $zis->add($zi);
        
        return $zis->toArray();
    }
}