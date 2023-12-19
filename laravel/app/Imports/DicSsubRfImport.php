<?php

namespace App\Imports;

use App\Models\ebd_ekbd\dictionaries\DicSsubRf;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DicSsubRfImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new DicSsubRf([
           'code_region' => $row['code_region'],
           'region_name' => $row['region_name'], 
           'code_region_parent' => $row['code_region_parent'], 
           'is_fo' => $row['is_fo'], 
           'is_sf' => $row['is_sf'], 
        ]);
    }
}