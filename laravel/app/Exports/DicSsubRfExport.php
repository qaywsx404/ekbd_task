<?php

namespace App\Exports;

use App\Http\Controllers\ImportControllers\DicSsubRfImportController;
use App\Imports\SubXlsImport;
use App\Models\ebd_ekbd\dictionaries\DicSsubRf;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Facades\Excel;

class DicSsubRfExport implements FromArray
{
    public function array() : array
    {
        $xlsRowsArr = Excel::toArray(new SubXlsImport, 'ekbd_sub.xlsx');
        $subNamesArr = self::getNamesOfDicSubs();

        $expRowsArr = [
            ["code_region", "region_name", "code_region_parent", "is_fo", "is_sf", "names"]
        ];
        
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
    private static function getNamesOfDicSubs() : array
    {
        /** Все встреченные названия суб */
        $subs = DicSsubRfImportController::getUniqueSubs();
        
        /** Массив суб(сл) -> [ его возможные названия ]*/
        $dicSubs['null'] = [];
        foreach(DicSsubRf::all() as $dicSub)
        {
            $dicSubs[$dicSub->region_name] = [$dicSub->region_name];   // - Добавляет исходное название сразу
            //$dicSubs[$dicSub->region_name] = [];                     // - Учитывает только встретившиеся
        }
        //dd($dicSubs);

        foreach($subs as $sub)
        {
            $cSub = self::fixSub($sub);
            
            $fSub = DicSsubRf::where('region_name', $cSub)
                                ->orWhere('region_name', 'ilike', '%'.$cSub.'%')
                                ->first()?->region_name;

            if($fSub)
            {
                if(!in_array($sub, $dicSubs[$fSub]))
                    array_push($dicSubs[$fSub], $sub);
            }
            else array_push($dicSubs['null'], $sub);
        }
        //dd($dicSubs);

        return $dicSubs;
    }

    private static function fixSub(string $sub) : string
    {
        if(str_contains($sub, 'Шельф')) return 'Шельф Российской Федерации';
        if(str_contains($sub, 'Красноярский край')) return 'Красноярский край';
        if(str_contains($sub, 'Калмыкия')) return 'Республика Калмыкия';
        if($sub == 'Республика Марий-Эл') return 'Республика Марий Эл';
        if($sub == 'Республика Северная-Осетия' || $sub == 'Республика Северная Осетия-Алания' || $sub == 'Республика Северная Осетия и Алания') return 'Республика Северная Осетия - Алания';
        if($sub == 'НАО' || $sub == 'Ненецкий АО') return 'Ненецкий автономный округ';
        if($sub == 'ХМАО' || $sub == 'Ханты-Мансийский  автономный округ' || $sub == 'Ханты-Мансийский автономный округ — Югра') return 'Ханты-Мансийский автономный округ - Югра';
        if($sub == 'Чукотский АО') return 'Чукотский автономный округ';
        if($sub == 'ЯНАО' || $sub == 'Ямало-Ненецкий АО') return 'Ямало-Ненецкий автономный округ';
        if($sub == 'Республика Саха(Якутия)') return 'Республика Саха (Якутия)';
        if($sub == 'Крымский') return 'Республика Крым';
        if($sub == 'Республика Хакасия' || $sub == 'Республика Хакассия') return 'Республика Хакасия';
        if($sub == 'Сев.-Западный') return 'Северо-Западный федеральный округ';
        if($sub == 'Волгоградская обл.') return 'Волгоградская область';
        if($sub == 'Камчатка') return 'Камчатский край';
        if($sub == 'Оренбурская область' || $sub == 'Оренбурская облать') return 'Оренбургская область';
        if($sub == 'Республика Баштортостан') return 'Республика Башкортостан';
        if($sub == 'Тюменская облать') return 'Тюменская область';
        if($sub == 'Челябинская облать') return 'Челябинская область';
        if($sub == 'Саратовская рбласть') return 'Саратовская область';

        return $sub;
    }
}
