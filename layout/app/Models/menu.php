<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class menu extends Model
{
	protected $table = 'menu';
    protected $primaryKey = 'cms_menu_id';
    public    $timestamps = false;
    protected $fillable=['menu_name'];

    const cached_minutes = 5;

    public static function find_np($np_id){
        //caches the value for the lifetime of the request so once we set the page_id in the controller we would be able to access it with this service elsewhere
        static $all = null;
        if(is_null($all)){
            $all = Cache::remember("model_cache_all_menu", self::cached_minutes, function () {
                $np_indexed = [];
                $all_tmp = self::all();
                if($all_tmp){
                    foreach ($all_tmp as $item){
                        $np_indexed[$item->np_menu_id] = $item;
                    }
                }
                return $np_indexed;
            });
        }
        return isset($np_indexed[$np_id]) ? $np_indexed[$np_id] : new menu();
    }

    //replaces menu_items() relation with the cached
    public function menu_items()
    {
        return menu_item::find_by_menu_id($this->np_menu_id);
    }
}
