<?php

namespace App\Http\Controllers\importcontrollers;

use App\Http\Controllers\Controller;
use App\Models\ebd_ekbd\dictionaries\DicNgpType;
use App\Models\ebd_ekbd\Ngp;
use App\Models\ebd_gis\Ngp2019;
use Illuminate\Support\Facades\Log;

class NgpImportController extends Controller
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
            dump("Импорт Ngp:");
            Log::channel('importlog')->info("Импорт Ngp:");
            Log::channel('importlog')->info("Очистка Ngp: ".Ngp::count());
        }

        Ngp::truncate();

        self::importFromTable();

        if(self::$showInf)
        {
            $mes = ("Ngp импорт завершен: добавлено " . self::$newCount . ', не добавлено ' . self::$unsCount);
            dump($mes);
            Log::channel('importlog')->info($mes);
        }
    }

    /**  Импорт записей из таблицы  ebd_gis.ngp_2019 */
    private static function importFromTable() : int
    {
        $newCount = $unsCount = 0;

        foreach(Ngp2019::all() as $n)
        {
            self::$hasErr = false;

            $newNgp = Ngp::make([
                'name' => $n->province,
                'ngp_type_id' => $n->type_ngp ? (DicNgpType::where('value', $n->type_ngp)->first()->id ?? self::getEx('ngp_type_id', $n->type_ngp, $n)) : null,
                'index_all' => $n->index_all,
                'comment' => null,
                'geom' => $n->geom
            ]);
    
            if(!self::$hasErr)
            {
                $newNgp->save();
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

    private static function getEx($attrName, $attrVal, &$ngpX)
    {
        if($attrVal)
        {
            $attrArr = $ngpX->attributesToArray();
            unset($attrArr['geom']);
            $ser = json_encode($attrArr, JSON_UNESCAPED_UNICODE);

            $mes = ("- Неверная строка или не найдена запись для Ngp: $attrName : \"$attrVal\"\r\nзапись из таблицы Ngp2019: $ser\r\n");
            
            //echo "\v".$mes;
            Log::channel('importerrlog')->error($mes);

            self::$hasErr = true;
        }
        
        return null;
    }
}