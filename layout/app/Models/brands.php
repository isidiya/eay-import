<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class brands extends Model
{
	protected  $table="brands";
    protected $primaryKey = 'id';
    public    $timestamps = false; 
    protected $fillable  = ['id','brand','slug','logo'];
    
    public static function get_brand_by_id($id) {
       return brands::where('id', $id)->get();
    }
}
