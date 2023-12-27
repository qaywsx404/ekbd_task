<?php

namespace App\Http\Controllers\importcontrollers;

use App\Http\Controllers\Controller;
use App\Models\ebd_ekbd\dictionaries\DicZapovednikCategory;
use App\Models\ebd_ekbd\dictionaries\DicZapovednikImportance;
use App\Models\ebd_ekbd\dictionaries\DicZapovednikProfile;
use App\Models\ebd_ekbd\dictionaries\DicZapovednikState;
use App\Models\ebd_ekbd\dictionaries\DicSsubRf;
use App\Models\ebd_ekbd\Zapovednik;
use DateTime;
use Illuminate\Support\Facades\Log;

class ZapovednikImportController extends Controller
{
        /** Доп. инф. */
        private static $showInf = false;
        /** Счетчики добавленных записей */
        private static $newCount = 0;
        /** Счетчики добавленных записей */
        private static $updCount = 0;
        /** Счетчики не добавленных записей */
        private static $unsCount = 0;
        /** Флаг ошибки */
        private static $hasErr = false;

    /**
     * Начать импорт
     * 
     * @param bool $showInf  `true`  - показывать доп. инф.
     *                       `false` - не показывать доп. инф.
     */
    public static function import(bool $showInf = false)
    {
        self::$showInf = $showInf;
        
        if(self::$showInf)
        {
            dump("Импорт Zapovednik:");
            Log::channel('importlog')->info("Импорт Zapovednik:");
        }

        //zapovedniki_ln
        self::importFromTable('ZapovednikiLn');
        //zapovedniki_pln
        self::importFromTable('ZapovednikiPln');
        //zapovedniki_pt
        self::importFromTable('ZapovednikiPt');

        if(self::$showInf)
        {
            $mes = ("Zapovednik импорт завершен: добавлено " . self::$newCount . ', обновлено ' . self::$updCount . ', не добавлено ' . self::$unsCount);
            dump($mes);
            Log::channel('importlog')->info($mes);
        }
    }

