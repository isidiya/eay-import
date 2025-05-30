<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ads_zones extends Model {

    protected $table = "ads_zones";
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = ['zone_name', 'is_active'];
    const cached_minutes = 1440;

    public static function find_np($zone_id) {
        static $all = null;
        if (is_null($all)) {
            //caches the value for the lifetime of the request so once we set the page_id in the controller we would be able to access it with this service elsewhere
            $all = Cache::remember("model_cache_all_zones", self::cached_minutes, function () {
                        $indexed_zones = [];
                        $all_tmp = self::orderBy('zone_name','asc')->get();
                        if ($all_tmp) {
                            foreach ($all_tmp as $item) {
                                $indexed_zones[$item->id] = $item;
                            }
                        }
                        return $indexed_zones;
                    });
        }
        if ($zone_id == 0) {
            return $all;
        }
        return isset($all[$zone_id]) ? $all[$zone_id] : new ads_zones();
    }
    
    public static function find_by_name($zone_name) {
        static $all = null;
        
        if (is_null($all)) {
            //caches the value for the lifetime of the request so once we set the page_id in the controller we would be able to access it with this service elsewhere
            $all = Cache::remember("model_cache_all_zones_names", self::cached_minutes, function () {
                        $indexed_zones = [];
                        $all_tmp = self::all();
                        if ($all_tmp) {
                            foreach ($all_tmp as $item) {
                                $indexed_zones[$item->zone_name] = $item;
                            }
                        }
                        return $indexed_zones;
                    });
        }
        return isset($all[$zone_name]) ? $all[$zone_name] : new ads_zones();
    }

}
