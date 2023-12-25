<?php

namespace App\Http\Controllers\importcontrollers;

use App\Http\Controllers\Controller;
use App\Models\ebd_ekbd\dictionaries\DicNgpType;
use App\Models\ebd_ekbd\Ngp;
use App\Models\ebd_gis\Ngp2019;

class NgpImportController extends Controller
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

        Ngp::truncate();
        if(self::$showInf) echo("\tngp очищено\r\n");

        $newCount = self::importFromTable();

        dump("Ngp импорт завершен: добавлено " . $newCount . ' из ' . Ngp2019::count());
    }

    /**  Импорт записей из таблицы  ebd_gis.ngp_2019 */
    private static function importFromTable() : int
    {
        $newCount = 0;

            foreach(Ngp2019::all() as $n)
            {
                $newNgp = Ngp::create([
                    'name' => $n->province,
                    'ngp_type_id' => $n->type_ngp ? (DicNgpType::where('value', $n->type_ngp)->first()->id ?? self::getEx('ngp_type_id', $n->type_ngp)) : null,
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