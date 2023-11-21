<?php

namespace App\Http\Controllers\importcontrollers;

use Ds\Set;

use App\Http\Controllers\Controller;
use App\Models\ebd_ekbd\dictionaries\DicZapovednikState;
use App\Models\ebd_gis\ZapovednikiLn;
use App\Models\ebd_gis\ZapovednikiPln;
use App\Models\ebd_gis\ZapovednikiPt;

class DicZapovednikStateImportController extends Controller
{
    public static function import() {
        
        $newCount = 0;

        foreach(self::getUniqueZapSt() as $zs) {
            $ob = DicZapovednikState::firstOrCreate([
                'value' => $zs
            ]);

            if($ob->wasRecentlyCreated) $newCount++;
        }
        
        // dump(self::getUniqueZapSt());

        dump("DicZapovednikiState: Added " . $newCount);
    }

    /** 
    * Состояния заповедников из 3 таблиц zapovedniki_*
    */
    private static function getUniqueZapSt() : array {
        $zss = new Set();
        
        foreach(ZapovednikiLn::distinct("Текущий_ст")->pluck("Текущий_ст")->flatten() as $zs)
            if($zs != null ) $zss->add($zs);
        foreach(ZapovednikiPln::distinct("Текущий_ст")->pluck("Текущий_ст")->flatten() as $zs)
            if($zs != null ) $zss->add($zs);
        foreach(ZapovednikiPt::distinct("Текущий_ст")->pluck("Текущий_ст")->flatten() as $zs)
            if($zs != null ) $zss->add($zs);
        
        return $zss->toArray();
    }
}