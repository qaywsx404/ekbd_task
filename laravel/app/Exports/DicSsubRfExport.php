<?php

namespace App\Exports;

use App\Http\Controllers\ImportControllers\DicSsubRfImportController;
use App\Imports\SubXlsImport;
use App\Models\ebd_ekbd\dictionaries\DicSsubRf;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Facades\Excel;

class DicSsubRfExport implements FromArray
{
    //** Запись только встретившихся субъектов */
    public static bool $isOnlyEnc = false;
    //** Вывод несопоставленных субъектов */
    public static bool $showInf = false;

    
    
    public function array() : array
    {
        $expRowsArr = [
            ["code_region", "region_name", "code_region_parent", "is_fo", "is_sf", "aliases"]
        ];
        
        $xlsRowsArr = Excel::toArray(new SubXlsImport, 'storage/app/ekbd_sub_with_seashelves.xlsx');
        $subNamesArr = self::getAliasesOfDicSubs();

        //dd($subNamesArr);

        if(self::$showInf) dump($subNamesArr['null']);
        
        foreach($xlsRowsArr[0] as $row)
        {
            $newRow = [];
            foreach($row as $arg => $val)
            {
                $newRow[] = $val;
            }
            $newRow[] = implode(',', $subNamesArr[ $row['region_name'] ]);
            
            $expRowsArr[] = $newRow;
        }
        
        return $expRowsArr;
    }

    /** Массив суб(сл) -> [ его возможные названия ] 
     *  arr['null'] -> [ ненайденные субъекты ]
    */
    private static function getAliasesOfDicSubs() : array
    {
        /** Все встреченные названия суб */
        $subs = DicSsubRfImportController::getUniqueSubs();
        
        /** Массив суб(сл) -> [ его возможные названия ]*/
        $dicSubs['null'] = [];
        foreach(DicSsubRf::all() as $dicSub)
        {
            if(self::$isOnlyEnc) $dicSubs[$dicSub->region_name] = [];           // - Добавляет исходное название сразу
            else $dicSubs[$dicSub->region_name] = [$dicSub->region_name];       // - Учитывает только встретившиеся
        }

        foreach($subs as $sub)
        {
            $fSub = DicSsubRf::fixRegionName($sub);
            $cSub = self::compSub($fSub);
            
            $ssub_rf = DicSsubRf::where('region_name', 'ilike', $cSub)
                                ->orWhere('region_name', 'ilike', $cSub.'%')
                                ->first()?->region_name;

            if($ssub_rf)
            {
                if( (mb_strtoupper($ssub_rf) != mb_strtoupper($fSub)) && !self::in_array_non_reg($fSub , $dicSubs[$ssub_rf] ) )
                    array_push($dicSubs[$ssub_rf], ($fSub));
            }
            else array_push($dicSubs['null'], $sub);
        }

        return $dicSubs;
    }
    //** Прописанное сопоставление */
    private static function compSub(string $sub) : string
    {
        if($sub == 'Шельф') return 'Шельф Российской Федерации';
        if(str_contains($sub, 'Красноярский край')) return 'Красноярский край';
        if(str_contains($sub, 'Калмыкия')) return 'Республика Калмыкия';
        if($sub == 'Республика Марий-Эл') return 'Республика Марий Эл';
        if($sub == 'Республика Северная Осетия и Алания') return 'Республика Северная Осетия - Алания';
        if($sub == 'НАО' || $sub == 'Ненецкий АО') return 'Ненецкий автономный округ';
        if($sub == 'ХМАО') return 'Ханты-Мансийский автономный округ - Югра';
        if($sub == 'Чукотский АО') return 'Чукотский автономный округ';
        if($sub == 'ЯНАО' || $sub == 'Ямало-Ненецкий АО') return 'Ямало-Ненецкий автономный округ';
        if($sub == 'Крымский') return 'Республика Крым';
        if($sub == 'Сев.-Западный') return 'Северо-Западный федеральный округ';
        if($sub == 'Камчатка') return 'Камчатский край';

        if($sub == 'Республика Северная-Осетия') return 'Республика Северная Осетия - Алания';
        if($sub == 'Республика Хакассия') return 'Республика Хакасия';
        
        return $sub;
    }
    private static function in_array_non_reg(string &$s, array &$arr) : bool
    {
        foreach($arr as $a)
        {
            if(mb_strtoupper($a) == mb_strtoupper($s))
                return true;
        }

        return false;
    }
}
