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
use App\Models\ebd_ekbd\konkurs_res\KonkursReservesN;
use App\Models\ebd_ekbd\konkurs_res\KonkursReservesG;
use App\Models\ebd_ekbd\konkurs_res\KonkursReservesK;
use App\Models\ebd_ekbd\konkurs_res\KonkursResourceN;
use App\Models\ebd_ekbd\konkurs_res\KonkursResourceG;
use App\Models\ebd_ekbd\konkurs_res\KonkursResourceK;
use App\Models\ebd_ekbd\rel\RelKonkursPi;
use Ds\Set;
use Exception;

class KonkursImportController extends Controller
{
    /** Доп. инф. */
    private static $showInf = false;
    //private static $dts;

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

        // self::$dts = new Set();

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

        // dump(self::$dts->toArray());
        dump("Konkurs total: Added " . $newCount . ', unsaved ' . $unsavedCount);
        //dump("  RelKonkursPi added: " . $RelNewCount . ' total: ' . RelKonkursPi::count());
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
                    'ryear' => $k->Год_включ,
                    'comp_form_id' => $k->Ф_состязан ? DicCompForm::where('value', $k->Ф_состязан)->first()?->id : null,
                    'ssub_rf_id' => $k->Регион ? DicSsubRf::where('value', $k->Регион)->first()?->id : null,
                    's_konkurs' => $k->Площадь ? str_ireplace(['/', ',', ' '], '.', $k->Площадь) : null,
                    'prev_konkurs_id' => self::getPrevId($k->Переход),
                    'prev_txt' => $k->Переход,
                    'arctic_zone_id' => $k->Арктическа ? DicArcticZone::where('value', $k->Арктическа)->first()?->id : null,
                    'reserves_n' => $k->Запасы_н_м,
                    'reserves_g' => $k->Запасы_газ,
                    'reserves_k' => $k->Запасы_к,
                    'resource_n' => $k->Ресурсы_н,
                    'resource_g' => $k->Ресурсы_г,
                    'resource_k' => $k->Ресурсы_к,
                    'comment' => $k->Примечание
                    //TODO
                    //,'geom' => $k->geom
                ]);
        
                
                $newKonkurs->src_hash = md5($k->number . $konkursTableName
                        // . $newKonkurs->name . $newKonkurs->license_type_id . $newKonkurs->purpose_id
                        // . $newKonkurs->ryear . $newKonkurs->comp_form_id . $newKonkurs->ssub_rf_id
                        // . ($newKonkurs->s_konkurs ? sprintf("%.3f", $newKonkurs->s_konkurs) : null) . $newKonkurs->prev_txt . $newKonkurs->arctic_zone_id
                        // . $newKonkurs->arctic_zone_id . $newKonkurs->comment . $newKonkurs->geom
                    );
                
                //dump($newKonkurs->src_hash . ' <-----> ' . $md5 . ' <-----> ' . ($newKonkurs->src_hash == $md5 ? 'T' : 'F'));

                if(!Konkurs::where('src_hash', $newKonkurs->src_hash)->exists())
                {
                    $newKonkurs->save();
                    $newCount++;
                }
                else
                {
                    $unsavedCount++;
                }

                // foreach(self::splitPi($k->Полезн_иск) as $pi)
                // {
                //     if($newKonkurs->id && $pi)
                //     {
                //         $newRel = RelKonkursPi::firstOrCreate([
                //             'konkurs_id' => $newKonkurs->id,
                //             'pi_id' => DicPi::where('value', $pi)->first()?->id
                //         ]);

                //         if($newRel->wasRecentlyCreated) $RelNewCount++;
                //     }
                // }

                //$arr = self::splitRes($k->Ресурсы_к);
                //$arr = self::splitRes($k->Запасы_газ);
                // if($newKonkurs != '')
                // {
                //     if(($res = self::splitRes($k->Запасы_н_м)) > 0)
                //         foreach($res as $r => $v)
                //         {
                //             // self::$dts->add($r);
                //             KonkursReservesN::firstOrCreate([
                //                 'konkurs_id' => $newKonkurs?->id,
                //                 'res' => ($r . ' = ' . $v), 
                //             ]);
                //         }
                //     if(($res = self::splitRes($k->Запасы_газ)) > 0)
                //         foreach($res as $r => $v)
                //         {
                //             KonkursReservesG::firstOrCreate([
                //                 'konkurs_id' => $newKonkurs?->id,
                //                 'res' => ($r . ' = ' . $v), 
                //             ]);
                //         }
                //     if(($res = self::splitRes($k->Запасы_к)) > 0)
                //         foreach($res as $r => $v)
                //         {
                //             KonkursReservesK::firstOrCreate([
                //                 'konkurs_id' => $newKonkurs?->id,
                //                 'res' => ($r . ' = ' . $v), 
                //             ]);
                //         }
                //     if(($res = self::splitRes($k->Ресурсы_н)) > 0)
                //         foreach($res as $r => $v)
                //         {
                //             KonkursResourceN::firstOrCreate([
                //                 'konkurs_id' => $newKonkurs?->id,
                //                 'res' => ($r . ' = ' . $v), 
                //             ]);
                //         }
                //     if(($res = self::splitRes($k->Ресурсы_г)) > 0)
                //         foreach($res as $r => $v)
                //         {
                //             KonkursResourceG::firstOrCreate([
                //                 'konkurs_id' => $newKonkurs?->id,
                //                 'res' => ($r . ' = ' . $v), 
                //             ]);
                //         }
                //     if(($res = self::splitRes($k->Ресурсы_к)) > 0)
                //         foreach($res as $r => $v)
                //         {
                //             KonkursResourceK::firstOrCreate([
                //                 'konkurs_id' => $newKonkurs?->id,
                //                 'res' => ($r . ' = ' . $v), 
                //             ]);
                //         }
                // }

                
            }
            catch(Exception $e)
            {
                dd($e->getMessage());
                //dd($k);
                //continue;
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
    private static function getPrevId($str) : ?string {
        //TODO
        return null;
    }


}