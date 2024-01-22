<?php

namespace App\Models\ebd_ekbd\dictionaries;

class DicSsubRf extends DictionaryModel
{
    protected $table = 'dic_ssub_rf';


    
    public static function findByRegionName(?string $regionName) : ?DicSsubRf
    {
        if( ($regionName == null) || ($regionName == "") )  return null;

        $res = null;
        
        $res = self::where('region_name','ilike', $regionName)
                    ->first();

        if( !$res )
        {
            $res = DicSsubRfAlias::where('value', 'ilike', $regionName)
                    ->first()?->ssub_rf_id;
            if( $res )
               return DicSsubRf::find($res);
        }

        return $res;
    }

    public static function fixRegionName(?string $regionName) : ?string
    {
        if( !$regionName || $regionName == "" ) return null;
        
        $regionName = preg_replace('/(\S)-Алания/m', "$1 - Алания", $regionName);
        $regionName = preg_replace('/(\S)\(/m', "$1 (", $regionName);

        $regionName = str_replace('обл.', 'область', $regionName);
        $regionName = str_replace('  ', ' ', $regionName);
        $regionName = str_replace('—', '-', $regionName);
       
        return $regionName;
    }
}