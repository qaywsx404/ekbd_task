<?php

namespace App\Http\Controllers\importcontrollers;

use App\Http\Controllers\Controller;
use App\Models\ebd_ekbd\dictionaries\DicNgpType;
use App\Models\ebd_ekbd\Ngp;
use App\Models\ebd_gis\Ngp2019;

class NgpImportController extends Controller
{
    public static function import()
    {
        $newCount = $unsavedCount = 0;

        [$newCount, $unsavedCount] = self::importFromTable();

        dump("Ngp total: Added " . $newCount . ', unsaved ' . $unsavedCount);
    }

    /**  Импорт записей из таблицы  ebd_gis.ngp_2019 */
    private static function importFromTable() : array
    {
        $newCount = $unsavedCount = 0;

            foreach(Ngp2019::all() as $n)
            {

                $newNgp = Ngp::create([
                    //TODO
                    //'vid'
                    'name' => $n->province,
                    'ngp_type_id' => $n->type_ngp ? DicNgpType::where('value', $n->type_ngp)->first()->id : null,
                    'index_all' => $n->index_all,
                    'comment' => null,
                    'geom' => $n->geom
                ]);
        
                $newNgp->refresh();

                if( count(Ngp::where('src_hash', $newNgp->src_hash)->get()) > 1)
                {
                    $newNgp->delete();
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