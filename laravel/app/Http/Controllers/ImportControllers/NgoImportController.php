<?php

namespace App\Http\Controllers\importcontrollers;

use App\Http\Controllers\Controller;
use App\Models\ebd_ekbd\dictionaries\DicNgoType;
use App\Models\ebd_ekbd\Ngp;
use App\Models\ebd_ekbd\Ngo;
use App\Models\ebd_gis\Ngo2019;

class NgoImportController extends Controller
{
    public static function import()
    {
        $newCount = $unsavedCount = 0;

        [$newCount, $unsavedCount] = self::importFromTable();

        dump("Ngo total: Added " . $newCount . ', unsaved ' . $unsavedCount);
    }

    /**  Импорт записей из таблицы  ebd_gis.ngo_2019 */
    private static function importFromTable() : array
    {
        $newCount = $unsavedCount = 0;

            foreach(Ngo2019::all() as $n)
            {

                $newNgo = Ngo::create([
                    //TODO
                    //'vid'
                    'name' => $n->region,
                    'ngo_type_id' => $n->type_ngo ? DicNgoType::where('value', $n->type_ngo)->first()->id : null,
                    'ngp_id' => $n->province ? Ngp::where('name', $n->province)->first()->id : null,
                    'index_all' => $n->index_all,
                    'comment' => null,
                    'geom' => $n->geom
                ]);
        
                $newNgo->refresh();

                if( count(Ngo::where('src_hash', $newNgo->src_hash)->get()) > 1)
                {
                    $newNgo->delete();
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