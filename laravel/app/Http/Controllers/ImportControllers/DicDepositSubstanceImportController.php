<?php

namespace App\Http\Controllers\ImportControllers;

use Ds\Set;

use App\Http\Controllers\Controller;
use App\Models\ebd_ekbd\dictionaries\DicDepositSubstance;
use App\Models\ebd_gis\NgMest;

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

        //dump(self::getUniqueDepositSubstances());

        dump("DicDepositSubstance: Added " . $newCount);
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