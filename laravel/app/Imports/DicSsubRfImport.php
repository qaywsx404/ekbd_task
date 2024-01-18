<?php

namespace App\Imports;

use App\Models\ebd_ekbd\dictionaries\DicSsubRf;
use App\Models\ebd_ekbd\dictionaries\DicSsubRfAlias;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DicSsubRfImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        $newCount = 0;
        $newCountAl = 0;

        foreach($rows as $row)
        {
            $newSub = DicSsubRf::firstOrCreate([
                'code_region' => $row['code_region'],
                'region_name' => $row['region_name'], 
                'code_region_parent' => $row['code_region_parent'], 
                'is_fo' => $row['is_fo'], 
                'is_sf' => $row['is_sf'], 
             ]);

            $als = $row['aliases'] ? explode(',', $row['aliases']) : [];
            
            foreach($als as $al)
            {
                $newSubAl = DicSsubRfAlias::firstOrCreate([
                    'ssub_rf_id' => DicSsubRf::where('region_name', $row['region_name'])->first()?->id,
                    'value' => $al
                ]);
                
                if($newSubAl->wasRecentlyCreated) $newCountAl++;
            }

            if($newSub->wasRecentlyCreated) $newCount++;
        }

        echo("\tDicSsubRf: added $newCount\r\n");
        echo("\tDicSsubRfAlias: added $newCountAl\r\n");
    }
}