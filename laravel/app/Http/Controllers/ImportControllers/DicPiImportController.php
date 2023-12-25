<?php

namespace App\Http\Controllers\importcontrollers;

use Ds\Set;

use App\Http\Controllers\Controller;
use App\Models\ebd_ekbd\dictionaries\DicPi;
use App\Models\ebd_gis\LicExpLn;
use App\Models\ebd_gis\LicExpPln;
use App\Models\ebd_gis\LicExpPt;
use App\Models\ebd_gis\LicPln;
use App\Models\ebd_gis\LicPt;


class DicPiImportController extends Controller
{
    public static function import() {
        
        $newCount = 0;

        foreach(self::getUniquePi() as $pi) {
            $ob = DicPi::firstOrCreate([
                'value' => $pi
            ]);

            if($ob->wasRecentlyCreated) $newCount++;
        }

        echo("\tDicPi: added $newCount, total: " . DicPi::count() . "\r\n");
    }

    /** 
    * Полезные ископаемые из 5 таблиц lic_* (с разбиением по запятой)
    */
    private static function getUniquePi() : array {
        $pis = new Set();
        
        foreach( LicExpLn::distinct("Пол_ископ")->pluck('Пол_ископ')->flatten() as $piStr )
            foreach( self::splitPi($piStr) as $pi )
                $pis->add( ltrim($pi) );
        
        foreach( LicExpPln::distinct("Пол_ископ")->pluck('Пол_ископ')->flatten() as $piStr )
            foreach( self::splitPi($piStr) as $pi )
                $pis->add( ltrim($pi) ); 
        
        foreach( LicExpPt::distinct("Пол_ископ")->pluck('Пол_ископ')->flatten() as $piStr )
            foreach( self::splitPi($piStr) as $pi )
                $pis->add( ltrim($pi) );

        foreach( LicPln::distinct("Пол_ископ")->pluck('Пол_ископ')->flatten() as $piStr )
            foreach( self::splitPi($piStr) as $pi )
                $pis->add( ltrim($pi) );
            
        foreach( LicPt::distinct("Пол_ископ")->pluck('Пол_ископ')->flatten() as $piStr )
            foreach( self::splitPi($piStr) as $pi )
                $pis->add( ltrim($pi) );
        
        return $pis->toArray();
    }

    private static function splitPi($str) : array {
        $pis = str_ireplace(', ', ',', $str);
        return explode(',', $pis);
    }
}