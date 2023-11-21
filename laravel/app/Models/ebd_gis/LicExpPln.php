<?php

namespace App\Models\ebd_gis;

class LicExpPln extends EbdGisModel
{
    protected $table = 'lic_exp_pln';

    protected $casts = [
        's_лиц' => 'float'
    ];
}