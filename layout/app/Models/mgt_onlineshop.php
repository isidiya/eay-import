<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class mgt_onlineshop extends Model
{
	protected  $table="mgt_onlineshop";
    protected $primaryKey = 'id';
    public    $timestamps = false; 
    protected $fillable  = ['id','website','brands'];  
}
