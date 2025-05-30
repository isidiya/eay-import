<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Layout\Website\Helpers\UrlHelper;

class static_pages extends Model
{
    protected $table = 'static_pages';
    protected $primaryKey = 'page_id ';
    public    $timestamps = false;
    const cached_minutes = 5;
}
