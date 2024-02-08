<?php

namespace App\Models\ebd_gis;

use Illuminate\Database\Eloquent\Model;

class EbdGisModel extends Model
{
    //protected $connection = 'pgsql_ekbd_gis';
    protected $connection = 'pgsql_geosys_ebd_gis';
}