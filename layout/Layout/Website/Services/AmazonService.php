<?php
/**
 * Created by PhpStorm.
 * User: timur
 * Date: 20.12.2018
 * Time: 16:31
 */

namespace Layout\Website\Services;


use Illuminate\Support\Facades\Cache;

class AmazonService
{
    const cached_minutes = 1;

    public static function get_pages(){
        return self::get_json_object(ThemeService::ConfigValue("JSON_URLS")."pages/pages.json");
    }

    public static function get_page($np_page_id){
        return self::get_json_object(ThemeService::ConfigValue("JSON_URLS")."pages/".$np_page_id.".json");
    }

    public static function get_menu($menu_id){
        return self::get_json_object(ThemeService::ConfigValue("JSON_URLS")."menus/" .$menu_id.".json");
    }

    private static function get_json_object($file_url){
        //Uncomment below if the cache is needed
        //$json_object = Cache::remember("json_object_cache::".$file_url, self::cached_minutes, function () use ($file_url) {
            return json_decode(self::get_json_file($file_url),true);
        //});
        //return $json_object;
    }

    private static function get_json_file($file_url){
        return file_get_contents($file_url);
    }
}