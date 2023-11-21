<?php

namespace App\Models\ebd_ekbd;

class License extends EbdEkbdEntityModel
{
    protected $table = 'license';

    protected $casts = [
        's_лиц' => 'float(12,3)',
    ];
}