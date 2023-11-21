<?php

namespace App\Http\Controllers\ImportControllers;

use App\Models\ebd_gis\Flangi;
use Ds\Set;

use App\Http\Controllers\Controller;
use App\Models\ebd_ekbd\dictionaries\DicSsubRf;
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

class DicSsubRfImportController extends Controller
{
    public static function import() {
        
        $newCount = 0;

        foreach(self::getUniqueSubs() as $s) {
            $ob = DicSsubRf::firstOrCreate([
                'value' => $s //TODO ...
            ]);

            if($ob->wasRecentlyCreated) $newCount++;
        }

        //dump(self::getUniqueSubs());

        dump("DicPurposeType: Added " . $newCount);
    }

    /** 
    * СФ из 5 таблиц lic_*, flangi, 5 konkurs*, ...
    */
    private static function getUniqueSubs() : array {
        $ss = new Set();
        
        foreach(LicExpLn::distinct("Назв_СФ")->pluck("Назв_СФ")->flatten() as $s)
            if($s != null) $ss->add($s);
        foreach(LicExpPln::distinct("Назв_СФ")->pluck("Назв_СФ")->flatten() as $s)
            if($s != null) $ss->add($s);
        foreach(LicExpPt::distinct("Назв_СФ")->pluck("Назв_СФ")->flatten() as $s)
            if($s != null) $ss->add($s);
        foreach(LicPln::distinct("Назв_СФ")->pluck("Назв_СФ")->flatten() as $s)
            if($s != null) $ss->add($s);
        foreach(LicPt::distinct("Назв_СФ")->pluck("Назв_СФ")->flatten() as $s)
            if($s != null) $ss->add($s);

        foreach(Flangi::distinct("Регион")->pluck("Регион")->flatten() as $s)
            if($s != null) $ss->add($s);

        foreach(Konkurs19::distinct("Регион")->pluck("Регион")->flatten() as $s)
            if($s != null) $ss->add($s);
        foreach(Konkurs20::distinct("Регион")->pluck("Регион")->flatten() as $s)
            if($s != null) $ss->add($s);
        foreach(Konkurs21::distinct("Регион")->pluck("Регион")->flatten() as $s)
            if($s != null) $ss->add($s);
        foreach(Konkurs22::distinct("Регион")->pluck("Регион")->flatten() as $s)
            if($s != null) $ss->add($s);
        foreach(Konkurs23::distinct("Регион")->pluck("Регион")->flatten() as $s)
            if($s != null) $ss->add($s);

            //TODO ...
        
        return $ss->toArray();
    }
}