<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class country extends Model
{
    protected $primaryKey = 'cms_country_id';
    public    $timestamps = false;
    protected  $table="country";
}
