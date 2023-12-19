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

use Exception;

class KonkursImportController extends Controller
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
        $newCount = $unsavedCount = $RelNewCount = 0;

        Konkurs::truncate();
        RelKonkursPi::truncate();

        //konkurs19
        $a = self::importFromTable('Konkurs19');
            $newCount += $a[0]; $unsavedCount += $a[1]; $RelNewCount += $a[2]; 
        //konkurs20
        $a = self::importFromTable('Konkurs20');
            $newCount += $a[0]; $unsavedCount += $a[1]; $RelNewCount += $a[2]; 
        //konkurs21
        $a = self::importFromTable('Konkurs21');
            $newCount += $a[0]; $unsavedCount += $a[1]; $RelNewCount += $a[2]; 
        //konkurs22
        $a = self::importFromTable('Konkurs22');
            $newCount += $a[0]; $unsavedCount += $a[1]; $RelNewCount += $a[2]; 
        //konkurs23
        $a = self::importFromTable('Konkurs23');
            $newCount += $a[0]; $unsavedCount += $a[1]; $RelNewCount += $a[2]; 

        dump("Konkurs total: Added " . $newCount . ', unsaved ' . $unsavedCount);
        if(self::$showInf) dump("    Konkurs_pi total: Added " . $RelNewCount . ', total ' . RelKonkursPi::count());
    }

    /**  Импорт записей из таблиц  ebd_gis.konkurs* */
    private static function importFromTable(string $konkursTableName) : array
    {
        $newCount = $unsavedCount = $RelNewCount = 0;

        $konkursTableModel = new (trim('App\Models\ebd_gis\ ') . $konkursTableName )();
        
        foreach($konkursTableModel::all() as $k)
        {
            try
            {
                $newKonkurs = Konkurs::make([
                    'name' => $k->Название_у,
                    'license_type_id' => $k->Тип_лиц ? DicLicenseType::where('value', $k->Тип_лиц)->first()?->id : null,
                    'purpose_id' => $k->Цель ? DicPurpose::where('value', $k->Цель)->first()?->id : null,
                    'ryear' => self::getRYear($k->Год_включ),
                    'comp_form_id' => $k->Ф_состязан ? DicCompForm::where('value', $k->Ф_состязан)->first()?->id : null,
                    'ssub_rf_id' => self::getSsubId($k->Регион),
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

                $newKonkurs->src_hash = md5($k->gid . $konkursTableName);
                
                if(!Konkurs::where('src_hash', $newKonkurs->src_hash)->exists())
                {
                    $newKonkurs->save();
                    $newCount++;
                }
                else
                {
                    $unsavedCount++;
                }

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
    
                            if($newRel->wasRecentlyCreated) $RelNewCount++;
                        }
                    }
                }
            }
            catch(Exception $e)
            {
                dd($e);
            }
        }
        
        if(self::$showInf) dump("  Konkurs frm " . $konkursTableName .' ('.$konkursTableModel::count().') '.   ": Added " . $newCount . ', unsaved ' . $unsavedCount);

        return [$newCount, $unsavedCount, $RelNewCount];
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
                          ->where('ryear', intval($ryear)-1)->first()?->id;
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
    private static function getSsubId(?string $ssub) : ?string {
        if($ssub)
        {
            if(str_contains($ssub, 'Шельф')) $ssub = 'Шельф Российской Федерации';
            if(str_contains($ssub, 'Ямало-Ненецкий АО')) $ssub = 'Ямало-Ненецкий автономный округ';
            if(str_contains($ssub, 'Республика Саха(Якутия)')) $ssub = 'Республика Саха';

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