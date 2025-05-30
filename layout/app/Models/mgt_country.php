<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class mgt_country extends Model
{
	protected  $table="mgt_country";
    protected $primaryKey = 'id';
    public    $timestamps = false; 
    protected $fillable  = ['id','country_name'];
    
}
