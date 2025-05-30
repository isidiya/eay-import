<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class agency extends Model
{
	protected  $table="agency";
    protected $primaryKey = 'id';
    public    $timestamps = false;
    protected $fillable  = ['id','automate_uuid','name','slug','address'];
    
    public static function get_agency_by_id($id) {
       return agency::where('id', $id)->get();
    }
}
