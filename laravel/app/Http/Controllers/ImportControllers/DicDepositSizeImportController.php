<?php

namespace App\Http\Controllers\ImportControllers;

use Ds\Set;

use App\Http\Controllers\Controller;
use App\Models\ebd_ekbd\dictionaries\DicDepositSize;
use App\Models\ebd_gis\NgMest;

class DicDepositSizeImportController extends Controller
{
    public static function import() {
        
        $newCount = 0;

        foreach(self::getUniqueDepositSize() as $ds) {
            $ob = DicDepositSize::firstOrCreate([
                'value' => $ds
            ]);

            if($ob->wasRecentlyCreated) $newCount++;
        }
        
        echo("\tDicDepositSize: added $newCount, total: " . DicDepositSize::count() . "\r\n");
    }

    /** 
    * deposit size из ng_mest
    */
    private static function getUniqueDepositSize() : array {
        $dss = new Set();

        foreach(NgMest::distinct("Извл_зап_Н")->pluck("Извл_зап_Н")->flatten() as $ds)
            if($ds != null ) $dss->add($ds);
        foreach(NgMest::distinct("Извл_зап_К")->pluck("Извл_зап_К")->flatten() as $ds)
            if($ds != null ) $dss->add($ds);
        foreach(NgMest::distinct("Извл_зап_Г")->pluck("Извл_зап_Г")->flatten() as $ds)
            if($ds != null ) $dss->add($ds);
       
        return $dss->toArray();
    }
}