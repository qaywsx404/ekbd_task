<?php

namespace App\Models\ebd_ekbd\konkurs_res;
use App\Models\ebd_ekbd\EbdEkbdEntityModel;

class KonkursReservesN extends EbdEkbdEntityModel
{
    //public $incrementing = false;
    //protected $keyType = 'string';
    public $timestamps = false;
    const CREATED_AT = 'cdate';
    protected $table = 'konkurs_reserves_n';
}