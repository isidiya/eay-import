<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class pressrelease_contacts extends Model
{
	protected  $table="pressrelease_contacts";
    protected $primaryKey = 'id';
    public    $timestamps = false; 
    protected $fillable  = ['id','name','email','agency_id'];
    
    public static function get_agency_contacts($id) {
       return pressrelease_contacts::where('agency_id', $id)->get();
    }
    
}
