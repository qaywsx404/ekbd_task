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
use App\Models\ebd_gis\LicExpLn;
use App\Models\ebd_gis\LicExpPln;
use App\Models\ebd_gis\LicExpPt;
use App\Models\ebd_gis\LicPln;
use App\Models\ebd_gis\LicPt;
use Exception;

class LicenseImportController extends Controller
{
    
    public static function import() {
        $newCount = $unsavedCount = 0;

        //lic_exp_ln
        $c = self::importFromLicExpLn();
            $newCount += $c[0]; $unsavedCount += $c[1];
        //lic_exp_pln
        $c = self::importFromLicExpPln();
            $newCount += $c[0]; $unsavedCount += $c[1];
        //lic_exp_pt
        $c = self::importFromLicExpPt();
            $newCount += $c[0]; $unsavedCount += $c[1];
        //lic_pln
        $c = self::importFromLicPln();
            $newCount += $c[0]; $unsavedCount += $c[1];
        //lic_pt
        $c = self::importFromLicPt();
            $newCount += $c[0]; $unsavedCount += $c[1];

        dump("License total: Added " . $newCount . ', unsaved ' . $unsavedCount);
    }

    private static function importFromLicExpLn() : array {
        $newCount = $unsavedCount = 0;

        foreach(LicExpLn::all() as $l) {         
            $newLic = License::create([
                //TODO
                //'vid'
                'name' => $l->Название,
                'series' => $l->Серия,
                'number' => $l->Номер_лиц,
                'license_type_id' => DicLicenseType::where('value', $l->Тип)->first()->id,
                //'status' => ,
                'pi_id' => DicPi::where('value', $l->Пол_ископ)->first()->id,
                'purpose_id' => DicPurpose::where('value', $l->Цель)->first()->id,
                'reason_id' => DicReason::where('value', $l->Осн_выдачи)->first()->id,
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

            if( count(License::where('src_hash', $newLic->src_hash)->get()) > 1) {
                $newLic->delete();
                $unsavedCount++;
            } else {
                $newCount++;
            }
        }

        dump("  License frm LicExpLn: Added " . $newCount . ', unsaved ' . $unsavedCount);

        return [$newCount, $unsavedCount];
    }

    private static function importFromLicExpPln() : array {
        $newCount = $unsavedCount = 0;
        
        foreach(LicExpPln::all() as $l) {         
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

            if( count(License::where('src_hash', $newLic->src_hash)->get()) > 1) {
                $newLic->delete();
                $unsavedCount++;
            } else {
                $newCount++;
            }
        }

        dump("  License frm LicExpPln: Added " . $newCount . ', unsaved ' . $unsavedCount);

        return [$newCount, $unsavedCount];
    }

    private static function importFromLicExpPt() : array {
        $newCount = $unsavedCount = 0;
        
        foreach(LicExpPt::all() as $l) {         
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

            if( count(License::where('src_hash', $newLic->src_hash)->get()) > 1) {
                $newLic->delete();
                $unsavedCount++;
            } else {
                $newCount++;
            }
        }

        dump("  License frm LicExpPt: Added " . $newCount . ', unsaved ' . $unsavedCount);

        return [$newCount, $unsavedCount];
    }

    private static function importFromLicPln() : array {
        $newCount = $unsavedCount = 0;
        
        foreach(LicPln::all() as $l) {         
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

            if( count(License::where('src_hash', $newLic->src_hash)->get()) > 1) {
                $newLic->delete();
                $unsavedCount++;
            } else {
                $newCount++;
            }
        }

        dump("  License frm LicPln: Added " . $newCount . ', unsaved ' . $unsavedCount);

        return [$newCount, $unsavedCount];
    }

    private static function importFromLicPt() : array {
        $newCount = $unsavedCount = 0;
        
        foreach(LicPt::all() as $l) {         
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

            if( count(License::where('src_hash', $newLic->src_hash)->get()) > 1) {
                $newLic->delete();
                $unsavedCount++;
            } else {
                $newCount++;
            }
        }

        dump("  License frm LicPt: Added " . $newCount . ', unsaved ' . $unsavedCount);

        return [$newCount, $unsavedCount];
    }


    private static function splitPi($str) : array {
        $pis = str_ireplace(', ', ',', $str);
        return explode(',', $pis);
    }
}