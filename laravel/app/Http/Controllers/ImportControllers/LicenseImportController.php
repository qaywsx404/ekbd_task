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
use ErrorException;
use Exception;

class LicenseImportController extends Controller
{
    /** Лицензии с ненайдеными прев. лиц. */
    private static $unfLics = [];
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
        $newCount = $unsavedCount = $RelNewCount = 0;

        if(self::$showInf) dump("Импорт License:");

        // lic_exp_ln
        $a = self::importFromLicTable('LicExpLn');
            $newCount += $a[0]; $unsavedCount += $a[1]; $RelNewCount += $a[2];
        // lic_exp_pln
        $a = self::importFromLicTable('LicExpPln');
            $newCount += $a[0]; $unsavedCount += $a[1]; $RelNewCount += $a[2];
        // lic_exp_pt
        $a = self::importFromLicTable('LicExpPt');
            $newCount += $a[0]; $unsavedCount += $a[1]; $RelNewCount += $a[2];
        // lic_pln
        $a = self::importFromLicTable('LicPln');
            $newCount += $a[0]; $unsavedCount += $a[1]; $RelNewCount += $a[2];
        // lic_pt
        $a = self::importFromLicTable('LicPt');
            $newCount += $a[0]; $unsavedCount += $a[1]; $RelNewCount += $a[2];
        
        $plc = self::setPrevLics();

