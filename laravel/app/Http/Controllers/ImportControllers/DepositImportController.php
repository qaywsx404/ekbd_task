<?php

namespace App\Http\Controllers\importcontrollers;

use App\Http\Controllers\Controller;
use App\Models\ebd_ekbd\Deposit;
use App\Models\ebd_ekbd\dictionaries\DicArcticZone;
use App\Models\ebd_ekbd\dictionaries\DicDepositSize;
use App\Models\ebd_ekbd\dictionaries\DicDepositStage;
use App\Models\ebd_ekbd\dictionaries\DicDepositSubstance;
use App\Models\ebd_ekbd\dictionaries\DicDepositType;
use App\Models\ebd_ekbd\dictionaries\DicSsubRf;
use App\Models\ebd_ekbd\Ngp;
use App\Models\ebd_ekbd\Ngo;
use App\Models\ebd_ekbd\Ngr;
use App\Models\ebd_gis\NgMest;
use Illuminate\Support\Facades\Log;

class DepositImportController extends Controller
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
            dump("Импорт Deposit:");
            Log::channel('importlog')->info("Импорт Deposit:");
            Log::channel('importlog')->info("Очистка Deposit: ".Deposit::count());
        }

        Deposit::truncate();

        self::importFromTable();

        if(self::$showInf)
        {
            $mes = ("Deposit импорт завершен: добавлено " . self::$newCount . ', не добавлено ' . self::$unsCount);
            dump($mes);
            Log::channel('importlog')->info($mes);
        }
    }

    /**  Импорт записей из таблицы  ebd_gis.ng_mest */
    private static function importFromTable()
    {
        $newCount = $unsCount = 0;

        foreach(NgMest::all() as $d)
        {
            self::$hasErr = false;

            $src_hash = md5($d->gid);

            if(!Deposit::where('src_hash', $src_hash)->exists())
            {
                $newDeposit = Deposit::make([
                    'src_hash' => $src_hash,
                    'name' => $d->СПИСОК_МЕС,
                    'deposit_type_id' => $d->Тип ? (DicDepositType::where('value', $d->Тип)->first()?->id ?? self::getEx($d, 'deposit_type_id', $d->Тип)) : null,
                    'deposit_stage_id' => $d->Стадия ? (DicDepositStage::where('value', $d->Стадия)->first()?->id ?? self::getEx($d, 'deposit_stage_id', $d->Стадия)) : null,
                    'dyear' => $d->Год_откр,
                    'oblast_ssub_rf_id' => self::getSsubId($d->Область, 'oblast_ssub_rf_id', $d),
                    'okrug_ssub_rf_id' => self::getSsubId($d->Округ, 'okrug_ssub_rf_id', $d),
                    'ngp_id' => $d->ngp ? (Ngp::where('name', 'ilike', $d->ngp)->first()?->id ?? self::getEx($d, 'ngp_id', $d->ngp)) : null,
                    'ngo_id' => self::getNgoId($d->ngo, $d),
                    'ngr_id' => self::getNgrId($d->ngr, $d),
                    'arctic_zone_id' => $d->Аркт_зона ? (DicArcticZone::where('value', $d->Аркт_зона)->first()?->id ?? self::getEx($d, 'arctic_zone_id', $d->Аркт_зона)) : null,
                    'deposit_n_size_id' => $d->Извл_зап_Н ? (DicDepositSize::where('value', $d->Извл_зап_Н)->first()?->id ?? self::getEx($d, 'deposit_n_size_id', $d->Извл_зап_Н)) : null,
                    'deposit_k_size_id' => $d->Извл_зап_К ? (DicDepositSize::where('value', $d->Извл_зап_К)->first()?->id ?? self::getEx($d,'deposit_k_size_id', $d->Извл_зап_К)) : null,
                    'deposit_g_size_id' => $d->Геол_зап_Г ? (DicDepositSize::where('value', $d->Геол_зап_Г)->first()?->id ?? self::getEx($d, 'deposit_g_size_id', $d->Геол_зап_Г)) : null,
                    'deposit_k_substance_id' => $d->Содержан_К ? (DicDepositSubstance::where('value', $d->Содержан_К)->first()?->id ?? self::getEx($d, 'deposit_k_substance_id', $d->Содержан_К)) : null,
                    'note' => $d->Примечание,
                    'geom' => $d->geom
                ]);

                if(!self::$hasErr)
                {
                    $newDeposit->save();
                    $newCount++;
                }
                else
                {
                    $unsCount++;
                }
            }
            else
            {
                $unsCount++;
            }   
        }

        if(self::$showInf)
        {
            $mes = "Deposit из таблицы NgMest(".NgMest::count()."): добавлено $newCount, не добавлено $unsCount";
            dump($mes);
            Log::channel('importlog')->info($mes);
        }

        self::$newCount = self::$newCount + $newCount;
        self::$unsCount = self::$unsCount + $unsCount;   

        return 0;
    }
    private static function getSsubId(?string $ssub, string $attrName, &$ngMest) : ?string {
        if($ssub)
        {
            // if(str_contains($ssub, 'Шельф')) $ssub = 'Шельф';
            // if(str_contains($ssub, 'Алания')) $ssub = 'Алания';
            // if(str_contains($ssub, 'Крымский')) $ssub = 'Крым';


            $ssubId = DicSsubRf::where('region_name', $ssub)
                                ->orWhere('region_name', 'ilike', '%'.$ssub.'%')
                                ->first()?->id;
            
            return $ssubId ?? self::getEx($attrName, $ssub, $ngMest);
        } 
        return null;
    }
    private static function getNgoId(?string $ngo, &$ngMest) : ?string {
        if($ngo)
        {
            // $ngo = str_ireplace('  ', ' ', $ngo);
            // if($ngo == 'АРЛАНСКАЯ НГО') $ngo = 'АРЛАНСКАЯ  НГО'; //TODO
            // if($ngo == 'ЗАПАДНО-ПРЕДКАВКАЗСКАЯ ГНО') $ngo = 'ЗАПАДНО-ПРЕДКАВКАЗСКАЯ НГО';
            // if($ngo == 'ЗАПАДНО-ПРЕДКАВКАЗСКАЯ ГНО') $ngo = 'ЗАПАДНО-УРАЛЬСКАЯ ПГО';

            $ngoId = Ngo::where('name', 'ilike', $ngo)->first()?->id;

           return $ngoId ?? self::getEx('ngo_id', $ngo, $ngMest);
        } 
        return null;
    }
    private static function getNgrId(?string $ngr, &$ngMest) : ?string {
        if($ngr)
        {
            // $ngr = str_ireplace('  ', ' ', $ngr);
            // $ngr = str_ireplace('ё', 'е', $ngr);
            // if($ngr == 'Северного борта и северо-западной центриклинали НГР') $ngr = 'СЕВЕРНОГО БОРТА И СЕВ.-ЗАПАДНОЙ ЦЕНТРИКЛИНАЛИ НГР';
            // if($ngr == 'Терско-Сунженский НГР') $ngr = 'ТЕРСКО-СУНЖЕНСКЙ НГР';
            // if($ngr == 'Черемшанско-Байтуганский НГР') $ngr = 'ЧЕРЕМШАНО-БАЙТУГАНСКИЙ НГР';

            $ngrId = Ngr::where('name', 'ilike', $ngr)->first()?->id;

            return $ngrId ?? self::getEx('ngr_id', $ngr, $ngMest);
        } 

        return null;
    }
    private static function getEx($attrName, $attrVal, &$NgMest)
    {
        if($attrVal)
        {
            $attrArr = $NgMest->attributesToArray();
            unset($attrArr['geom']);
            $ser = json_encode($attrArr, JSON_UNESCAPED_UNICODE);

            $mes = ("- Неверная строка или не найдена запись для Deposit: $attrName : \"$attrVal\"\r\nзапись из таблицы NgMest: $ser\r\n");
            
            //echo "\v".$mes;
            Log::channel('importerrlog')->error($mes);

            self::$hasErr = true;
        }
        
        return null;
    }
}