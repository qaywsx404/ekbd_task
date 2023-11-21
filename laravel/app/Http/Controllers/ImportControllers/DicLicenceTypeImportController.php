<?php

namespace App\Http\Controllers\ImportControllers;

use Ds\Set;

use App\Http\Controllers\Controller;
use App\Models\ebd_ekbd\dictionaries\DicLicenseType;
use App\Models\ebd_gis\LicExpLn;
use App\Models\ebd_gis\LicExpPln;
use App\Models\ebd_gis\LicExpPt;
use App\Models\ebd_gis\LicPln;
use App\Models\ebd_gis\LicPt;

class DicLicenceTypeImportController extends Controller
{
    public static function import() {
        
        $newCount = 0;

        foreach(self::getUniqueTypes() as $type) {
            $ob = DicLicenseType::firstOrCreate([
                'value' => $type
            ]);

            if($ob->wasRecentlyCreated) $newCount++;
        }

        dump("DicLicenseType: Added " . $newCount);
    }

    /** 
    * Типы лицензий из 5 таблиц lic_*
    */
    private static function getUniqueTypes() : array {
        $types = new Set();
        
        foreach(LicExpLn::distinct('type')->pluck('type')->flatten() as $type)
            $types->add($type);
        foreach(LicExpPln::distinct('type')->pluck('type')->flatten() as $type)
            $types->add($type);
        foreach(LicExpPt::distinct('type')->pluck('type')->flatten() as $type)
            $types->add($type);
        foreach(LicPln::distinct('type')->pluck('type')->flatten() as $type)
            $types->add($type);
        foreach(LicPt::distinct('type')->pluck('type')->flatten() as $type)
            $types->add($type);
        
        return $types->toArray();
    }
}