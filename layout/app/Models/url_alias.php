<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class url_alias extends Model
{
	protected  $table="url_alias";
    public    $timestamps = false;
}
