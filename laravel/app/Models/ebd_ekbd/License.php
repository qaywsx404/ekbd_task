<?php

namespace App\Models\ebd_ekbd;

class License extends EbdEkbdEntityModel
{
    //public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'license';
    protected $casts = [
        's_лиц' => 'float(12,3)',
    ];
}