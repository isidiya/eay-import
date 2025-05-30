<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class mgt_shop extends Model
{
	protected  $table="mgt_shop";
    protected  $primaryKey = 'id';
    public     $timestamps = false; 
    protected  $fillable  = ['id','brands','phone','category','mall_id','address','country','region'];  
    
    public function countryName(){
        return $this->belongsTo('App\Models\mgt_country','country','id');
    }
    public function mall(){
        return $this->belongsTo('App\Models\mgt_mall','mall_id','id');
    }
    public function brand(){
        return $this->belongsTo('App\Models\mgt_brands','brands','id');
    }
    
}
