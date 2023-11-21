<?php

namespace App\Http\Controllers\importcontrollers;

use Ds\Set;

use App\Http\Controllers\Controller;
use App\Models\ebd_ekbd\dictionaries\DicZapovednikProfile;
use App\Models\ebd_gis\ZapovednikiLn;
use App\Models\ebd_gis\ZapovednikiPln;
use App\Models\ebd_gis\ZapovednikiPt;

class DicZapovednikProfileImportController extends Controller
{
    public static function import() {
        
        $newCount = 0;

        foreach(self::getUniqueZapPr() as $zp) {
            $ob = DicZapovednikProfile::firstOrCreate([
                'value' => $zp
            ]);

            if($ob->wasRecentlyCreated) $newCount++;
        }
        
        // dump(self::getUniqueZapPr());

        dump("DicZapovednikiProfile: Added " . $newCount);
    }

    /** 
    * Профиль заповедников из 3 таблиц zapovedniki_*
    */
    private static function getUniqueZapPr() : array {
        $zps = new Set();
        
        foreach(ZapovednikiLn::distinct("Профиль")->pluck("Профиль")->flatten() as $zp)
            if($zp != null ) $zps->add($zp);
        foreach(ZapovednikiPln::distinct("Профиль")->pluck("Профиль")->flatten() as $zp)
            if($zp != null ) $zps->add($zp);
        foreach(ZapovednikiPt::distinct("Профиль")->pluck("Профиль")->flatten() as $zp)
            if($zp != null ) $zps->add($zp);
        
        return $zps->toArray();
    }
}