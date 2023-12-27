<?php

namespace App\Http\Controllers\ImportControllers;

use Ds\Set;

use App\Http\Controllers\Controller;
use App\Models\ebd_ekbd\dictionaries\DicDepositType;
use App\Models\ebd_gis\NgMest;
use App\Models\ebd_gis\NgStruct;
use Illuminate\Support\Facades\Log;

class DicDepositTypeImportController extends Controller
{
    public static function import() {
        
        $newCount = 0;

        foreach(self::getUniqueDepositTypes() as $dt) {
            $ob = DicDepositType::firstOrCreate([
                'value' => $dt
            ]);

            if($ob->wasRecentlyCreated) $newCount++;
        }

        $mes = ("DicDepositType: added $newCount, total: " . DicDepositType::count());
        echo "\t".$mes."\r\n";
        Log::channel('importlog')->info($mes);
    }

    /** 
    * deposit type из ng_mest ng_struct
    */
    private static function getUniqueDepositTypes() : array {
        $dts = new Set();

        foreach(NgMest::distinct("Тип")->pluck("Тип")->flatten() as $dt)
            if($dt != null ) $dts->add($dt);
        foreach(NgStruct::distinct("Тип")->pluck("Тип")->flatten() as $ds)
            if($ds != null ) $dts->add($ds);
       
        return $dts->toArray();
    }
}