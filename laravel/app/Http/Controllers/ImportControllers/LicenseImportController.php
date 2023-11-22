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
use ErrorException;
use Exception;

class LicenseImportController extends Controller
{
    /** licWithUnfoundndPrevLic */
    private static $unfLics = [];
    
    public static function import()
    {
        
        $newCount = $unsavedCount = 0;

        //lic_exp_ln
        $a = self::importFromLicTable('LicExpLn');
            $newCount += $a[0]; $unsavedCount += $a[1];
        //lic_exp_pln
        $a = self::importFromLicTable('LicExpPln');
            $newCount += $a[0]; $unsavedCount += $a[1];
        // //lic_exp_pt
        $a = self::importFromLicTable('LicExpPt');
            $newCount += $a[0]; $unsavedCount += $a[1];
        // //lic_pln
        $a = self::importFromLicTable('LicPln');
            $newCount += $a[0]; $unsavedCount += $a[1];
        // //lic_pt
        $a = self::importFromLicTable('LicPt');
            $newCount += $a[0]; $unsavedCount += $a[1];

        dump("License total: Added " . $newCount . ', unsaved ' . $unsavedCount);
    }

    private static function importFromLicTable(string $licTableName) : array
    {
        $newCount = $unsavedCount = 0;
        
        $licTableModel = new (trim('App\Models\ebd_gis\ ') . $licTableName )();

        try
        {
        foreach($licTableModel::all() as $l) {
            $pis = $l->Пол_ископ == null ? [null] : self::splitPi($l->Пол_ископ);

            foreach($pis as $pi) {
                $newLic = License::create([
                    //TODO ??
                    //'vid' => ? 
                    'name' => $l->Название,
                    'series' => $l->Серия,
                    'number' => $l->Номер_лиц,
                    'license_type_id' => self::getLicTypeId($l->Тип),
                    //'status' => '',
                    'pi_id' => self::getPiId($pi),
                    'purpose_id' =>  self::getPurposeId($l->Цель),
                    'reason_id' =>  self::getReasId($l->Осн_выдачи),
                    'rdate' => $l->Дата_регис,
                    'validity' => $l->Срок_дейст,
                    'suser' => $l->Недропольз,
                    'suser_inn' => $l->ИНН,
                    'suser_adr' => $l->Адрес,
                    'founder' => $l->Учредители,
                    'pcomp' => $l->Гол_предпр,
                    //TODO Повторно пройти и проставить появившиеся лицензии
                    'prev_license_id' => self::getPrevLicId($l->Старая_лиц),
                    //TODO ??
                    'ssub_rf_code' => DicSsubRf::where('value', $l->Назв_СФ)->first()->id,
                    //TODO ??
                    'ssub_rf_id' => DicSsubRf::where('value', $l->Назв_СФ)->first()->id,
                    'arctic_zone_id' => DicArcticZone::where('value', $l->Аркт_зона)->first()->id,
                    's_license' => $l->s_лиц,
                    'comment' => $l->Примечание,
                    'geom' => $l->geom
                ]);
           
                $newLic->refresh();

                // $m = md5($newLic->name . $newLic->series . $newLic->number 
                //                         . $newLic->license_type_id . $newLic->pi_id . $newLic->purpose_id  
                //                         . $newLic->reason_id . date('d-m-Y', strtotime($newLic->rdate) ) . date('d-m-Y', strtotime($newLic->validity) )
                //                         . $newLic->suser ?? '' . $newLic->suser_inn ?? '' . $newLic->suser_adr ?? '' .
                //                         $newLic->founder ?? '' . $newLic->pcomp ?? '' . $newLic->reason_id ?? '' . $newLic->prev_license_id ?? '' .
                //                         $newLic->ssub_rf_code ?? '' . $newLic->ssub_rf_id ?? '' . $newLic->arctic_zone_id ?? '' .
                //                         $newLic->s_license ?? '' . $newLic->comment ?? '' . $newLic->geom ?? ''
                //                     );

                if( count(License::where('src_hash', $newLic->src_hash)->get()) > 1) {
                    $newLic->delete();
                    $unsavedCount++;
                } else {
                    $newCount++;
                }
            }
        }
        }
        catch(Exception $e){dd($newLic);}

        dump("  License frm " . $licTableName . ": Added " . $newCount . ', unsaved ' . $unsavedCount);

        return [$newCount, $unsavedCount];
    }

    private static function splitPi($str) : array {
        $pis = str_ireplace([', ', ',   ', ',  ', ', '], ',', $str);
        return explode(',', $pis);
    }
    private static function getLicTypeId(?string $licType) : ?string {
        if($licType) {
           $type = DicLicenseType::where('value', $licType)->first();
           if($type) return $type->id;
           else {
                dump('Тип лицензии задан, но не найден: ' . $licType);
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
           return $pp ? $pp->id : throw new ErrorException('Цель задана, но не найдена: ' . $licPurp);
        } 
        return null;
    }
    private static function getReasId(?string $licReas) : ?string {
        if($licReas) {
           $rr = DicReason::where('value', $licReas)->first();
           return $rr ? $rr->id : throw new ErrorException('Осн. выдачи задана, но не найдена: ' . $licReas);
        } 
        return null;
    }
    private static function getPrevLicId(?string $licPrevLic) : ?string {
        if($licPrevLic) {
            $spltPrevLic=[];
            preg_match('/(^[А-Я]*) ?(\d*) ? ?([А-Я]+$)/u', $licPrevLic, $spltPrevLic, 0);

            if(count($spltPrevLic) == 4) {
                $pl = License::where('series', $spltPrevLic[1])
                                ->where('number', $spltPrevLic[2])
                                ->where('license_type_id', self::getLicTypeId($spltPrevLic[3]))
                                ->first();

                if($pl) return $pl->id;
                else {
                    //dump('Прев. лиц задана, но не найдена: ' . $licPrevLic . ' -> ' . $spltPrevLic[1] .'-'. $spltPrevLic[2].'-'. $spltPrevLic[3]);
                    //self::$unfLics[] = $licPrevLic;
                    return null;
                }
            }
            else {
                dump('Прев. лиц задана неверно -> вернется нулл.  Полученная строка: ' . $licPrevLic);
                return null;
            }       
        }
        return null;
    }
}