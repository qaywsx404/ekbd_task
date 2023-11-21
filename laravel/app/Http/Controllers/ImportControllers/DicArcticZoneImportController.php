<?php

namespace App\Http\Controllers\ImportControllers;

use Ds\Set;

use App\Http\Controllers\Controller;
use App\Models\ebd_ekbd\dictionaries\DicArcticZone;
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
use App\Models\ebd_gis\NgMest;
use App\Models\ebd_gis\NgStruct;

class DicArcticZoneImportController extends Controller
{
    public static function import() {
        
        $newCount = 0;

        foreach(self::getUniqueArcticZones() as $az) {
            $ob = DicArcticZone::firstOrCreate([
                'value' => $az
            ]);

            if($ob->wasRecentlyCreated) $newCount++;
        }

        //dump(self::getUniqueArcticZones());

        dump("DicArcticZone: Added " . $newCount);
    }

    /** 
    * Аркт. зоны из 5 таблиц lic_*, 5 таблиц konkurs*, ng_mest и ng_struct
    */
    private static function getUniqueArcticZones() : array {
        $azs = new Set();
        
        foreach(LicExpLn::distinct("Аркт_зона")->pluck("Аркт_зона")->flatten() as $az)
            if($az != null ) $azs->add($az);
        foreach(LicExpPln::distinct("Аркт_зона")->pluck("Аркт_зона")->flatten() as $az)
            if($az != null ) $azs->add($az);
        foreach(LicExpPt::distinct("Аркт_зона")->pluck("Аркт_зона")->flatten() as $az)
            if($az != null ) $azs->add($az);
        foreach(LicPln::distinct("Аркт_зона")->pluck("Аркт_зона")->flatten() as $az)
            if($az != null ) $azs->add($az);
        foreach(LicPt::distinct("Аркт_зона")->pluck("Аркт_зона")->flatten() as $az)
            if($az != null ) $azs->add($az);

        // foreach(Konkurs19::distinct("Арктическа")->pluck("Арктическа")->flatten() as $az)
        //     if($az != null ) $azs->add($az);
        foreach(Konkurs20::distinct("Арктическа")->pluck("Арктическа")->flatten() as $az)
            if($az != null ) $azs->add($az);
        foreach(Konkurs21::distinct("Арктическа")->pluck("Арктическа")->flatten() as $az)
            if($az != null ) $azs->add($az);
        foreach(Konkurs22::distinct("Арктическа")->pluck("Арктическа")->flatten() as $az)
            if($az != null ) $azs->add($az);
        foreach(Konkurs23::distinct("Аркт_зона")->pluck("Аркт_зона")->flatten() as $az)
            if($az != null ) $azs->add($az);

        foreach(NgMest::distinct("Аркт_зона")->pluck("Аркт_зона")->flatten() as $az)
            if($az != null ) $azs->add($az);
        foreach(NgStruct::distinct("arct_zona")->pluck("arct_zona")->flatten() as $az)
            if($az != null ) $azs->add($az);
       
        return $azs->toArray();
    }
}