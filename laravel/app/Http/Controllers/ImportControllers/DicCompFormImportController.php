<?php

namespace App\Http\Controllers\importcontrollers;

use Ds\Set;

use App\Http\Controllers\Controller;
use App\Models\ebd_ekbd\dictionaries\DicCompForm;
use App\Models\ebd_gis\Konkurs19;
use App\Models\ebd_gis\Konkurs20;
use App\Models\ebd_gis\Konkurs21;
use App\Models\ebd_gis\Konkurs22;
use App\Models\ebd_gis\Konkurs23;


class DicCompFormImportController extends Controller
{
    public static function import() {
        
        $newCount = 0;

        foreach(self::getUniqueCompForms() as $cf) {
            $ob = DicCompForm::firstOrCreate([
                'value' => $cf
            ]);

            if($ob->wasRecentlyCreated) $newCount++;
        }

        echo("\tDicCompForm: added $newCount, total: " . DicCompForm::count() . "\r\n");
    }

    /** 
    * Формы состязаний из 5 таблиц konkurs*
    */
    private static function getUniqueCompForms() : array {
        $cfs = new Set();
        
        foreach(Konkurs19::distinct("Ф_состязан")->pluck("Ф_состязан")->flatten() as $cf)
            $cfs->add($cf);
        foreach(Konkurs20::distinct("Ф_состязан")->pluck("Ф_состязан")->flatten() as $cf)
            $cfs->add($cf);
        foreach(Konkurs21::distinct("Ф_состязан")->pluck("Ф_состязан")->flatten() as $cf)
            $cfs->add($cf);
        foreach(Konkurs22::distinct("Ф_состязан")->pluck("Ф_состязан")->flatten() as $cf)
            $cfs->add($cf);
        foreach(Konkurs23::distinct("Ф_состязан")->pluck("Ф_состязан")->flatten() as $cf)
            $cfs->add($cf);
        
        return $cfs->toArray();
    }
}