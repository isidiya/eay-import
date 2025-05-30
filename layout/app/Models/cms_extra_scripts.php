<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class cms_extra_scripts extends Model
{
	protected  $table="cms_extra_scripts";
    protected $primaryKey = 'ces_server_id';
    public    $timestamps = false;
    protected $fillable  = ['ces_header_scripts','ces_footer_scripts','ces_last_modified '];
    const cached_minutes = 5;
	public static function find_extra_script($ces_server_id=1){
        //caches the value for the lifetime of the request so once we set the page_id in the controller we would be able to access it with this service elsewhere
        $extra_script = Cache::remember("cache_extra_script_".$ces_server_id, self::cached_minutes, function ()use ($ces_server_id) {
				$es_tmp = self::where("ces_server_id",$ces_server_id)->first();
                return $es_tmp;
			});
		return isset($extra_script) ? $extra_script : new cms_extra_scripts();
    }
}