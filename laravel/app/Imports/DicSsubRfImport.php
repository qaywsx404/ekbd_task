<?php

namespace App\Imports;

use App\Models\ebd_ekbd\dictionaries\DicSsubRf;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DicSsubRfImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        $newCount = 0;

        foreach($rows as $row)
        {
            $ob = DicSsubRf::firstOrCreate([
                'code_region' => $row['code_region'],
                'region_name' => $row['region_name'], 
                'code_region_parent' => $row['code_region_parent'], 
                'is_fo' => $row['is_fo'], 
                'is_sf' => $row['is_sf'], 
             ]);

             if($ob->wasRecentlyCreated) $newCount++;
        }

        echo("\tDicSsubRf: added $newCount\r\n");
    }
}