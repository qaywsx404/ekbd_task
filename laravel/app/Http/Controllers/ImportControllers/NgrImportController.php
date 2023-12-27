<?php

namespace App\Http\Controllers\importcontrollers;

use App\Http\Controllers\Controller;
use App\Models\ebd_ekbd\dictionaries\DicNgrType;
use App\Models\ebd_ekbd\Ngr;
use App\Models\ebd_ekbd\Ngp;
use App\Models\ebd_ekbd\Ngo;
use App\Models\ebd_gis\Ngr2019;
use Illuminate\Support\Facades\Log;

class NgrImportController extends Controller
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
            dump("Импорт Ngr:");
            Log::channel('importlog')->info("Импорт Ngr:");
            Log::channel('importlog')->info("Очистка Ngr: ".Ngr::count());
        }

        Ngr::truncate();

        self::importFromTable();

        if(self::$showInf)
        {
            $mes = ("Ngr импорт завершен: добавлено " . self::$newCount . ', не добавлено ' . self::$unsCount);
            dump($mes);
            Log::channel('importlog')->info($mes);
        }
    }

    /**  Импорт записей из таблицы  ebd_gis.ngr_2019 */
    private static function importFromTable() : int
    {
        $newCount = $unsCount = 0;

        foreach(Ngr2019::all() as $n)
        {
            $newNgr = Ngr::make([
                'name' => $n->district,
                'ngr_type_id' => $n->type_ngr ? (DicNgrType::where('value', $n->type_ngr)->first()->id ?? self::getEx('ngr_type_id', $n->type_ngr, $n)) : null,
                'ngp_id' => $n->province ? (Ngp::where('name', $n->province)->first()->id ?? self::getEx('ngp_id', $n->province, $n)): null,
                'ngo_id' => $n->region ? (Ngo::where('name', $n->region)->first()->id ?? self::getEx('ngo_id', $n->region, $n)) : null,
                'index_all' => $n->index_all,
                'comment' => null,
                'geom' => $n->geom
            ]);
    
            if(!self::$hasErr)
            {
                $newNgr->save();
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

    private static function getEx($attrName, $attrVal, &$ngrX)
    {
        if($attrVal)
        {
            $attrArr = $ngrX->attributesToArray();
            unset($attrArr['geom']);
            $ser = json_encode($attrArr, JSON_UNESCAPED_UNICODE);

            $mes = ("- Неверная строка или не найдена запись для Ngr: $attrName : \"$attrVal\"\r\nзапись из таблицы Ngr2019: $ser\r\n");
            
            //echo "\v".$mes;
            Log::channel('importerrlog')->error($mes);

            self::$hasErr = true;
        }
        
        return null;
    }
}