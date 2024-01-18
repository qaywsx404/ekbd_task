<?php

namespace App\Http\Controllers\ImportControllers;

use App\Http\Controllers\Controller;
use App\Imports\DicSsubRfImport;
use App\Models\ebd_ekbd\dictionaries\DicSsubRf;
use App\Models\ebd_ekbd\dictionaries\DicSsubRfAlias;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use Ds\Set;

use App\Models\ebd_gis\LicExpLn;
use App\Models\ebd_gis\Flangi;
use App\Models\ebd_gis\LicExpPln;
use App\Models\ebd_gis\LicExpPt;
use App\Models\ebd_gis\LicPln;
use App\Models\ebd_gis\LicPt;
use App\Models\ebd_gis\Konkurs19;
use App\Models\ebd_gis\Konkurs20;
use App\Models\ebd_gis\Konkurs21;
use App\Models\ebd_gis\Konkurs22;
use App\Models\ebd_gis\Konkurs23;
use App\Models\ebd_gis\Konkurs24;
use App\Models\ebd_gis\NgMest;
use App\Models\ebd_gis\NgStruct;
use App\Models\ebd_gis\ZapovednikiLn;
use App\Models\ebd_gis\ZapovednikiPln;
use App\Models\ebd_gis\ZapovednikiPt;

class DicSsubRfImportController extends Controller
{
    public static function import() {
        
        Excel::import(new DicSsubRfImport, 'storage/app/ekbd_sub_m.xlsx');

        $mes = ("DicSsubRf: total " . DicSsubRf::count());
        echo "\t".$mes."\r\n";
        Log::channel('importlog')->info($mes);
        $mes = ("DicSsubRfAlias: total " . DicSsubRfAlias::count());
        echo "\t".$mes."\r\n";
        Log::channel('importlog')->info($mes);
    }

    public static function getUniqueSubs() : array {
        $ss = new Set();

        foreach(LicExpLn::distinct("Назв_СФ")->pluck("Назв_СФ")->flatten() as $s)
            if($s != null) $ss->add($s);
        foreach(LicExpPln::distinct("Назв_СФ")->pluck("Назв_СФ")->flatten() as $s)
            if($s != null) $ss->add($s);
        foreach(LicExpPt::distinct("Назв_СФ")->pluck("Назв_СФ")->flatten() as $s)
            if($s != null) $ss->add($s);
        foreach(LicPln::distinct("Назв_СФ")->pluck("Назв_СФ")->flatten() as $s)
            if($s != null) $ss->add($s);
        foreach(LicPt::distinct("Назв_СФ")->pluck("Назв_СФ")->flatten() as $s)
            if($s != null) $ss->add($s);

        foreach(Flangi::distinct("Регион")->pluck("Регион")->flatten() as $s)
            if($s != null) $ss->add($s);

        foreach(Konkurs19::distinct("Регион")->pluck("Регион")->flatten() as $s)
            if($s != null) $ss->add($s);
        foreach(Konkurs20::distinct("Регион")->pluck("Регион")->flatten() as $s)
            if($s != null) $ss->add($s);
        foreach(Konkurs21::distinct("Регион")->pluck("Регион")->flatten() as $s)
            if($s != null) $ss->add($s);
        foreach(Konkurs22::distinct("Регион")->pluck("Регион")->flatten() as $s)
            if($s != null) $ss->add($s);
        foreach(Konkurs23::distinct("Регион")->pluck("Регион")->flatten() as $s)
            if($s != null) $ss->add($s);
        foreach(Konkurs24::distinct("Регион")->pluck("Регион")->flatten() as $s)
            if($s != null) $ss->add($s);

        foreach(NgMest::distinct("Область")->pluck("Область")->flatten() as $s)
            if($s != null) $ss->add($s);
        foreach(NgMest::distinct("Округ")->pluck("Округ")->flatten() as $s)
            if($s != null) $ss->add($s);

        foreach(NgStruct::distinct("Область")->pluck("Область")->flatten() as $s)
            if($s != null) $ss->add($s);
        foreach(NgStruct::distinct("Округ")->pluck("Округ")->flatten() as $s)
            if($s != null) $ss->add($s);

        foreach(ZapovednikiLn::distinct("Регион")->pluck("Регион")->flatten() as $s)
            if($s != null) $ss->add($s);
        foreach(ZapovednikiPln::distinct("Регион")->pluck("Регион")->flatten() as $s)
            if($s != null) $ss->add($s);
        foreach(ZapovednikiPt::distinct("Регион")->pluck("Регион")->flatten() as $s)
            if($s != null) $ss->add($s);

        return $ss->toArray();
    }
}