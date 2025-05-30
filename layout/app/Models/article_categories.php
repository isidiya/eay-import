<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Layout\Website\Services\ThemeService;

/**
 * App\Models\article_categories
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article_categories newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article_categories newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\article_categories query()
 * @mixin \Eloquent
 */
class article_categories extends Model
{
    protected $table = "article_categories";
    protected $primaryKey = 'ac_id';
    public    $timestamps = false;

    const cached_minutes = 15;

    public static function get_all() {
        // Cache::forget("model_cache_all_categories");
        //caches the value for the lifetime of the request so once we set the page_id in the controller we would be able to access it with this service elsewhere
        static $all = null;
        if (is_null($all)) {
            $all = Cache::remember("model_cache_all_categories", self::cached_minutes, function (){
                $np_indexed = [];
                $all_tmp = self::where('ac_is_hidden', 0)->where('ac_xml_name', '!=', NULL)->whereNotIn('ac_xml_name', ThemeService::ConfigValue('EXCLUDED_CUSTOM_FIELDS'))->get();
                if ($all_tmp) {
                    foreach ($all_tmp as $item) {
                        $np_indexed[] = $item;
                    }
                }
                return $np_indexed;
            });
        }
        return $all;
    }

    public static function get_all_flags() {
        // Cache::forget("model_cache_all_categories_flags");
        //caches the value for the lifetime of the request so once we set the page_id in the controller we would be able to access it with this service elsewhere
        static $all = null;
        if (is_null($all)) {
            $all = Cache::remember("model_cache_all_categories_flags", self::cached_minutes, function (){
                $np_indexed = [];
                $all_tmp = self::where('ac_is_hidden', 0)->where('ac_xml_name', '!=', NULL)->where('ac_type', 'checkbox')->pluck('ac_xml_name');
                if ($all_tmp) {
                    foreach ($all_tmp as $item) {
                        $np_indexed[] = $item;
                    }
                }
                return $np_indexed;
            });
        }
        return $all;
    }
}
