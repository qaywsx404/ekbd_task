<?php

namespace App\Http\Controllers\importcontrollers;

use App\Http\Controllers\Controller;
use App\Models\ebd_ekbd\dictionaries\DicArcticZone;
use App\Models\ebd_ekbd\dictionaries\DicLicenseType;
use App\Models\ebd_ekbd\dictionaries\DicPi;
use App\Models\ebd_ekbd\dictionaries\DicPurpose;
use App\Models\ebd_ekbd\dictionaries\DicReason;
use App\Models\ebd_ekbd\dictionaries\DicSsubRf;
use App\Models\ebd_ekbd\License;
use App\Models\ebd_ekbd\rel\RelLicensePi;
use Illuminate\Support\Facades\Log;

class LicenseImportController extends Controller
{
    /** Лицензии с ненайдеными прев. лиц. */
    private static $unfLics = [];
    /** Доп. инф. */
    private static $showInf = false;
    /** Счетчики добавленных записей */
    private static $newCount = 0;
    /** Счетчики добавленных записей */
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
            dump("Импорт License:");
            Log::channel('importlog')->info("Импорт License:");
        }

        // lic_exp_ln
        self::importFromLicTable('LicExpLn');
        // lic_exp_pln
        self::importFromLicTable('LicExpPln');
        // lic_exp_pt
        self::importFromLicTable('LicExpPt');
        // lic_pln
        self::importFromLicTable('LicPln');
        // lic_pt
        self::importFromLicTable('LicPt');
        
        self::setPrevLics();

        if(self::$showInf)
        {
            $mes = ("RelLicensePi добавлено записей: ". self::$RelNewCount . ", всего: " . RelLicensePi::count());
            dump($mes);
            Log::channel('importlog')->info($mes);

            $mes = ("License импорт завершен: добавлено " . self::$newCount . ', обновлено ' . self::$updCount . ', не добавлено ' . self::$unsCount);
            dump($mes);
            Log::channel('importlog')->info($mes);
        }
    }
    /** 
     * Импорт записей из 5 таблиц ebd_gis.lic*
     * 
     * @param string $licTableName название модели таблицы импорта
     * 
     * */
    private static function importFromLicTable(string $licTableName)
    {
        $newCount = $updCount = $unsCount = 0;
        
        $licTableModel = new ('App\Models\ebd_gis\\' . $licTableName )();
        
        foreach($licTableModel::all() as $l)
        {
            self::$hasErr = false;

            $src_hash = md5($l->Серия . $l->Номер_лиц . $l->Тип);
            $prevLic = self::getPrevLicId($l->Старая_лиц, $licTableName, $l);

            if(!License::where('src_hash', $src_hash)->exists())
            {
                $newLic = License::make([
                    'src_hash' => $src_hash,
                    'name' => $l->Название,
                    'series' => $l->Серия,
                    'number' => $l->Номер_лиц,
                    'license_type_id' => $l->Тип ? (DicLicenseType::where('value', $l->Тип)->first()?->id ?? self::getEx('license_type_id', $l->Тип, $licTableName, $l)) : null,
                    'status' => null, // нет данных для заполнения
                    'purpose_id' => $l->Цель ? (DicPurpose::where('value', $l->Цель)->first()?->id ?? self::getEx('purpose_id', $l->Цель, $licTableName, $l)) : null,
                    'reason_id' => $l->Осн_выдачи ? (DicReason::where('value', $l->Осн_выдачи)->first()?->id ?? self::getEx('reason_id', $l->Осн_выдачи, $licTableName, $l)) : null,
                    'rdate' => $l->Дата_регис,
                    'validity' => $l->Срок_дейст,
                    'suser' => $l->Недропольз,
                    'suser_inn' => $l->ИНН,
                    'suser_adr' => $l->Адрес,
                    'founder' => $l->Учредители,
                    'pcomp' => $l->Гол_предпр,
                    'prev_license_id' => $prevLic,
                    'ssub_rf_code' => $l->Код_СФ,
                    'ssub_rf_id' => $l->Назв_СФ ? (DicSsubRf::where('region_name', $l->Назв_СФ)->first()?->id ?? self::getEx('ssub_rf_id', $l->Назв_СФ, $licTableName, $l)) : null,
                    'arctic_zone_id' => $l->Аркт_зона ? (DicArcticZone::where('value', $l->Аркт_зона)->first()?->id ?? self::getEx('arctic_zone_id', $l->Аркт_зона, $licTableName, $l)) : null,
                    's_license' => $l->s_лиц,
                    'comment' => $l->Примечание,
                    'geom' => $l->geom
                ]);

                if(!self::$hasErr)
                {
                    if( ($prevLic == null) && ($l->Старая_лиц != null) )
                    {
                        self::$unfLics[$newLic->id] = $l->Старая_лиц;
                    }

                    $newLic->save();
                    $newLic->refresh();
                    $newCount++;
                    
                    foreach(self::splitPi($l->Пол_ископ) as $pi)
                    {
                        if(($newLic->id != null) && ($newLic->id != ""))
                        {
                            $newRel = RelLicensePi::firstOrCreate([
                                'license_id' => $newLic->id,
                                'pi_id' => DicPi::where('value', $pi)->first()?->id
                            ]);

                            if($newRel->wasRecentlyCreated) self::$RelNewCount++;
                        }
                    }
                }
                else
                {
                    $unsCount++;
                }
            }
            else
            {
                $curLic = License::where('src_hash', $src_hash)->first();

                $curLic->name = $l->Название;
                $curLic->purpose_id = $l->Цель ? (DicPurpose::where('value', $l->Цель)->first()?->id ?? self::getEx('purpose_id', $l->Цель, $licTableName, $l)) : null;
                $curLic->reason_id = $l->Осн_выдачи ? (DicReason::where('value', $l->Осн_выдачи)->first()?->id ?? self::getEx('reason_id', $l->Осн_выдачи, $licTableName, $l)) : null;
                $curLic->rdate = $l->Дата_регис;
                $curLic->validity = $l->Срок_дейст;
                $curLic->suser = $l->Недропольз;
                $curLic->suser_inn = $l->ИНН;
                $curLic->suser_adr = $l->Адрес;
                $curLic->founder = $l->Учредители;
                $curLic->pcomp = $l->Гол_предпр;
                $curLic->prev_license_id = $prevLic; //TODO
                $curLic->ssub_rf_code = $l->Код_СФ;
                $curLic->ssub_rf_id = $l->Назв_СФ ? (DicSsubRf::where('region_name', $l->Назв_СФ)->first()?->id ?? self::getEx('ssub_rf_id', $l->Назв_СФ, $licTableName, $l)) : null;
                $curLic->arctic_zone_id = $l->Аркт_зона ? (DicArcticZone::where('value', $l->Аркт_зона)->first()?->id ?? self::getEx('arctic_zone_id', $l->Аркт_зона, $licTableName, $l)) : null;
                $curLic->s_license = $l->s_лиц;
                $curLic->comment = $l->Примечание;
                $curLic->geom = $l->geom;

                if(!self::$hasErr)
                {
                    //TODO
                    foreach(self::splitPi($l->Пол_ископ) as $pi)
                    {
                        if(($curLic->id != null) && ($curLic->id != ""))
                        {
                            $newRel = RelLicensePi::firstOrCreate([
                                'license_id' => $curLic->id,
                                'pi_id' => DicPi::where('value', $pi)->first()?->id
                            ]);

                            if($newRel->wasRecentlyCreated) self::$RelNewCount++;
                        }
                    }
                    
                    $curLic->save();
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
            $mes = "License из таблицы $licTableName(".$licTableModel::count()."): добавлено $newCount, обновлено $updCount, не добавлено $unsCount";
            dump($mes);
            Log::channel('importlog')->info($mes);
        }

        self::$newCount = self::$newCount + $newCount;
        self::$updCount = self::$updCount + $updCount;
        self::$unsCount = self::$unsCount + $unsCount;

        return 0;
    }
    private static function setPrevLics() : int {
        $counter = 0;

        foreach(self::$unfLics as $id => $pl)
        {
            if($id && $pl)
            {   
                $l = License::find($id);
                
                $l->prev_license_id = self::getPrevLicId($pl);
                $l->save();

                if($l->prev_license_id != null)
                    $counter++;
            }
        }

        return $counter;
    }
    private static function splitPi($str) : array {
        $pis = str_ireplace([', ', ',   ', ',  ', ', '], ',', $str);
        return explode(',', $pis);
    }
    private static function splitPrevLic(?string $str) : ?array {
        if($str)
        {
            $spltPrevLic=[];
            preg_match('/(^[А-Я]*) ?(\d*) ? ?([А-Я]+$)/u', $str, $spltPrevLic, 0);
            return $spltPrevLic;
        }
        return null;
    }
    private static function getPrevLicId(?string $prevLicStr, string $licTableName = "-", &$gLic = null) : ?string {
        if($prevLicStr) {
            $spltPrevLic = self::splitPrevLic($prevLicStr);

            if(count($spltPrevLic) == 4) {
                $pl = License::where('series', $spltPrevLic[1])
                                ->where('number', $spltPrevLic[2])
                                ->where('license_type_id', DicLicenseType::where('value', $spltPrevLic[3])->first()?->id)
                                ->first() 
                                //?? self::getEx('prev_license_id', $prevLicStr, $licTableName, $gLic)
                                ;

                if($pl) return $pl->id;
                else
                {
                    return null;
                }
            }
            else
            {
                return self::getEx('prev_license_id', $prevLicStr, $licTableName, $gLic);
            }       
        }
        return null;
    }
    private static function getEx($attrName, $attrVal, $tableName, &$licX)
    {
        if($attrVal)
        {
            $attrArr = $licX->attributesToArray();
            unset($attrArr['geom']);
            $ser = json_encode($attrArr, JSON_UNESCAPED_UNICODE);

            $mes = ("- Неверная строка или не найдена запись для License: $attrName : \"$attrVal\"\r\nзапись из таблицы $tableName: $ser\r\n");
            
            //echo "\v".$mes;
            Log::channel('importerrlog')->error($mes);

            self::$hasErr = true;
        }
        
        return null;
    }
}