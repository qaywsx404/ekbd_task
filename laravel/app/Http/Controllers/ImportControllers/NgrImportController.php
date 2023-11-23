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
    public static function import()
    {
        $newCount = $unsavedCount = 0;

        [$newCount, $unsavedCount] = self::importFromTable();

        dump("Ngr total: Added " . $newCount . ', unsaved ' . $unsavedCount);
    }

    /**  Импорт записей из таблицы  ebd_gis.ngr_2019 */
    private static function importFromTable() : array
    {
        $newCount = $unsavedCount = 0;

            foreach(Ngr2019::all() as $n)
            {

                $newNgr = Ngr::create([
                    //TODO
                    //'vid'
                    'name' => $n->district,
                    'ngr_type_id' => $n->type_ngr ? DicNgrType::where('value', $n->type_ngr)->first()->id : null,
                    'ngp_id' => $n->province ? Ngp::where('name', $n->province)->first()->id : null,
                    'ngo_id' => $n->region ? Ngo::where('name', $n->region)->first()->id : null,
                    'index_all' => $n->index_all,
                    'comment' => null,
                    'geom' => $n->geom
                ]);
        
                $newNgr->refresh();

                if( count(Ngr::where('src_hash', $newNgr->src_hash)->get()) > 1)
                {
                    $newNgr->delete();
                    $unsavedCount++;
                }
                else
                {
                    $newCount++;
                }
            }

        return [$newCount, $unsavedCount];
    }
}