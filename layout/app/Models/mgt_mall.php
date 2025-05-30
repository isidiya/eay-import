<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class mgt_mall extends Model
{
	protected  $table="mgt_mall";
    protected $primaryKey = 'id';
    public    $timestamps = false; 
    protected $fillable  = ['id','slug','name','address','region','country_id'];
    
    public function country(){
        return $this->belongsTo('App\Models\mgt_country','country_id','id');
    }
    
    
}
