<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class enviroment_variables extends Model
{
	protected  $table="enviroment_variables";
    protected $primaryKey = 'id';
    public    $timestamps = false;
    protected $fillable  = ['env_variable','env_description','env_value','start_date','end_date'];
    const cached_minutes = 1440;


	public static function find_env($env_variable){
        //caches the value for the lifetime of the request so once we set the page_id in the controller we would be able to access it with this service elsewhere
        static $all = null;
        if (is_null($all)) {
            $all = Cache::remember("model_cache_all_env", self::cached_minutes, function ()  {

                $np_indexed = [];
                $all_tmp = self::all();
                if ($all_tmp) {
                    foreach ($all_tmp as $item) {
                        $np_indexed[$item->env_variable] = $item->env_value;
                    }
                }
                return $np_indexed;
            });
        }
        if ($env_variable !== 0) {
            return isset($all[$env_variable]) ? $all[$env_variable] : null;
        }
        return $all;
    }
}
