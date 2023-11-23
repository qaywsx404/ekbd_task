<?php

namespace App\Models\ebd_ekbd;

use Illuminate\Database\Eloquent\Model;

class EbdEkbdEntityModel extends Model
{
    //public $incrementing = false;
    protected $keyType = 'string';
    const CREATED_AT = 'cdate';
    const UPDATED_AT = 'mdate';
    protected $guarded = [];
}