        if(self::$showInf)
        {
            echo ("\tprev_license_id добавлено: $plc, не найдено подходящих лицензий: " . count(self::$unfLics)-$plc . "\r\n");
            echo "\t"; dump("RelLicensePi сделано записей: $RelNewCount, всего: " . RelLicensePi::count());
        }
        dump("License импорт завершен: добавлено " . $newCount . ', не добавлено ' . $unsavedCount);
    }
    /** 
     * Импорт записей из 5 таблиц ebd_gis.lic*
     * 
     * @param string $licTableName название модели таблицы импорта
     * 
     * */
    private static function importFromLicTable(string $licTableName) : array
    {
        $newCount = $unsavedCount = $RelNewCount = 0;
        
        $licTableModel = new ('App\Models\ebd_gis\\' . $licTableName )();
        
        try
        {
            foreach($licTableModel::all() as $l) {
                
                $prevLic = self::getPrevLicId($l->Старая_лиц, $licTableName, $l);

                $newLic = License::make([
                    'name' => $l->Название,
                    'series' => $l->Серия,
                    'number' => $l->Номер_лиц,
                    'license_type_id' => self::getLicTypeId($l->Тип, $licTableName),
                    'status' => null, // нет данных для заполнения
                    'purpose_id' =>  self::getPurposeId($l->Цель),
                    'reason_id' =>  self::getReasId($l->Осн_выдачи),
                    'rdate' => $l->Дата_регис,
                    'validity' => $l->Срок_дейст,
                    'suser' => $l->Недропольз,
                    'suser_inn' => $l->ИНН,
                    'suser_adr' => $l->Адрес,
                    'founder' => $l->Учредители,
                    'pcomp' => $l->Гол_предпр,
                    'prev_license_id' => $prevLic,
                    'ssub_rf_code' => $l->Код_СФ,
                    'ssub_rf_id' => self::getSsubId($l->Назв_СФ),
                    'arctic_zone_id' => DicArcticZone::where('value', $l->Аркт_зона)->first()?->id,
                    's_license' => $l->s_лиц,
                    'comment' => $l->Примечание,
                    'geom' => $l->geom
                ]);

                $newLic->src_hash = md5($newLic->series . $newLic->number . $newLic->license_type_id);

                if(!License::where('src_hash', $newLic->src_hash)->exists())
                {
                    $newLic->save();
                    $newCount++;

                    if( ($prevLic == null) && ($l->Старая_лиц != null) )
                    {
                        self::$unfLics[$newLic->id] = $l->Старая_лиц;
                    }
                    
                    foreach(self::splitPi($l->Пол_ископ) as $pi)
                    {
                        if(($newLic->id != null) && ($newLic->id != ""))
                        {
                            $newRel = RelLicensePi::firstOrCreate([
                                'license_id' => $newLic->id,
                                'pi_id' => DicPi::where('value', $pi)->first()?->id
                            ]);

                            if($newRel->wasRecentlyCreated) $RelNewCount++;
                        }
                    }

                }
                else
                {
                    $unsavedCount++;

                    if(self::$showInf)
                    {
                        $ll = License::where('src_hash', $newLic->src_hash)->first();

                        echo ("\t - Не сохранена строка с повторным hash: $newLic->src_hash
                        \r\t\tиз таблицы $licTableName: gid: $l->gid, $l->Название, $l->Серия $l->Номер_лиц $l->Тип
                        \r\t\tсуществующая запись: id: $ll?->id, $ll?->name, $ll?->series $ll?->number\r\n");
                    }
                }
            }
        }
        catch(Exception $e){ dd($e); }

        if(self::$showInf)
        {
            dump("License из таблицы $licTableName(".$licTableModel::count()."): добавлено $newCount, не добавлено $unsavedCount");
        }

        return [$newCount, $unsavedCount, $RelNewCount];
    }
    private static function setPrevLics() : int
    {
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
    private static function getLicTypeId(?string $licType, ?string $tableName = "-") : ?string {
        if($licType) {
           $type = DicLicenseType::where('value', $licType)->first();
           if($type) return $type->id;
           else
           {
                if(self::$showInf) echo("\t- Не найдено license:type, получена строка \"$licType\" из таблицы $tableName\r\n");
                
                return null;
           }
        } 
        return null;
    }
    private static function getPiId(?string $licPi) : ?string {
        if($licPi) {
           $pi = DicPi::where('value', $licPi)->first();
           return $pi ? $pi->id : throw new ErrorException('ПИ задано, но не найдено: ->' . $licPi);
        } 
        return null;
    }
    private static function getPurposeId(?string $licPurp) : ?string {
        if($licPurp) {
           $pp = DicPurpose::where('value', $licPurp)->first();
           return $pp ? $pp->id : throw new ErrorException('        Цель задана, но не найдена: ' . $licPurp);
        } 
        return null;
    }
    private static function getReasId(?string $licReas) : ?string {
        if($licReas) {
           $rr = DicReason::where('value', $licReas)->first();
           return $rr ? $rr->id : throw new ErrorException('        Осн. выдачи задана, но не найдена: ' . $licReas);
        } 
        return null;
    }
    private static function getPrevLicId(?string $prevLicStr, string $licTableName = "-", &$gLic = null) : ?string {
        if($prevLicStr) {
            $spltPrevLic = self::splitPrevLic($prevLicStr);

            if(count($spltPrevLic) == 4) {
                $pl = License::where('series', $spltPrevLic[1])
                                ->where('number', $spltPrevLic[2])
                                ->where('license_type_id', self::getLicTypeId($spltPrevLic[3]))
                                ->first();

                if($pl) return $pl->id;
                else
                {
                    return null;
                }
            }
            else {
                if(self::$showInf && $gLic)
                    echo ("\t - Ошибочная строка для License: prev_license_id: $prevLicStr
                    \r\t\tиз таблицы $licTableName: gid: $gLic->gid, $gLic->Название, $gLic->Серия $gLic->Номер_лиц $gLic->Тип, Старая лиц.: $gLic->Старая_лиц\r\n");
                return null;
            }       
        }
        return null;
    }
    private static function getSsubId(?string $ssub) : ?string {
        if($ssub)
        {
            if(str_contains($ssub, 'Шельф')) $ssub = 'Шельф Российской Федерации';
            if(str_contains($ssub, 'Республика Калмыкия')) $ssub = 'Республика Калмыкия';
            if(str_contains($ssub, 'Красноярский край')) $ssub = 'Красноярский край';
            if(str_contains($ssub, 'Республика Северная-Осетия')) $ssub = 'Республика Северная Осетия';
            if(str_contains($ssub, 'Республика Марий-Эл')) $ssub = 'Республика Марий Эл';

            $ssubId = DicSsubRf::where('region_name', $ssub)
                                ->orWhere('region_name', 'ilike', '%'.$ssub.'%')
                                ->first()?->id;
            
            if($ssubId) return $ssubId;
            else
            {
                dump('        СФ не найден: ' . $ssub);
                return null;
            }
        } 
        return null;
    }
}