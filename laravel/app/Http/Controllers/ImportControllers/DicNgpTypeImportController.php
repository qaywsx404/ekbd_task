<?php

namespace App\Http\Controllers\ImportControllers;

use Ds\Set;

use App\Http\Controllers\Controller;
use App\Models\ebd_ekbd\dictionaries\DicNgpType;
use App\Models\ebd_gis\Ngp2019;

class DicNgpTypeImportController extends Controller
{
    public static function import() {
        
        $newCount = 0;

        foreach(self::getUniqueTypes() as $t) {
            $ob = DicNgpType::firstOrCreate([
                'value' => $t
            ]);

            if($ob->wasRecentlyCreated) $newCount++;
        }

        //dump(self::getUniqueTypes());

        dump("DicNgpType: Added " . $newCount);
    }

    /** 
    * ngp type выдачи из ngp_2019
    */
    private static function getUniqueTypes() : array {
        $ts = new Set();
        
        foreach(Ngp2019::distinct("type_ngp")->pluck("type_ngp")->flatten() as $t)
            if($t != null ) $ts->add($t);
        
        return $ts->toArray();
    }
}