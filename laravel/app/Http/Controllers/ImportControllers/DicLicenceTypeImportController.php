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
use Illuminate\Support\Facades\Log;

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

        $mes = ("DicLicenseType: added $newCount, total: " . DicLicenseType::count());
        echo "\t".$mes."\r\n";
        Log::channel('importlog')->info($mes);
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

        $types->add('Э'); 
        
        return $types->toArray();
    }
}