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

class LicenseImportController extends Controller
{
    
    public static function import() {
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

    private static function importFromLicTable(string $licTableName) : array {
        $newCount = $unsavedCount = 0;
        
        $licTableModel = new (trim('App\Models\ebd_gis\ ') . $licTableName )();

        foreach($licTableModel::all() as $l) {         
            $newLic = License::create([
                'name' => $l->Название,
                'series' => $l->Серия,
                'number' => $l->Номер_лиц,
                'license_type_id' => DicLicenseType::where('value', $l->Тип)->first()->id,
                // //'status' => ,
                'pi_id' => $l->Пол_ископ == null ? null : DicPi::where('value', self::splitPi($l->Пол_ископ)[0])->first()->id, //TODO несколько pi
                'purpose_id' => DicPurpose::where('value', $l->Цель)->first()->id,
                'reason_id' => $l->Осн_выдачи == null ? null : DicReason::where('value', $l->Осн_выдачи)->first()->id,
                'rdate' => $l->Дата_регис,
                'validity' => $l->Срок_дейст,
                'suser' => $l->Недропольз,
                'suser_inn' => $l->ИНН,
                'suser_adr' => $l->Адрес,
                'founder' => $l->Учредители,
                'pcomp' => $l->Гол_предпр,
                //TODO
                //'prev_license_id' => License::where('value', $l->Старая_лиц)->first()->id,
                'ssub_rf_code' => DicSsubRf::where('value', $l->Назв_СФ)->first()->id,
                //TODO
                //'ssub_rf_code' => DicSsubRf::where('value', $l->Назв_СФ)->first()->id,
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

        dump("  License frm " . $licTableName . ": Added " . $newCount . ', unsaved ' . $unsavedCount);

        return [$newCount, $unsavedCount];
    }

    private static function splitPi($str) : array {
        $pis = str_ireplace(', ', ',', $str);
        return explode(',', $pis);
    }
}