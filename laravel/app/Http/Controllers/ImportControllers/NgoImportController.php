<?php

namespace App\Http\Controllers\importcontrollers;

use App\Http\Controllers\Controller;
use App\Models\ebd_ekbd\dictionaries\DicNgoType;
use App\Models\ebd_ekbd\Ngp;
use App\Models\ebd_ekbd\Ngo;
use App\Models\ebd_gis\Ngo2019;

class NgoImportController extends Controller
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

        Ngo::truncate();
        if(self::$showInf) echo("\tngo очищено\r\n");

        $newCount = self::importFromTable();

        dump("Ngo импорт завершен: добавлено " . $newCount . ' из ' . Ngo2019::count());
    }

    /**  Импорт записей из таблицы  ebd_gis.ngo_2019 */
    private static function importFromTable() : int
    {
        $newCount = 0;

            foreach(Ngo2019::all() as $n)
            {
                Ngo::create([
                    'name' => $n->region,
                    'ngo_type_id' => $n->type_ngo ? (DicNgoType::where('value', $n->type_ngo)->first()->id ?? self::getEx('ngo_type_id', $n->type_ngo)) : null,
                    'ngp_id' => $n->province ? (Ngp::where('name', $n->province)->first()->id ?? self::getEx('ngp_id', $n->province)) : null,
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