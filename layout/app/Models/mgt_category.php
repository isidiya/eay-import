<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class mgt_category extends Model
{
	protected  $table="mgt_category";
    protected $primaryKey = 'id';
    public    $timestamps = false; 
    protected $fillable  = ['id','category_name'];
    
}
