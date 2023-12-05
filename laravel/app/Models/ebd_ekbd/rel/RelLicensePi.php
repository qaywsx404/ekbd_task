<?php

namespace App\Models\ebd_ekbd\rel;
use App\Models\ebd_ekbd\EbdEkbdEntityModel;

class RelLicensePi extends EbdEkbdEntityModel
{
    //public $incrementing = false;
    //protected $keyType = 'string';
    public $timestamps = false;
    const CREATED_AT = 'cdate';
    protected $table = 'rel_license_pi';
}