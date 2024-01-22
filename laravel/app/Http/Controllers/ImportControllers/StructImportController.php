<?php

namespace App\Http\Controllers\importcontrollers;

use App\Http\Controllers\Controller;
use App\Models\ebd_ekbd\dictionaries\DicDepositStage;
use App\Models\ebd_ekbd\dictionaries\DicDepositType;
use App\Models\ebd_ekbd\Ngp;
use App\Models\ebd_ekbd\Ngo;
use App\Models\ebd_ekbd\Ngr;
use App\Models\ebd_ekbd\Struct;
use App\Models\ebd_ekbd\dictionaries\DicArcticZone;
use App\Models\ebd_ekbd\dictionaries\DicSsubRf;
use App\Models\ebd_gis\NgStruct;
use Illuminate\Support\Facades\Log;

class StructImportController extends Controller
{
    /** Доп. инф. */
    private static $showInf = false;
    /** Счетчики добавленных записей */
    private static $newCount = 0;
    /** Счетчики добавленных записей */
    private static $updCount = 0;
    /** Счетчики не добавленных записей */
    private static $unsCount = 0;
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
            dump("Импорт Struct:");
            Log::channel('importlog')->info("Импорт Struct:");
            $mes = "Очистка Struct: ".Struct::count();
            dump($mes);
            Log::channel('importlog')->info($mes);
        }

        Struct::truncate();

        self::importFromTable();

        if(self::$showInf)
        {
            $mes = ("Struct импорт завершен: добавлено " . self::$newCount . ', не добавлено ' . self::$unsCount);
            dump($mes);
            Log::channel('importlog')->info($mes);
        }
    }

    /**  Импорт записей из таблиц  ebd_gis.ng_struct */
    private static function importFromTable()
    {
        $newCount = $unsCount = 0;

        foreach(NgStruct::all() as $s)
        {
            self::$hasErr = false;

            $src_hash =md5($s->gid);

            $oblast_ssub_rf_id = DicSsubRf::findByRegionName( DicSsubRf::fixRegionName($s->Область) )?->id;
            $okrug_ssub_rf_id = DicSsubRf::findByRegionName( DicSsubRf::fixRegionName($s->Округ) )?->id;

            if(!Struct::where('src_hash', $src_hash)->exists())
            {
                $newStruct = Struct::make([
                    'src_hash' => $src_hash,
                    'name' => $s->СПИСОК_СТР,
                    'deposit_type_id' => $s->Тип ? (DicDepositType::where('value', $s->Тип)->first()?->id ?? self::getEx('deposit_type_id', $s->Тип, $s)) : null,
                    'deposit_stage_id' => $s->Стадия ? (DicDepositStage::where('value', $s->Стадия)->first()?->id ?? self::getEx('deposit_stage_id',  $s->Стадия, $s)) : null,
                    'ng_struct' => $s->Отложения,
                    'oblast_ssub_rf_id' => $oblast_ssub_rf_id ?? self::getEx('ssub_rf_id', $s->Область, $s),
                    'okrug_ssub_rf_id' => $okrug_ssub_rf_id ?? self::getEx('ssub_rf_id', $s->Округ, $s),
                    'ngp_id' => $s->ngp ? (Ngp::where('name', $s->ngp)->first()?->id ?? self::getEx('ngp_id', $s->ngp, $s)) : null,
                    'ngo_id' => $s->ngo ? (Ngo::where('name', 'ilike', $s->ngo)->first()?->id ?? self::getEx('ngo_id', $s->ngo, $s)) : null, //TODO - ilike
                    'ngr_id' => self::getNgrId($s->ngr, $s),
                    'arctic_zone_id' => $s->Аркт_зона ? (DicArcticZone::where('value',$s->Аркт_зона, $s)->first()?->id ?? self::getEx('arctic_zone_id', $s->Аркт_зона, $s)) : null,
                    'syear' => $s->Год_ввода,
                    'lastyear' => $s->Год_списан,
                    'nf' => $s->НФ,
                    'gr_n' => $s->Геол_рес_Н,
                    'gr_g' => $s->Геол_рес_Г,
                    'gr_k' => $s->Геол_рес_К,
                    'ir_n' => $s->Извл_рес_Н,
                    'ir_k' => $s->Извл_рес_К,
                    'rdl_n' => $s->dл_рес_Н,
                    'rdl_g' => $s->dл_рес_Г,
                    'rdl_k' => $s->dл_рес_К,
                    'comment' => $s->Примечание,
                    'geom' => $s->geom
                ]);

                if(!self::$hasErr)
                {
                    $newStruct->save();
                    $newCount++;
                }
                else
                {
                    $unsCount++;
                }
            }
        }

        self::$newCount = self::$newCount + $newCount;
        self::$unsCount = self::$unsCount + $unsCount;

        return 0;
    }
    private static function getNgrId(?string $ngr, &$ngStruct) : ?string {
        if($ngr)
        {
            // $ngr = str_ireplace('  ', ' ', $ngr);
            // $ngr = str_ireplace('ё', 'е', $ngr);
            // if($ngr == 'Черемшанско-Байтуганский НГР') $ngr = 'ЧЕРЕМШАНО-БАЙТУГАНСКИЙ НГР';

            $ngrId = Ngr::where('name', 'ilike', $ngr)->first()?->id;
            //$ngrId = Ngr::where('name', $ngr)->first()?->id; ??

            return $ngrId ?? self::getEx('ngr_id', $ngr, $ngStruct);
        } 

        return null;
    }
    private static function getEx($attrName, $attrVal, &$ngStruct)
    {
        if($attrVal)
        {
            $attrArr = $ngStruct->attributesToArray();
            unset($attrArr['geom']);
            $ser = json_encode($attrArr, JSON_UNESCAPED_UNICODE);

            $mes = ("- Неверная строка или не найдена запись для Struct: $attrName : \"$attrVal\"\r\nзапись из таблицы NgStruct: $ser\r\n");
            
            //echo "\v".$mes;
            Log::channel('importerrlog')->error($mes);

            self::$hasErr = true;
        }
        
        return null;
    }
}