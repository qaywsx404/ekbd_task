<?php

namespace App\Http\Controllers\importcontrollers;

use App\Http\Controllers\Controller;
use App\Models\ebd_ekbd\dictionaries\DicZapovednikCategory;
use App\Models\ebd_ekbd\dictionaries\DicZapovednikImportance;
use App\Models\ebd_ekbd\dictionaries\DicZapovednikProfile;
use App\Models\ebd_ekbd\dictionaries\DicZapovednikState;
use App\Models\ebd_ekbd\dictionaries\DicSsubRf;
use App\Models\ebd_ekbd\Zapovednik;
use Exception;

class ZapovednikImportController extends Controller
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
                    $newZap = Zapovednik::make([
                        'name' => $z->Название,
                        'zapovednik_category_id' => $z->категория_ ? DicZapovednikCategory::where('value',  $z->категория_)->first()?->id : null,
                        'zapovednik_importance_id' => $z->значение_о ? DicZapovednikImportance::where('value',  $z->значение_о)->first()?->id : null,
                        'zapovednik_profile_id' => $z->Профиль ? DicZapovednikProfile::where('value',  $z->Профиль)->first()?->id : null,
                        'zapovednik_state_id' => $z->Текущий_ст ? DicZapovednikState::where('value',  $z->Текущий_ст)->first()?->id : null,
                        'ssub_rf_id' => $z->Регион ? DicSsubRf::where('value',  $z->Регион)->first()?->id : null,
                        's_zapovednik' => self::getS($z->Площадь_км),
                        'ohr_zona' => self::getS($z->Охранная_з),
                        'rdate' => self::getRDate($z->Дата_созда),
                        'comment' => $z->Примечание,
                        'geom' => $z->geom
                    ]);
                    
                    $newZap->src_hash = md5($z->number . $zapTableName);
                
                    if(!Zapovednik::where('src_hash', $newZap->src_hash)->exists())
                    {
                        $newZap->save();
                        $newCount++;
                    }
                    else
                    {
                        $unsavedCount++;
                    }
                }
                catch(Exception $e)
                {
                    dd($e);
                }
            }

        if(self::$showInf) dump("   Zapovednik frm " . $zapTableName .' ('.$zapTableModel::count().') '.   ": Added " . $newCount . ', unsaved ' . $unsavedCount);

        return [$newCount, $unsavedCount];
    }
    private static function getS(?string $strS) : ?float
    {
        if($strS != null && $strS != "")
        {
            $strS = str_ireplace([' ', ',', '..'], '.', $strS);
            if(str_contains($strS, '+'))
            {
                if(self::$showInf) dump("       Неверное знаечние S строка: " . $strS . ' ->null');
                return null;
            }

            return floatval($strS);
        }

        return null;
    }
    private static function getRDate(?string $str) : ?string
    {
        if($str != null && $str != "")
        {
            //TODO
            if($str == '19.041983') $str = '19.04.1983';
            if($str == '07.16.1988') $str = '16.07.2988';
            if($str == '21.08.1991.') $str = '21.08.1991';
            if($str == '30.08.2017г.') $str = '30.08.2017';
            if(strlen($str) == 4) $str = '01.01.'.$str;

            if(strlen($str) == 10)
                return $str;

            if(self::$showInf) dump("       Дата неверного формата : " . $str . " ->null");
        }
        
        return null;
    }
}