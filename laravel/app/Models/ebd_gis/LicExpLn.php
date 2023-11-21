<?php

namespace App\Models\ebd_gis;

class LicExpLn extends EbdGisModel
{
    protected $table = 'lic_exp_ln';

    protected $casts = [
        's_лиц' => 'float'
    ];
}