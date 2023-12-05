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
        if(self::$showInf) dump("   Ngr tr");

        $newCount = self::importFromTable();

        dump("Ngr total: Added " . $newCount . ' of ' . Ngr2019::count());
    }

    /**  Импорт записей из таблицы  ebd_gis.ngr_2019 */
    private static function importFromTable() : int
    {
        $newCount = 0;

            foreach(Ngr2019::all() as $n)
            {
                $newNgr = Ngr::create([
                    'name' => $n->district,
                    'ngr_type_id' => $n->type_ngr ? DicNgrType::where('value', $n->type_ngr)->first()->id : null,
                    'ngp_id' => $n->province ? Ngp::where('name', $n->province)->first()->id : null,
                    'ngo_id' => $n->region ? Ngo::where('name', $n->region)->first()->id : null,
                    'index_all' => $n->index_all,
                    'comment' => null,
                    'geom' => $n->geom
                ]);
        
               $newCount++;
            }

        return $newCount;
    }
}