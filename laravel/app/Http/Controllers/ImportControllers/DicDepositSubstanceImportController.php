<?php

namespace App\Http\Controllers\ImportControllers;

use Ds\Set;

use App\Http\Controllers\Controller;
use App\Models\ebd_ekbd\dictionaries\DicDepositSubstance;
use App\Models\ebd_gis\NgMest;
use Illuminate\Support\Facades\Log;

class DicDepositSubstanceImportController extends Controller
{
    public static function import() {
        
        $newCount = 0;

        foreach(self::getUniqueDepositSubstances() as $ds) {
            $ob = DicDepositSubstance::firstOrCreate([
                'value' => $ds
            ]);

            if($ob->wasRecentlyCreated) $newCount++;
        }

        $mes = ("DicDepositSubstance: added $newCount, total: " . DicDepositSubstance::count());
        echo "\t".$mes."\r\n";
        Log::channel('importlog')->info($mes);
    }

    /** 
    * deposit substance из ng_mest
    */
    private static function getUniqueDepositSubstances() : array {
        $dss = new Set();

        foreach(NgMest::distinct("Содержан_К")->pluck("Содержан_К")->flatten() as $ds)
            if($ds != null ) $dss->add($ds);
       
        return $dss->toArray();
    }
}