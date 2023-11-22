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
    public static function import()
    {
        $newCount = $unsavedCount = 0;

        [$newCount, $unsavedCount] = self::importFromLicTable();

        dump("Flang total: Added " . $newCount . ', unsaved ' . $unsavedCount);
    }

    /**  Импорт записей из таблицы  ebd_gis.flangi */
    private static function importFromLicTable() : array
    {
        $newCount = $unsavedCount = 0;

            foreach(Flangi::all() as $f)
            {
                $licAndDate = self::getLicIdAndRdate($f->Выдана_лиц);

                try
                {
                    $newFlang = Flang::create([
                        //TODO
                        //'vid' => ?
                        'name' => $f->Название_у,
                        'deposit' => $f->Фланг_мест,
                        'isflang' => $f->Явл_фланго,
                        's_flang' => $f->Площадь ? str_ireplace(',', '.', $f->Площадь) : null,
                        'declarant' => $f->Заявитель,
                        'edate' => $f->Дата_экспе,
                        'resol' => $f->Резолюция_,
                        'ssub_rf_id' => $f->Регион ? DicSsubRf::where('value', $f->Регион)->first()->id : null,
                        'license_id' => $licAndDate ? $licAndDate[0] : null,
                        'rdate' => $licAndDate ? $licAndDate[1] : null,
                        'flang_status_id' => $f->Статус_по_ ? DicFlangStatus::where('value', $f->Статус_по_)->first()->id : null,
                        'comment' => $f->Примечание,
                        'geom' => $f->geom
                    ]);
            
                    $newFlang->refresh();

                    if( count(Flang::where('src_hash', $newFlang->src_hash)->get()) > 1)
                    {
                        $newFlang->delete();
                        $unsavedCount++;
                    }
                    else
                    {
                        $newCount++;
                    }
                }
                catch(Exception $e)
                {
                    dump($e->getMessage());
                    dd($f);
                    continue;
                }
            }

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
            dump('  Flangi: Ошибочная строка: ' . $licStr . ' лиц. и дата -> null');
            return null;
        };
    }
}