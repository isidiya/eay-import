<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class mgt_brands extends Model
{
	protected  $table="mgt_brands";
    protected $primaryKey = 'id';
    public    $timestamps = false; 
    protected $fillable  = ['id','slug','english_name','arabic_name','logo','logo_alt','content','website','shop_from','keywords','category_id'];
     
    public static function get_brand_by_id($id) {
       return mgt_brands::where('id', $id)->get();
    }
}
