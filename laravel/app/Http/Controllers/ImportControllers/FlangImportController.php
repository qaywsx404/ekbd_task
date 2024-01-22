<?php

namespace App\Http\Controllers\importcontrollers;

use App\Http\Controllers\Controller;
use App\Models\ebd_ekbd\dictionaries\DicFlangStatus;
use App\Models\ebd_ekbd\dictionaries\DicLicenseType;
use App\Models\ebd_ekbd\dictionaries\DicSsubRf;
use App\Models\ebd_ekbd\Flang;
use App\Models\ebd_ekbd\License;
use App\Models\ebd_gis\Flangi;
use Illuminate\Support\Facades\Log;

class FlangImportController extends Controller
{
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
            dump("Импорт Flang:");
            Log::channel('importlog')->info("Импорт Flang:");
        }

        self::importFromTable();

        if(self::$showInf)
        {
            $mes = ("Flang импорт завершен: добавлено " . self::$newCount . ', обновлено ' . self::$updCount . ', не добавлено ' . self::$unsCount);
            dump($mes);
            Log::channel('importlog')->info($mes);
        }
    }

    /**  Импорт записей из таблицы  ebd_gis.flangi */
    private static function importFromTable()
    {
        $newCount = $updCount = $unsCount = 0;

        foreach(Flangi::all() as $f)
        {
            self::$hasErr = false;

            $src_hash = md5($f->id);
            $licAndDate = self::getLicIdAndRdate($f->Выдана_лиц, $f);

            $ssub_rf_id = DicSsubRf::findByRegionName( DicSsubRf::fixRegionName($f->Регион) )?->id;

            if(!Flang::where('src_hash', $src_hash)->exists())
            {
                $newFlang = Flang::make([
                    'src_hash' => $src_hash,
                    'name' => $f->Название_у,
                    'deposit' => $f->Фланг_мест,
                    'isflang' => $f->Явл_фланго,
                    's_flang' => $f->Площадь ? str_ireplace(',', '.', $f->Площадь) : null,
                    'declarant' => $f->Заявитель,
                    'edate' => $f->Дата_экспе,
                    'resol' => $f->Резолюция_,
                    'ssub_rf_id' => $ssub_rf_id ?? self::getEx('ssub_rf_id', $f->Регион, $f),
                    'license_id' => $licAndDate ? $licAndDate[0] : null,
                    'rdate' => $licAndDate ? $licAndDate[1] : null,
                    'flang_status_id' => $f->Статус_по_ ? (DicFlangStatus::where('value', $f->Статус_по_)->first()?->id ?? self::getEx('flang_status_id', $f->Статус_по_, $f)) : null,
                    'comment' => $f->Примечание,
                    'geom' => $f->geom
                ]);

                if(!self::$hasErr)
                {
                    $newFlang->save();
                    $newCount++;
                }
                else
                {
                    $unsCount++;
                }
            }
            else
            {
                $curFlang = Flang::where('src_hash', $src_hash)->first();

                $curFlang->name = $f->Название_у;
                $curFlang->deposit = $f->Фланг_мест;
                $curFlang->isflang = $f->Явл_фланго;
                $curFlang->s_flang = $f->Площадь ? str_ireplace(',', '.', $f->Площадь) : null;
                $curFlang->declarant = $f->Заявитель;
                $curFlang->edate = $f->Дата_экспе;
                $curFlang->resol = $f->Резолюция_;
                $curFlang->ssub_rf_id = $ssub_rf_id ?? self::getEx('ssub_rf_id', $f->Регион, $f);
                $curFlang->license_id = $licAndDate ? $licAndDate[0] : null;
                $curFlang->rdate = $licAndDate ? $licAndDate[1] : null;
                $curFlang->flang_status_id = $f->Статус_по_ ? (DicFlangStatus::where('value', $f->Статус_по_)->first()?->id ?? self::getEx('flang_status_id', $f->Статус_по_, $f)) : null;
                $curFlang->comment = $f->Примечание;
                $curFlang->geom = $f->geom;

                if(!self::$hasErr)
                {
                    $curFlang->save();
                    $updCount++;
                }
                else
                {
                    $unsCount++;
                }
            }
        }

        self::$newCount = self::$newCount + $newCount;
        self::$updCount = self::$updCount + $updCount;
        self::$unsCount = self::$unsCount + $unsCount;

        return 0;
    }

    private static function getLicIdAndRdate(?string $licStr, Flangi &$f) : ?array
    {
        if(!$licStr) return null;

        $licSplit=[];

        preg_match('/(^[А-Я]{3})\s?(\d*)\s?([А-Я]{2}),?\s\D*?\s?(\d\d.\d\d.\d{4})/u', $licStr, $licSplit);

        if(count($licSplit) == 5)
        {
            $lic = License::where('series', $licSplit[1])
                            ->where('number', $licSplit[2])
                            ->where('license_type_id',
                                DicLicenseType::where('value', $licSplit[3])->first()->id
                            )
                            ->first() ; 
                            //TODO
                            // ?? self::getEx('license_id', $licStr, $f);  Вывод ошибки, если не найдена лицензия
            
            if($lic) return [$lic->id, str_ireplace(' ', '.', $licSplit[4])];
            else return [null, str_ireplace(' ', '.', $licSplit[4])];
        }
        else
        {
            return self::getEx('rdate', $licStr, $f);
        };
    }
    private static function getEx($attrName, $attrVal, $flangX)
    {
        if($attrVal)
        {
            $attrArr = $flangX->attributesToArray();
            unset($attrArr['geom']);
            $ser = json_encode($attrArr, JSON_UNESCAPED_UNICODE);

            $mes = ("- Неверная строка или не найдена запись для Flang: $attrName : \"$attrVal\"\r\nзапись из таблицы Flangi: $ser\r\n");
             - 
            //echo "\v".$mes;
            Log::channel('importerrlog')->error($mes);

            self::$hasErr = true;
        }
        
        return null;
    }
}