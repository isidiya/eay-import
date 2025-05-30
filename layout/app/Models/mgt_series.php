<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class mgt_series extends Model
{
	protected  $table="mgt_series";
    protected $primaryKey = 'id';
    public    $timestamps = false;
    protected $fillable  = ['id','name','image','description','year','trailer_platform','trailer_link','actors']; 
    public static function get_serie_by_id($id) {
        return mgt_series::where('id', $id)->get();
    }
}
