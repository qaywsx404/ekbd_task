<?php

namespace App\Http\Controllers\importcontrollers;

use App\Http\Controllers\Controller;
use App\Models\ebd_ekbd\dictionaries\DicNgoType;
use App\Models\ebd_ekbd\Ngp;
use App\Models\ebd_ekbd\Ngo;
use App\Models\ebd_gis\Ngo2019;
use Illuminate\Support\Facades\Log;

class NgoImportController extends Controller
{
    /** Доп. инф. */
    private static $showInf = false;
    /** Счетчики добавленных записей */
    private static $newCount = 0;
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
            dump("Импорт Ngo:");
            Log::channel('importlog')->info("Импорт Ngo:");
            Log::channel('importlog')->info("Очистка Ngo: ".Ngo::count());
        }

        Ngo::truncate();

        self::importFromTable();

        if(self::$showInf)
        {
            $mes = ("Ngo импорт завершен: добавлено " . self::$newCount . ', не добавлено ' . self::$unsCount);
            dump($mes);
            Log::channel('importlog')->info($mes);
        }
    }

    /**  Импорт записей из таблицы  ebd_gis.ngo_2019 */
    private static function importFromTable()
    {
        $newCount = $unsCount = 0;

        foreach(Ngo2019::all() as $n)
        {
            self::$hasErr = false;

            $newNgo = Ngo::make([
                'name' => $n->region,
                'ngo_type_id' => $n->type_ngo ? (DicNgoType::where('value', $n->type_ngo)->first()->id ?? self::getEx('ngo_type_id', $n->type_ngo, $n)) : null,
                'ngp_id' => $n->province ? (Ngp::where('name', $n->province)->first()->id ?? self::getEx('ngp_id', $n->province, $n)) : null,
                'index_all' => $n->index_all,
                'comment' => null,
                'geom' => $n->geom
            ]);

            if(!self::$hasErr)
            {
                $newNgo->save();
                $newCount++;
            }
            else
            {
                $unsCount++;
            }
        }

        self::$newCount = self::$newCount + $newCount;
        self::$unsCount = self::$unsCount + $unsCount;

        return 0;
    }

    private static function getEx($attrName, $attrVal, &$ngoX)
    {
        if($attrVal)
        {
            $attrArr = $ngoX->attributesToArray();
            unset($attrArr['geom']);
            $ser = json_encode($attrArr, JSON_UNESCAPED_UNICODE);

            $mes = ("- Неверная строка или не найдена запись для Ngo: $attrName : \"$attrVal\"\r\nзапись из таблицы Ngo2019: $ser\r\n");
            
            //echo "\v".$mes;
            Log::channel('importerrlog')->error($mes);

            self::$hasErr = true;
        }
        
        return null;
    }
}