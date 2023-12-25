<?php

namespace App\Http\Controllers\ImportControllers;

use Ds\Set;

use App\Http\Controllers\Controller;
use App\Models\ebd_ekbd\dictionaries\DicPurpose;
use App\Models\ebd_gis\LicExpLn;
use App\Models\ebd_gis\LicExpPln;
use App\Models\ebd_gis\LicExpPt;
use App\Models\ebd_gis\LicPln;
use App\Models\ebd_gis\LicPt;
use App\Models\ebd_gis\Konkurs19;
use App\Models\ebd_gis\Konkurs20;
use App\Models\ebd_gis\Konkurs21;
use App\Models\ebd_gis\Konkurs22;
use App\Models\ebd_gis\Konkurs23;

class DicPurposeImportController extends Controller
{
    public static function import() {
        
        $newCount = 0;

        foreach(self::getUniquePurposes() as $pp) {
            $ob = DicPurpose::firstOrCreate([
                'value' => $pp
            ]);

            if($ob->wasRecentlyCreated) $newCount++;
        }

        echo("\tDicPurpose: added $newCount, total: " . DicPurpose::count() . "\r\n");
    }

    /** 
    * Цели из 5 таблиц lic_* и 5 konkurs*
    */
    private static function getUniquePurposes() : array {
        $pps = new Set();
        
        foreach(LicExpLn::distinct("Цель")->pluck("Цель")->flatten() as $p)
            $pps->add($p);
        foreach(LicExpPln::distinct("Цель")->pluck("Цель")->flatten() as $p)
            $pps->add($p);
        foreach(LicExpPt::distinct("Цель")->pluck("Цель")->flatten() as $p)
            $pps->add($p);
        foreach(LicPln::distinct("Цель")->pluck("Цель")->flatten() as $p)
            $pps->add($p);
        foreach(LicPt::distinct("Цель")->pluck("Цель")->flatten() as $p)
            $pps->add($p);
        foreach(Konkurs19::distinct("Цель")->pluck("Цель")->flatten() as $p)
            $pps->add($p);
        foreach(Konkurs20::distinct("Цель")->pluck("Цель")->flatten() as $p)
            $pps->add($p);
        foreach(Konkurs21::distinct("Цель")->pluck("Цель")->flatten() as $p)
            $pps->add($p);
        foreach(Konkurs22::distinct("Цель")->pluck("Цель")->flatten() as $p)
            $pps->add($p);
        foreach(Konkurs23::distinct("Цель")->pluck("Цель")->flatten() as $p)
            $pps->add($p);
        
        return $pps->toArray();
    }
}