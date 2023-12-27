<?php

namespace App\Http\Controllers\ImportControllers;

use Ds\Set;

use App\Http\Controllers\Controller;
use App\Models\ebd_ekbd\dictionaries\DicDepositStage;
use App\Models\ebd_gis\NgMest;
use App\Models\ebd_gis\NgStruct;
use Illuminate\Support\Facades\Log;

class DicDepositStageImportController extends Controller
{
    public static function import() {
        
        $newCount = 0;

        foreach(self::getUniqueDepositStages() as $ds) {
            $ob = DicDepositStage::firstOrCreate([
                'value' => $ds
            ]);

            if($ob->wasRecentlyCreated) $newCount++;
        }

        $mes = ("DicDepositStage: added $newCount, total: " . DicDepositStage::count());
        echo "\t".$mes."\r\n";
        Log::channel('importlog')->info($mes);
    }

    /** 
    * deposit stage из ng_mest и ng_struct
    */
    private static function getUniqueDepositStages() : array {
        $dss = new Set();

        foreach(NgMest::distinct("Стадия")->pluck("Стадия")->flatten() as $az)
            if($az != null ) $dss->add($az);
        foreach(NgStruct::distinct("Стадия")->pluck("Стадия")->flatten() as $az)
            if($az != null ) $dss->add($az);
       
        return $dss->toArray();
    }
}