    /**  Импорт записей из таблиц  ebd_gis.zapovedniki_* */
    private static function importFromTable(string $zapTableName)
    {
        $newCount = $updCount = $unsCount = 0;

        $zapTableModel = new (trim('App\Models\ebd_gis\ ') . $zapTableName )();

        foreach($zapTableModel::all() as $z)
        {
            self::$hasErr = false;

            $src_hash = md5($z->number . $zapTableName);

            if(!Zapovednik::where('src_hash', $src_hash)->exists())
            {
                $newZap = Zapovednik::make([
                    'src_hash' => $src_hash,
                    'name' => $z->Название,
                    'zapovednik_category_id' => $z->категория_ ? (DicZapovednikCategory::where('value',  $z->категория_)->first()?->id ?? self::getEx('zapovednik_category_id', $z->категория_, $z)) : null,
                    'zapovednik_importance_id' => $z->значение_о ? (DicZapovednikImportance::where('value',  $z->значение_о)->first()?->id ?? self::getEx('zapovednik_importance_id', $z->значение_о, $z)): null,
                    'zapovednik_profile_id' => $z->Профиль ? (DicZapovednikProfile::where('value',  $z->Профиль)->first()?->id ?? self::getEx('zapovednik_profile_id', $z->Профиль, $z)) : null,
                    'zapovednik_state_id' => $z->Текущий_ст ? (DicZapovednikState::where('value',  $z->Текущий_ст)->first()?->id ?? self::getEx('zapovednik_state_id', $z->Текущий_ст, $z)) : null,
                    'ssub_rf_id' => self::getSsubId($z->Регион, $z, $zapTableName),
                    's_zapovednik' => self::getS($z->Площадь_км, 's_zapovednik', $zapTableName, $z),
                    'ohr_zona' => self::getS($z->Охранная_з, 'ohr_zona', $zapTableName, $z),
                    'rdate' => self::getRDate($z->Дата_созда, $zapTableName, $z),
                    'comment' => $z->Примечание,
                    'geom' => $z->geom
                ]);

                if(!self::$hasErr)
                {
                    $newZap->save();
                    $newCount++;
                }
                else
                {
                    $unsCount++;
                }
            }
            else
            {
                $curZap = Zapovednik::where('src_hash', $src_hash)->first();

                $curZap->name = $z->Название;
                $curZap->zapovednik_category_id = $z->категория_ ? (DicZapovednikCategory::where('value',  $z->категория_)->first()?->id ?? self::getEx('zapovednik_category_id', $z->категория_, $z)) : null;
                $curZap->zapovednik_importance_id = $z->значение_о ? (DicZapovednikImportance::where('value',  $z->значение_о)->first()?->id ?? self::getEx('zapovednik_importance_id', $z->значение_о, $z)): null;
                $curZap->zapovednik_profile_id = $z->Профиль ? (DicZapovednikProfile::where('value',  $z->Профиль)->first()?->id ?? self::getEx('zapovednik_profile_id', $z->Профиль, $z)) : null;
                $curZap->zapovednik_state_id = $z->Текущий_ст ? (DicZapovednikState::where('value',  $z->Текущий_ст)->first()?->id ?? self::getEx('zapovednik_state_id', $z->Текущий_ст, $z)) : null;
                $curZap->ssub_rf_id = self::getSsubId($z->Регион, $z, $zapTableName);
                $curZap->s_zapovednik = self::getS($z->Площадь_км, 's_zapovednik', $zapTableName, $z);
                $curZap->ohr_zona = self::getS($z->Охранная_з, 'ohr_zona', $zapTableName, $z);
                $curZap->rdate = self::getRDate($z->Дата_созда, $zapTableName, $z);
                $curZap->comment = $z->Примечание;
                $curZap->geom = $z->geom;

                if(!self::$hasErr)
                {
                    $curZap->save();
                    $updCount++;
                }
                else
                {
                    $unsCount++;
                }
            }
        }

        if(self::$showInf)
        {
            $mes = "Zapovednik из таблицы $zapTableName(".$zapTableModel::count()."): добавлено $newCount, обновлено $updCount, не добавлено $unsCount";
            dump($mes);
            Log::channel('importlog')->info($mes);
        }

        self::$newCount = self::$newCount + $newCount;
        self::$updCount = self::$updCount + $updCount;
        self::$unsCount = self::$unsCount + $unsCount;

        return 0;
    }
    private static function getS(?string $strS, $attrName, $tableName, &$z) : ?float
    {
        if($strS != null && $strS != "")
        {
            $strS = str_ireplace([' ', ',', '..'], '.', $strS);
            if(str_contains($strS, '+'))
            {
                return self::getEx($attrName, $strS, $tableName, $z);
            }

            return floatval($strS);
        }

        return null;
    }
    private static function getRDate(?string $str, $tableName, &$z) : ?string
    {
        if($str != null && $str != "")
        {
            if( (strlen($str) == 10) && ($d = DateTime::createFromFormat('d.m.Y', $str)) && !(($str[3] == '1') && ($str[4] > 2)) )
            {
                return $str;
            }

            return self::getEx('rdate', $str, $tableName, $z);
        }
        
        return null;
    }
    private static function getSsubId(?string $ssub, &$zap, $tableName) : ?string {
        if($ssub)
        {
            // $ssub = str_ireplace('облать', 'область', $ssub);
            // $ssub = str_ireplace('  ', ' ', $ssub);
            // if(str_contains($ssub, 'Шельф')) $ssub = 'Шельф';
            // if(str_contains($ssub, 'Красноярский край')) $ssub = 'Красноярский край';
            // if(str_contains($ssub, 'Эвенкийский')) $ssub = 'Красноярский край';
            // if(str_contains($ssub, 'Алания')) $ssub = 'Алания';
            // if(str_contains($ssub, 'Волгоградская')) $ssub = 'Волгоградская';
            // if(str_contains($ssub, 'Ямало-Ненецкий')) $ssub = 'Ямало-Ненецкий';
            // if($ssub == 'Ненецкий АО') $ssub = 'Ненецкий автономный округ';
            // if(str_contains($ssub, 'о.Домашний')) $ssub = 'Красноярский край';
            // if(str_contains($ssub, 'Кандалакшский залив')) $ssub = 'Карелия'; // ?
            // if(str_contains($ssub, 'Югра')) $ssub = 'Югра';
            // if(str_contains($ssub, 'Камчатка')) $ssub = 'Камчатский край';
            // if(str_contains($ssub, 'Саратовская рбласть')) $ssub = 'Саратовская область';
            // if(str_contains($ssub, 'Таймырский (Долгано-Ненецкий) а.о')) $ssub = 'Красноярский край'; // ?
            // if(str_contains($ssub, 'Оренбурская область')) $ssub = 'Оренбургская область';
            // if(str_contains($ssub, 'п-ов Парижской Коммуны')) $ssub = 'Красноярский край';
            // if(str_contains($ssub, 'о.Октябрьской Революции')) $ssub = 'Красноярский край';
            // if(str_contains($ssub, 'Вологодская область Ярославская область')) $ssub = 'Вологодская область';
            // if(str_contains($ssub, 'Баштортостан')) $ssub = 'Башкортостан';
            // if(str_contains($ssub, 'о. Большевик')) $ssub = 'Красноярский край';

            $ssubId = DicSsubRf::where('region_name', $ssub)
                                ->orWhere('region_name', 'ilike', '%'.$ssub.'%')
                                ->first()?->id;
            
            return $ssubId ?? self::getEx('ssub_rf_id', $ssub, $tableName, $zap);
        } 

        return null;
    }
    private static function getEx($attrName, $attrVal, $tableName, &$zapX)
    {
        if($attrVal)
        {
            $attrArr = $zapX->attributesToArray();
            unset($attrArr['geom']);
            $ser = json_encode($attrArr, JSON_UNESCAPED_UNICODE);

            $mes = ("- Неверная строка или не найдена запись для Zapovednik: $attrName : \"$attrVal\"\r\nзапись из таблицы $tableName: $ser\r\n");
            
            //echo "\v".$mes;
            Log::channel('importerrlog')->error($mes);

            self::$hasErr = true;
        }
        
        return null;
    }
}