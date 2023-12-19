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
use Exception;

class StructImportController extends Controller
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

        dump("Struct total: Added " . $newCount . ', unsaved ' . $unsavedCount);
    }

    /**  Импорт записей из таблиц  ebd_gis.ng_struct */
    private static function importFromTable() : array
    {
        $newCount = $unsavedCount = 0;

        Struct::truncate();

            foreach(NgStruct::all() as $s)
            {
                try
                {
                    $newStruct = Struct::make([
                        'name' => $s->СПИСОК_СТР,
                        'deposit_type_id' => $s->Тип ? DicDepositType::where('value', $s->Тип)->first()?->id : null,
                        'deposit_stage_id' => $s->Стадия ? DicDepositStage::where('value', $s->Стадия)->first()?->id : null,
                        'ng_struct' => $s->Отложения,
                        'oblast_ssub_rf_id' => self::getSsubId($s->Область),
                        'okrug_ssub_rf_id' => self::getSsubId($s->Округ),
                        'ngp_id' => $s->ngp ? Ngp::where('name', $s->ngp)->first()?->id : null,
                        'ngo_id' => $s->ngo ? Ngo::where('name', $s->ngo)->first()?->id : null,
                        'ngr_id' => $s->ngr ? Ngr::where('name', $s->ngr)->first()?->id : null,
                        'arctic_zone_id' => $s->Аркт_зона ? DicArcticZone::where('value',$s->Аркт_зона)->first()?->id : null,
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
            
                    $newStruct->src_hash = md5($s->gid);
                    
                    if(!Struct::where('src_hash', $newStruct->src_hash)->exists())
                    {
                        $newStruct->save();
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
                    dd($s);
                    continue;
                }
            }

        if(self::$showInf) dump("   Struct frm ng_struct" .' ('.NgStruct::count().') '.  ": Added " . $newCount . ', unsaved ' . $unsavedCount);

        return [$newCount, $unsavedCount];
    }
    private static function getSsubId(?string $ssub) : ?string {
        if($ssub)
        {
            if(str_contains($ssub, 'Шельф')) $ssub = 'Шельф';
            if(str_contains($ssub, 'Сев.-Западный')) $ssub = 'Северо-Западный федеральный округ';
            if(str_contains($ssub, 'Алания')) $ssub = 'Алания';
            if(str_contains($ssub, 'Хакассия')) $ssub = 'Хакасия';

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