<?php

namespace App\Http\Controllers\importcontrollers;

use App\Http\Controllers\Controller;
use App\Models\ebd_ekbd\dictionaries\DicCompForm;
use App\Models\ebd_ekbd\dictionaries\DicLicenseType;
use App\Models\ebd_ekbd\dictionaries\DicPurpose;
use App\Models\ebd_ekbd\dictionaries\DicZapovednikCategory;
use App\Models\ebd_ekbd\dictionaries\DicZapovednikImportance;
use App\Models\ebd_ekbd\dictionaries\DicZapovednikProfile;
use App\Models\ebd_ekbd\dictionaries\DicZapovednikState;
use App\Models\ebd_ekbd\Konkurs;
use App\Models\ebd_ekbd\dictionaries\DicArcticZone;
use App\Models\ebd_ekbd\dictionaries\DicSsubRf;
use App\Models\ebd_ekbd\Zapovednik;
use Exception;

class ZapovednikImportController extends Controller
{
    public static function import()
    {
        $newCount = $unsavedCount = 0;

        //zapovedniki_ln
        $a = self::importFromTable('ZapovednikiLn');
            $newCount += $a[0]; $unsavedCount += $a[1];
        //zapovedniki_pln
        $a = self::importFromTable('ZapovednikiPln');
            $newCount += $a[0]; $unsavedCount += $a[1];
        //zapovedniki_pt
        $a = self::importFromTable('ZapovednikiPt');
            $newCount += $a[0]; $unsavedCount += $a[1];

        dump("Zapovednik total: Added " . $newCount . ', unsaved ' . $unsavedCount);
    }

    /**  Импорт записей из таблиц  ebd_gis.zapovedniki_* */
    private static function importFromTable(string $zapTableName) : array
    {
        $newCount = $unsavedCount = 0;

        $zapTableModel = new (trim('App\Models\ebd_gis\ ') . $zapTableName )();

            foreach($zapTableModel::all() as $z)
            {
                try
                {
                    $newZap = Zapovednik::create([
                        //TODO
                        //'vid' => ?
                        'name' => $z->Название,
                        'zapovednik_category_id' => $z->категория_ ? DicZapovednikCategory::where('value',  $z->категория_)->first()?->id : null,
                        'zapovednik_importance_id' => $z->значение_о ? DicZapovednikImportance::where('value',  $z->значение_о)->first()?->id : null,
                        'zapovednik_profile_id' => $z->Профиль ? DicZapovednikProfile::where('value',  $z->Профиль)->first()?->id : null,
                        'zapovednik_state_id' => $z->Текущий_ст ? DicZapovednikState::where('value',  $z->Текущий_ст)->first()?->id : null,
                        //TODO
                        'ssub_rf_id' => $z->Регион ? DicSsubRf::where('value',  $z->Регион)->first()?->id : null,
                        //TODO
                        //'s_zapovednik' => $z->Площадь_км ? str_ireplace([' ', ',', '..'], '.', $z->Площадь_км) : null,
                        //TODO
                        //'ohr_zona' => $z->Охранная_з,
                        //TODO
                        //'rdate' => $z->Дата_созда,
                        'comment' => $z->Примечание,
                        //TODO
                        //'geom' => $z->geom
                    ]);
            
                    $newZap->refresh();

                    if( count(Zapovednik::where('src_hash', $newZap->src_hash)->get()) > 1)
                    {
                        $newZap->delete();
                        $unsavedCount++;
                    }
                    else
                    {
                        $newCount++;
                    }
                }
                catch(Exception $e)
                {
                    dd($e->getMessage());
                    //dd($z);
                    //continue;
                }
            }
        
        dump("  Zapovedniki frm " . $zapTableName . ": Added " . $newCount . ', unsaved ' . $unsavedCount);

        return [$newCount, $unsavedCount];
    }
}