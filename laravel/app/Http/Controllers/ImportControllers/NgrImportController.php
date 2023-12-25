<?php

namespace App\Http\Controllers\importcontrollers;

use App\Http\Controllers\Controller;
use App\Models\ebd_ekbd\dictionaries\DicNgrType;
use App\Models\ebd_ekbd\Ngr;
use App\Models\ebd_ekbd\Ngp;
use App\Models\ebd_ekbd\Ngo;
use App\Models\ebd_gis\Ngr2019;

class NgrImportController extends Controller
{
    /** Доп. инф. */
    private static $showInf = false;

    /**
     * Начать импорт
     * 
     * @param bool $showInf  `true`  - показывать доп. инф.
     *                       `false` - не показывать доп. инф.
     */
    public static function import(bool $showInf = false)
    {
        self::$showInf = $showInf;
        $newCount = 0;

        Ngr::truncate();
        if(self::$showInf) echo("\tngr очищено\r\n");

        $newCount = self::importFromTable();

        dump("Ngr импорт завершен: добавлено " . $newCount . ' из ' . Ngr2019::count());
    }

    /**  Импорт записей из таблицы  ebd_gis.ngr_2019 */
    private static function importFromTable() : int
    {
        $newCount = 0;

            foreach(Ngr2019::all() as $n)
            {
                $newNgr = Ngr::create([
                    'name' => $n->district,
                    'ngr_type_id' => $n->type_ngr ? (DicNgrType::where('value', $n->type_ngr)->first()->id ?? self::getEx('ngr_type_id', $n->type_ngr)) : null,
                    'ngp_id' => $n->province ? (Ngp::where('name', $n->province)->first()->id ?? self::getEx('ngp_id', $n->province)): null,
                    'ngo_id' => $n->region ? (Ngo::where('name', $n->region)->first()->id ?? self::getEx('ngo_id', $n->region)) : null,
                    'index_all' => $n->index_all,
                    'comment' => null,
                    'geom' => $n->geom
                ]);
        
               $newCount++;
            }

        return $newCount;
    }

    private static function getEx($attrName, $attrVal)
    {
        if($attrVal)
        {
            echo("\tНеверная строка или не найдена запись для Deposit: $attrName : \"$attrVal\"
            \t\r\n");
        }
        
        return null;
    }
}