<?php

namespace App\Http\Controllers\importcontrollers;

use App\Http\Controllers\Controller;
use App\Models\ebd_ekbd\dictionaries\DicCompForm;
use App\Models\ebd_ekbd\dictionaries\DicLicenseType;
use App\Models\ebd_ekbd\dictionaries\DicPurpose;
use App\Models\ebd_ekbd\Konkurs;
use App\Models\ebd_ekbd\dictionaries\DicArcticZone;
use App\Models\ebd_ekbd\dictionaries\DicSsubRf;
use Exception;

class KonkursImportController extends Controller
{
    public static function import()
    {
        $newCount = $unsavedCount = 0;

        //konkurs19
        $a = self::importFromTable('Konkurs19');
            $newCount += $a[0]; $unsavedCount += $a[1];
        //konkurs20
        $a = self::importFromTable('Konkurs20');
            $newCount += $a[0]; $unsavedCount += $a[1];
        //konkurs21
        $a = self::importFromTable('Konkurs21');
            $newCount += $a[0]; $unsavedCount += $a[1];
        //konkurs22
        $a = self::importFromTable('Konkurs22');
            $newCount += $a[0]; $unsavedCount += $a[1];
        //konkurs23
        $a = self::importFromTable('Konkurs23');
            $newCount += $a[0]; $unsavedCount += $a[1];

        dump("Konkurs total: Added " . $newCount . ', unsaved ' . $unsavedCount);
    }

    /**  Импорт записей из таблиц  ebd_gis.konkurs* */
    private static function importFromTable(string $konkursTableName) : array
    {
        $newCount = $unsavedCount = 0;

        $konkursTableModel = new (trim('App\Models\ebd_gis\ ') . $konkursTableName )();

            foreach($konkursTableModel::all() as $k)
            {
                try
                {
                    $newKopnkurs = Konkurs::create([
                        //TODO
                        //'vid' => ?
                        'name' => $k->Название_у,
                        'license_type_id' => $k->Тип_лиц ? DicLicenseType::where('value', $k->Тип_лиц)->first()?->id : null,
                        //TODO ---полезное_ископаемое --ebd_gis.konkursXX.Полезн_иск (разбить по запятой) --Связь:"konkurs_pi"
                        //'konkurs_pi_id' =>
                        'purpose_id' => $k->Цель ? DicPurpose::where('value', $k->Цель)->first()?->id : null,
                        'ryear' => $k->Год_включ,
                        'comp_form_id' => $k->Ф_состязан ? DicCompForm::where('value', $k->Ф_состязан)->first()?->id : null,
                        'ssub_rf_id' => $k->Регион ? DicSsubRf::where('value', $k->Регион)->first()?->id : null,
                        's_konkurs' => $k->Площадь ? str_ireplace(['/', ',', ' '], '.', $k->Площадь) : null,
                        //TODO id_переходящий Сущность:konkurs.id DEFAULT NULL --ebd_gis.konkursXX.Переход --Cсылка на предыдущий (При импорте из 2019 переходящий = NULL. ebd_gis.konkursXX.Переход нужно разбирать по запятой и привязывать год_включения-1)
                        //'prev_konkurs_id'
                        //'prev_txt'
                        'arctic_zone_id' => $k->Арктическа ? DicArcticZone::where('value', $k->Арктическа)->first()?->id : null,
                        //TODO --нужно преврящать в таблицу или на первом этапе оставить в виде строки
                        // 'reserves_n'
                        // 'reserves_g'
                        // 'reserves_k'
                        // 'resource_n'
                        // 'resource_g'
                        // 'resource_k'
                        'comment' => $k->Примечание,
                        //TODO
                        //'geom' => $k->geom
                    ]);
            
                    $newKopnkurs->refresh();

                    if( count(Konkurs::where('src_hash', $newKopnkurs->src_hash)->get()) > 1)
                    {
                        $newKopnkurs->delete();
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
                    dd($k);
                    continue;
                }
            }
        
        dump("  Konkurs frm " . $konkursTableName . ": Added " . $newCount . ', unsaved ' . $unsavedCount);

        return [$newCount, $unsavedCount];
    }
}