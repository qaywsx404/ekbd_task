<?php

namespace App\Models\ebd_ekbd\dictionaries;

use Illuminate\Database\Eloquent\Model;

class DictionaryModel extends Model
{
    protected $connection = 'pgsql_geosys_ekbd';
    
    public $incrementing = false;
    public $timestamps = false;
    const CREATED_AT = 'cdate';
    protected $guarded = ['id'];
}