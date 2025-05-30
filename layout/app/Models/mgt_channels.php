<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class mgt_channels extends Model
{
	protected  $table="mgt_channels";
    protected $primaryKey = 'id';
    public    $timestamps = false; 
    protected $fillable  = ['id','name','image','description'];
     
    public static function get_actor_by_id($id) {
       return mgt_channels::where('id', $id)->get();
    }
}
