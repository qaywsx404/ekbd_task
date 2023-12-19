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
    /** Связанные связи flang */
    private static $flangLics = [];
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
            dump("  Added prevLics : " . $plc . " anset PL: " . count(self::$unfLics));
            dump("  RelLicensePi added : " . $RelNewCount . ' total: ' . RelLicensePi::count());
        }
        dump("License total: Added " . $newCount . ', unsaved ' . $unsavedCount);
        
        //DB::commit();
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
                
                $prevLic = self::getPrevLicId($l->Старая_лиц, md5($l->Серия . $l->Номер_лиц . self::getLicTypeId($l->Тип)), $licTableName, $l->gid);

                $newLic = License::make([
                    'name' => $l->Название,
                    'series' => $l->Серия,
                    'number' => $l->Номер_лиц,
                    'license_type_id' => self::getLicTypeId($l->Тип),
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
                    'prev_license_id' => $prevLic ? ($prevLic[0] ? $prevLic[1] : null) : null,
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
                }
                else
                {
                    $unsavedCount++;
                    //if(self::$showInf) dump('   -> Повторная запись не сохранена: ' . $licTableName . ' : gid:' . $l->gid . ' : ' . $newLic->series . $newLic->number . $l->Тип);
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

                if($prevLic != null && !$prevLic[0])
                {
                    self::$unfLics = [License::where('src_hash')->first()?->id => $prevLic[1]];
                }
            }
        }
        catch(Exception $e){ dd($e); }

        if(self::$showInf) dump("  License frm " . $licTableName .' ('.$licTableModel::count().') '.  ": Added " . $newCount . ', unsaved ' . $unsavedCount);
        
        return [$newCount, $unsavedCount, $RelNewCount];
    }
    private static function setPrevLics() : int
    {
        $counter = 0;

        foreach(self::$unfLics as $id => $pl)
        {

            if($id != "") {   
                $l = License::find($id);
                
                if(License::find($id)->exists())
                {
                    $spltPL = self::splitPrevLic($pl);

                    $pl = License::where('series', $spltPL[1])
                                    ->where('number', $spltPL[2])
                                    ->where('license_type_id', self::getLicTypeId($spltPL[3]))
                                    ->first();

                    if($pl)
                    {
                        $l->prev_license_id = $pl?->id;
                        $l->save();
                        $counter++;
                        unset(self::$unfLics[$id]);
                    }
                    else
                    {
                        $l->prev_license_id = null;
                        $l->save();
                        //unset(self::$unfLics[$id]);
                    }
                }
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
    private static function getLicTypeId(?string $licType) : ?string {
        if($licType) {
           $type = DicLicenseType::where('value', $licType)->first();
           if($type) return $type->id;
           else {
                if(self::$showInf) dump('       Тип лицензии задан, но не найден: ' . $licType);
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
    private static function getPrevLicId(?string $licPrevLic, $licHash = null, $ltn = null, $l = null) : ?array {
        if($licPrevLic) {
            $spltPrevLic = self::splitPrevLic($licPrevLic);

            if(count($spltPrevLic) == 4) {
                $pl = License::where('series', $spltPrevLic[1])
                                ->where('number', $spltPrevLic[2])
                                ->where('license_type_id', self::getLicTypeId($spltPrevLic[3]))
                                ->first();

                if($pl) return [true, $pl->id];
                else {
                    //dump('Прев. лиц задана, но не найдена: ' . $licPrevLic . ' -> ' . $spltPrevLic[1] .'-'. $spltPrevLic[2].'-'. $spltPrevLic[3]);
                    self::$unfLics[] = $licPrevLic;
                    return [false, $licPrevLic];
                }
            }
            else {
                if(self::$showInf) dump('       Прев. лиц для лиц. с hash: ' . $licHash . ' задана неверно -> set null. Полученная строка: ' . $licPrevLic . ' из ' . $ltn . ' gid: '. $l);
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