<?php

namespace App\Http\Controllers\importcontrollers;

use App\Http\Controllers\Controller;
use App\Models\ebd_ekbd\dictionaries\DicCompForm;
use App\Models\ebd_ekbd\dictionaries\DicLicenseType;
use App\Models\ebd_ekbd\dictionaries\DicPi;
use App\Models\ebd_ekbd\dictionaries\DicPurpose;
use App\Models\ebd_ekbd\Konkurs;
use App\Models\ebd_ekbd\dictionaries\DicArcticZone;
use App\Models\ebd_ekbd\dictionaries\DicSsubRf;
use App\Models\ebd_ekbd\rel\RelKonkursPi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KonkursImportController extends Controller
{
    /** Доп. инф. */
    private static $showInf = false;
    /** Счетчики добавленных записей */
    private static $newCount = 0;
    /** Счетчики обновленных записей */
    private static $updCount = 0;
    /** Счетчики не добавленных записей */
    private static $unsCount = 0;
    /** Счетчики добавленных записей RelLicensePi */
    private static $RelNewCount = 0;
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
            dump("Импорт Konkurs:");
            Log::channel('importlog')->info("Импорт Konkurs:");
            Log::channel('importlog')->info("Очистка Konkurs: ".Konkurs::count());
            Log::channel('importlog')->info("Очистка RelKonkursPi: ".RelKonkursPi::count());
        }

        Konkurs::truncate();
        RelKonkursPi::truncate();

        //konkurs19
        self::importFromTable('Konkurs19');
        //konkurs20
        self::importFromTable('Konkurs20');
        //konkurs21
        self::importFromTable('Konkurs21');
        //konkurs22
        self::importFromTable('Konkurs22');
        //konkurs23
        self::importFromTable('Konkurs23');

        if(self::$showInf)
        {
            $mes = ("Konkurs импорт завершен: добавлено " . self::$newCount . ', не добавлено ' . self::$unsCount);
            dump($mes);
            Log::channel('importlog')->info($mes);

            $mes = ("Konkurs_pi: добавлено " . self::$RelNewCount . ', всего ' . RelKonkursPi::count());
            dump($mes);
            Log::channel('importlog')->info($mes);
        }
    }

    /**  Импорт записей из таблиц  ebd_gis.konkurs* */
    private static function importFromTable(string $konkursTableName)
    {
        $newCount = $unsCount = 0;

        $konkursTableModel = new (trim('App\Models\ebd_gis\ ') . $konkursTableName )();
        
        foreach($konkursTableModel::all() as $k)
        {
            self::$hasErr = false;

            $src_hash = md5($k->gid . $konkursTableName);

            if(!Konkurs::where('src_hash', $src_hash)->exists())
            {
                $newKonkurs = Konkurs::make([
                    'src_hash' => $src_hash,
                    'name' => $k->Название_у,
                    'license_type_id' => $k->Тип_лиц ? (DicLicenseType::where('value', $k->Тип_лиц)->first()?->id ?? self::getEx('license_type_id', $k->Тип_лиц)) : null,
                    'purpose_id' => $k->Цель ? (DicPurpose::where('value', $k->Цель)->first()?->id ?? self::getEx('purpose_id',  $k->Цель)) : null,
                    'ryear' => self::getRYear($k->Год_включ),
                    'comp_form_id' => $k->Ф_состязан ? (DicCompForm::where('value', $k->Ф_состязан)->first()?->id ?? self::getEx('comp_form_id',  $k->Ф_состязан)) : null,
                    'ssub_rf_id' => self::getSsubId($k->Регион, $konkursTableName, $k),
                    's_konkurs' => $k->Площадь ? str_ireplace(['/', ',', ' '], '.', $k->Площадь) : null,
                    'prev_konkurs_id' => self::getPrevId($k->Название_у, self::getRYear($k->Год_включ)),
                    'prev_txt' => $k->Переход,
                    'arctic_zone_id' => $k->Арктическа ? DicArcticZone::where('value', $k->Арктическа)->first()?->id : null,
                    'reserves_n' => $k->Запасы_н_м,
                    'reserves_g' => $k->Запасы_газ,
                    'reserves_k' => $k->Запасы_к,
                    'resource_n' => $k->Ресурсы_н,
                    'resource_g' => $k->Ресурсы_г,
                    'resource_k' => $k->Ресурсы_к,
                    'comment' => $k->Примечание,
                    'geom' => $konkursTableName != 'Konkurs19' ? $k->geom : (DB::select('SELECT public.ST_TRANSFORM(?::text, 7683) as geom',[$k->geom])[0]->geom)
                ]);

                if(!self::$hasErr)
                {
                    $newKonkurs->save();
                    $newKonkurs->refresh();
                    $newCount++;

                    foreach(self::splitPi($k->Полезн_иск) as $pi)
                    {
                        if($newKonkurs->id && $pi)
                        {
                            if($newKonkurs->id != null && $newKonkurs->id != "")
                            {
                                $newRel = RelKonkursPi::firstOrCreate([
                                    'konkurs_id' => $newKonkurs->id,
                                    'pi_id' => DicPi::where('value', $pi)->first()?->id
                                ]);

                                if($newRel->wasRecentlyCreated) self::$RelNewCount++;
                            }
                        }
                    }
                }
                else
                {
                    $unsCount++;
                }
            }
        }

        if(self::$showInf)
        {
            $mes = "Konkurs из таблицы $konkursTableName(".$konkursTableModel::count()."): добавлено $newCount, не добавлено $unsCount";
            dump($mes);
            Log::channel('importlog')->info($mes);
        }

        self::$newCount = self::$newCount + $newCount;
        self::$unsCount = self::$unsCount + $unsCount;

        return 0;
    }
    private static function splitPi($str) : array {
        $pis = str_ireplace(', ', ',', $str);
        return explode(',', $pis);
    }
    private static function splitRes($str) : array {
        if(empty($str))
            return [];
        
        $str = preg_replace('/(.?\d),(\d+)/m', "$1.$2", $str);
        $str = str_ireplace('А', 'A', $str); // рус -> анг
        $str = str_ireplace('В', 'B', $str);
        $str = str_ireplace('С', 'C', $str);
        $str = str_ireplace(' ', '', $str);
        $str = str_ireplace(',', ';', $str);
        $res  = [];
        foreach(explode(';', $str) as $r)
        {
            $arr = explode('-', $r);
            $res[$arr[0]] = $arr[1];
        }

        return $res;
    }
    private static function getPrevId(?string $name, ?string $ryear) : ?string {
        if(intval($ryear) && $name != null)
        {
            return Konkurs::where('name', $name)
                          ->where('ryear', intval($ryear)-1)->first()?->id; //TODO ?? self::getEx('prev_konkurs_id', );
        }

        return null;
    }
    private static function getRYear(string $yearStr) : ?string {
        if($yearStr != null && $yearStr != "")
        {
            $res = "";

            preg_match_all('/\s?(\d+)/m', $yearStr, $res, 0);

            return $res[1][0];
        }

        return null;
    }
    private static function getSsubId(?string $ssub, $tableName, $konkursX) : ?string {
        if($ssub)
        {
            // if(str_contains($ssub, 'Шельф')) $ssub = 'Шельф Российской Федерации';
            // if(str_contains($ssub, 'Ямало-Ненецкий АО')) $ssub = 'Ямало-Ненецкий автономный округ';
            // if(str_contains($ssub, 'Республика Саха(Якутия)')) $ssub = 'Республика Саха';

            $ssubId = DicSsubRf::where('region_name', $ssub)
                                //->orWhere('region_name', 'ilike', '%'.$ssub.'%')
                                ->orWhere('region_name', $ssub)
                                ->first()?->id;
            
            if($ssubId) return $ssubId;
            else
            {
                return self::getEx('ssub_rf_id', $ssub, $tableName, $konkursX);
            }
        } 
        return null;
    }
    private static function getEx($attrName, $attrVal, $tableName, &$konkursX)
    {
        if($attrVal)
        {
            $attrArr = $konkursX->attributesToArray();
            unset($attrArr['geom']);
            $ser = json_encode($attrArr, JSON_UNESCAPED_UNICODE);

            $mes = ("- Неверная строка или не найдена запись для Konkurs: $attrName : \"$attrVal\"\r\nзапись из таблицы $tableName: $ser\r\n");
            
            //echo "\v".$mes;
            Log::channel('importerrlog')->error($mes);

            self::$hasErr = true;
        }
        
        return null;
    }
}