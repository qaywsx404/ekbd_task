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
        if(self::$showInf) dump("   Ngo tr");

        $newCount = self::importFromTable();

        dump("Ngo total: Added " . $newCount . ' of ' . Ngo2019::count());
    }

    /**  Импорт записей из таблицы  ebd_gis.ngo_2019 */
    private static function importFromTable() : int
    {
        $newCount = 0;

            foreach(Ngo2019::all() as $n)
            {
                Ngo::create([
                    'name' => $n->region,
                    'ngo_type_id' => $n->type_ngo ? DicNgoType::where('value', $n->type_ngo)->first()->id : null,
                    'ngp_id' => $n->province ? Ngp::where('name', $n->province)->first()->id : null,
                    'index_all' => $n->index_all,
                    'comment' => null,
                    'geom' => $n->geom
                ]);

                $newCount++;
            }

        return $newCount;
    }
}