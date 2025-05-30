<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class mgt_actors extends Model
{
	protected  $table="mgt_actors";
    protected $primaryKey = 'id';
    public    $timestamps = false; 
    protected $fillable  = ['id','name','image','keywords'];
     
    public static function get_actor_by_id($id) {
       return mgt_actors::where('id', $id)->get();
    }
}
