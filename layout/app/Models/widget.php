<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class widget extends Model
{
    protected $table = 'widget';
    protected $primaryKey = 'cms_widget_id';
    public    $timestamps = false;
    const cached_minutes = 1;
    protected $fillable = ['widget_data'];

    public static function find_np($np_id){
        if(is_array($np_id)){
			$widget = Cache::remember("cachewidget_". implode("_", $np_id), self::cached_minutes, function () use ($np_id) {
				$widget = self::whereIn('np_widget_id', $np_id)->orderByRaw(\Illuminate\Support\Facades\DB::raw("FIELD(np_widget_id, ". implode(',',$np_id).")"))->get();

                return $widget;
			});
			return isset($widget) ? $widget : new widget();


            return self::whereIn('np_widget_id', $np_id)->orderByRaw(\Illuminate\Support\Facades\DB::raw("FIELD(np_widget_id, ". implode(',',$np_id).")"))->get();
        }

		$widget = Cache::remember("cachewidget_". $np_id, self::cached_minutes, function ()use ($np_id) {
			$widget = self::where('np_widget_id', $np_id)->first();

			return $widget;
		});
		return isset($widget) ? $widget : new widget();
    }

	public static function parent_np($np_id){
        if(is_array($np_id)){
            return self::whereIn('parent_widget_id', $np_id)->get();
        }
        return self::where('parent_widget_id', $np_id)->get();
    }

	public static function find_by_page_id($page_id){
        return self::where('page_id', $page_id)->get();
    }

    public static function find_np_by_page_id($page_id) {
       
        static $all = null;
        if (is_null($all)) {
            //caches the value for the lifetime of the request so once we set the page_id in the controller we would be able to access it with this service elsewhere
            $all = Cache::remember("model_cache_all_widgets_by_page_id_" . $page_id, self::cached_minutes, function () use ($page_id) {
                $np_indexed = [];
                $all_tmp = self::find_by_page_id($page_id);
                if ($all_tmp) {
                    foreach ($all_tmp as $item) {
                        $np_indexed[$item->page_id][$item->np_widget_id] = $item;
                    }
                }
                return $np_indexed;
            });
        }
        return isset($all) ? $all : new widget();
    }
}
