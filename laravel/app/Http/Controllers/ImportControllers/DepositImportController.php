<?php

namespace App\Http\Controllers\importcontrollers;

use App\Http\Controllers\Controller;
use App\Models\ebd_ekbd\Deposit;
use App\Models\ebd_ekbd\dictionaries\DicArcticZone;
use App\Models\ebd_ekbd\dictionaries\DicDepositSize;
use App\Models\ebd_ekbd\dictionaries\DicDepositStage;
use App\Models\ebd_ekbd\dictionaries\DicDepositSubstance;
use App\Models\ebd_ekbd\dictionaries\DicDepositType;
use App\Models\ebd_ekbd\dictionaries\DicSsubRf;
use App\Models\ebd_ekbd\Ngp;
use App\Models\ebd_ekbd\Ngo;
use App\Models\ebd_ekbd\Ngr;
use App\Models\ebd_gis\NgMest;
use Exception;

class DepositImportController extends Controller
{
    public static function import()
    {
        $newCount = $unsavedCount = 0;

        [$newCount, $unsavedCount] = self::importFromTable();

        dump("Deposit total: Added " . $newCount . ', unsaved ' . $unsavedCount);
    }

    /**  Импорт записей из таблицы  ebd_gis.ng_mest */
    private static function importFromTable() : array
    {
        $newCount = $unsavedCount = 0;

            foreach(NgMest::all() as $d)
            {
                try
                {
                    $newDeposit = Deposit::create([
                        //TODO
                        //'vid' => ?
                        'name' => $d->СПИСОК_МЕС,
                        'deposit_type_id' => $d->Тип ? DicDepositType::where('value', $d->Тип)->first()?->id : null,
                        'deposit_stage_id' => $d->Стадия ? DicDepositStage::where('value', $d->Стадия)->first()?->id : null,
                        'dyear' => $d->Год_откр,
                        //TODO
                        'oblast_ssub_rf_id' => $d->Область ? DicSsubRf::where('value', $d->Область)->first()?->id : null,
                        //TODO
                        'okrug_ssub_rf_id' => $d->Округ ? DicSsubRf::where('value', $d->Округ)->first()?->id : null,
                        'ngp_id' => $d->ngp ? Ngp::where('name', $d->ngp)->first()?->id : null,
                        'ngo_id' => $d->ngo ? Ngo::where('name', $d->ngo)->first()?->id : null,
                        'ngr_id' => $d->ngr ? Ngr::where('name', $d->ngr)->first()?->id : null,
                        'arctic_zone_id' => $d->Аркт_зона ? DicArcticZone::where('value', $d->Аркт_зона)->first()?->id : null,
                        'deposit_n_size_id' => $d->Извл_зап_Н ? DicDepositSize::where('value', $d->Извл_зап_Н)->first()?->id : null,
                        'deposit_k_size_id' => $d->Извл_зап_К ? DicDepositSize::where('value', $d->Извл_зап_К)->first()?->id : null,
                        'deposit_g_size_id' => $d->Геол_зап_Г ? DicDepositSize::where('value', $d->Геол_зап_Г)->first()?->id : null,
                        'deposit_k_substance_id' => $d->Содержан_К ? DicDepositSubstance::where('value', $d->Содержан_К)->first()?->id : null,
                        'comment' => $d->Комментар,
                        'note' => $d->Примечание,
                        'geom' => $d->geom
                    ]);
            
                    $newDeposit->refresh();

                    if( count(Deposit::where('src_hash', $newDeposit->src_hash)->get()) > 1)
                    {
                        $newDeposit->delete();
                        $unsavedCount++;
                    }
                    else
                    {
                        $newCount++;
                    }
                }
                catch(Exception $e)
                {
                    dump($e->getMessage());
                    dd($d);
                    continue;
                }
            }

        return [$newCount, $unsavedCount];
    }
}