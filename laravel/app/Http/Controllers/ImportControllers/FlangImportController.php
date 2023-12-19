<?php

namespace App\Http\Controllers\importcontrollers;

use App\Http\Controllers\Controller;
use App\Models\ebd_ekbd\dictionaries\DicFlangStatus;
use App\Models\ebd_ekbd\dictionaries\DicLicenseType;
use App\Models\ebd_ekbd\dictionaries\DicSsubRf;
use App\Models\ebd_ekbd\Flang;
use App\Models\ebd_ekbd\License;
use App\Models\ebd_gis\Flangi;
use Exception;

class FlangImportController extends Controller
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
        $newCount = $unsavedCount = 0;

        [$newCount, $unsavedCount] = self::importFromTable();

        dump("Flang total: Added " . $newCount . ', unsaved ' . $unsavedCount);
    }

    /**  Импорт записей из таблицы  ebd_gis.flangi */
    private static function importFromTable() : array
    {
        $newCount = $unsavedCount = 0;

            foreach(Flangi::all() as $f)
            {
                $licAndDate = self::getLicIdAndRdate($f->Выдана_лиц);

                try
                {
                    $newFlang = Flang::make([
                        'name' => $f->Название_у,
                        'deposit' => $f->Фланг_мест,
                        'isflang' => $f->Явл_фланго,
                        's_flang' => $f->Площадь ? str_ireplace(',', '.', $f->Площадь) : null,
                        'declarant' => $f->Заявитель,
                        'edate' => $f->Дата_экспе,
                        'resol' => $f->Резолюция_,
                        'ssub_rf_id' => self::getSsubId($f->Регион),
                        'license_id' => $licAndDate ? $licAndDate[0] : null,
                        'rdate' => $licAndDate ? $licAndDate[1] : null,
                        'flang_status_id' => $f->Статус_по_ ? DicFlangStatus::where('value', $f->Статус_по_)->first()->id : null,
                        'comment' => $f->Примечание,
                        'geom' => $f->geom
                    ]);

                    $newFlang->src_hash = md5($f->id);

                    if(!Flang::where('src_hash', $newFlang->src_hash)->exists())
                    {
                        $newFlang->save();
                        $newCount++;
                    }
                    else
                    {
                        $unsavedCount++;
                    }
                }
                catch(Exception $e)
                {
                    dump($e->getMessage());
                    dd($f);
                    continue;
                }
            }

        if(self::$showInf) dump("   Flang frm flangi" .' ('.Flangi::count().') '.  ": Added " . $newCount . ', unsaved ' . $unsavedCount);

        return [$newCount, $unsavedCount];
    }

    private static function getLicIdAndRdate(?string $licStr) : ?array
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
                            ->first();
            
            if($lic) return [$lic->id, str_ireplace(' ', '.', $licSplit[4])];
            else return [null, str_ireplace(' ', '.', $licSplit[4])];
        }
        else
        {
            if(self::$showInf) dump('       Flangi: Ошибочная строка: ' . $licStr . ' лиц. и дата -> null');
            return null;
        };
    }
    private static function getSsubId(?string $ssub) : ?string {
        if($ssub)
        {
            if(str_contains($ssub, 'ХМАО')) $ssub = 'Ханты-Мансийский автономный округ';
            if(str_contains($ssub, 'ЯНАО')) $ssub = 'Ямало-Ненецкий автономный округ';
            if(str_contains($ssub, 'НАО')) $ssub = 'Ненецкий автономный округ';
            if(str_contains($ssub, 'Чукотский АО')) $ssub = 'Чукотский автономный округ';

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