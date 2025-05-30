<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class sponsored_links extends Model
{   
    protected  $table="sponsored_links";
    protected $primaryKey = 'id';
    public    $timestamps = false;
    protected $fillable  = ['link','thumbnail','title','advertiser'];
    const cached_minutes = 1440;


}
