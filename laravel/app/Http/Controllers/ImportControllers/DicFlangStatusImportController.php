<?php

namespace App\Http\Controllers\ImportControllers;

use Ds\Set;

use App\Http\Controllers\Controller;
use App\Models\ebd_ekbd\dictionaries\DicFlangStatus;
use App\Models\ebd_gis\Flangi;
use Illuminate\Support\Facades\Log;

class DicFlangStatusImportController extends Controller
{
    public static function import() {
        
        $newCount = 0;

        foreach(self::getUniqueFlangStatuses() as $fs) {
            $ob = DicFlangStatus::firstOrCreate([
                'value' => $fs
            ]);

            if($ob->wasRecentlyCreated) $newCount++;
        }

        $mes = ("DicFlangStatus: added $newCount, total: " . DicFlangStatus::count());
        echo "\t".$mes."\r\n";
        Log::channel('importlog')->info($mes);
    }

    /** 
    * Flang status из 1 таблицы flangi
    */
    private static function getUniqueFlangStatuses() : array {
        $fss = new Set();
        
        foreach(Flangi::distinct("Статус_по_")->pluck("Статус_по_")->flatten() as $fs)
            if($fs != null) $fss->add($fs);
        
        return $fss->toArray();
    }
}