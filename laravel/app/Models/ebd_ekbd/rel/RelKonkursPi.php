<?php

namespace App\Models\ebd_ekbd\rel;
use App\Models\ebd_ekbd\EbdEkbdEntityModel;

class RelKonkursPi extends EbdEkbdEntityModel
{
    //public $incrementing = false;
    //protected $keyType = 'string';
    public $timestamps = false;
    const CREATED_AT = 'cdate';
    protected $table = 'rel_konkurs_pi';
}