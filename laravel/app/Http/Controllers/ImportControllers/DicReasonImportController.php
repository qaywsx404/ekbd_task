<?php

namespace App\Http\Controllers\ImportControllers;

use Ds\Set;

use App\Http\Controllers\Controller;
use App\Models\ebd_ekbd\dictionaries\DicReason;
use App\Models\ebd_gis\LicExpLn;
use App\Models\ebd_gis\LicExpPln;
use App\Models\ebd_gis\LicExpPt;
use App\Models\ebd_gis\LicPln;
use App\Models\ebd_gis\LicPt;

class DicReasonImportController extends Controller
{
    public static function import() {
        
        $newCount = 0;

        foreach(self::getUniqueReasons() as $r) {
            $ob = DicReason::firstOrCreate([
                'value' => $r
            ]);

            if($ob->wasRecentlyCreated) $newCount++;
        }

        dump("DicReason: Added " . $newCount);
    }

    /** 
    * Основания выдачи из 5 таблиц lic_*
    */
    private static function getUniqueReasons() : array {
        $rs = new Set();
        
        foreach(LicExpLn::distinct("Осн_выдачи")->pluck("Осн_выдачи")->flatten() as $r)
            if($r != null ) $rs->add($r);
        foreach(LicExpPln::distinct("Осн_выдачи")->pluck("Осн_выдачи")->flatten() as $r)
            if($r != null ) $rs->add($r);
        foreach(LicExpPt::distinct("Осн_выдачи")->pluck("Осн_выдачи")->flatten() as $r)
            if($r != null ) $rs->add($r);
        foreach(LicPln::distinct("Осн_выдачи")->pluck("Осн_выдачи")->flatten() as $r)
            if($r != null ) $rs->add($r);
        foreach(LicPt::distinct("Осн_выдачи")->pluck("Осн_выдачи")->flatten() as $r)
            if($r != null ) $rs->add($r);
        
        return $rs->toArray();
    }
}