<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Layout\Website\Helpers\UrlHelper;
use Layout\Website\Services\ThemeService;
use App\Http\Controllers\CommonController;

class section extends Model {

    protected $table = 'section';
    protected $primaryKey = 'cms_section_id';
    public $timestamps = false;
    protected $fillable = ['section_name', 'section_color'];


    const cached_minutes = 10;

    public function sub_sections() {
        $relation = $this->hasMany('App\Models\sub_section', 'section_id', 'np_section_id');
        return $relation;
    }

    public static function find_np($np_id,$with_sub_section=0) {
        //caches the value for the lifetime of the request so once we set the page_id in the controller we would be able to access it with this service elsewhere
        static $all = null;
        if (is_null($all)) {
            //$all = Cache::remember("model_cache_all_sections" . ($with_sub_section ? ('_subsection') : ''), self::cached_minutes, function () use ($with_sub_section) {
            $all = Cache::remember("model_cache_all_sections", self::cached_minutes, function () use ($with_sub_section) {
                //SERKAN : I think we can always get with subsections , it is just 1 more query
                $np_indexed = [];
                //if ($with_sub_section) {
                $all_tmp = self::with('sub_sections')->get();
                //} else {
                //    $all_tmp = self::all();
                //}
                if ($all_tmp) {
                    foreach ($all_tmp as $item) {
                        $np_indexed[$item->np_section_id] = $item;
                    }
                }
                return $np_indexed;
            });
        }
        if ($np_id != 0) {
            return isset($all[$np_id]) ? $all[$np_id] : new section();
        }
        return $all;
    }

    public static function find_np_live($np_id) {
        static $all = null;
        if (is_null($all)) {
            $all = Cache::remember("model_cache_all_sections_live", self::cached_minutes, function () {
                $np_indexed = [];
                $all_tmp = self::where('np_section_id','>',0)->orwhere(function($q){$q->where('np_section_id','<',0)->where('mini_cms',1);})->with('sub_sections')->get();
                if ($all_tmp) {
                    foreach ($all_tmp as $item) {
                        $np_indexed[$item->np_section_id] = $item;
                    }
                }
                return $np_indexed;
            });
        }
        if ($np_id != 0) {
            return isset($all[$np_id]) ? $all[$np_id] : new section();
        }
        return $all;
    }

    public static function find_by_cms_id($cms_id) {
        //caches the value for the lifetime of the request so once we set the page_id in the controller we would be able to access it with this service elsewhere
        static $all = null;
        if (is_null($all)) {
            $all = Cache::remember("model_cache_all_sections_cms", self::cached_minutes, function () {
                        $cms_indexed = [];
                        $all_tmp = self::find_np(0);
                        if ($all_tmp) {
                            foreach ($all_tmp as $item) {
                                $cms_indexed[$item->cms_section_id] = $item;
                            }
                        }
                        return $cms_indexed;
                    });
        }
        if ($cms_id > 0) {
            return isset($all[$cms_id]) ? $all[$cms_id] : new section();
        }
        return $all;
    }

    public static function find_np_by_name($section_name) {
        static $all = null;
        if (is_null($all)) {
            //caches the value for the lifetime of the request so once we set the page_id in the controller we would be able to access it with this service elsewhere
            $all = Cache::remember("model_cache_all_sections_name", self::cached_minutes, function () {
                $np_indexed = [];
                $all_tmp = self::find_np(0);
                if ($all_tmp) {
                    foreach ($all_tmp as $item) {
                        $np_indexed[mb_strtolower($item->section_name)] = $item;
                    }
                }
                return $np_indexed;
            });
        }
        return isset($all[$section_name]) ? $all[$section_name] : new section();
    }


    //find_np_by_url
    // find section_name contain space and replaced by -
    public static function find_np_by_url($section_url) {
        //caches the value for the lifetime of the request so once we set the page_id in the controller we would be able to access it with this service elsewhere
        static $all = null;
        if (is_null($all)) {
            $all = Cache::remember("model_cache_all_sections_url", self::cached_minutes, function () {
                        $np_indexed = [];
                        $all_tmp = self::find_np(0);
                        if ($all_tmp) {
                            foreach ($all_tmp as $item) {
                                $np_indexed[\Layout\Website\Helpers\UrlHelper::clean_url($item->section_name)] = $item;
                            }
                        }
                        return $np_indexed;
                    });
        }
        return isset($all[\Layout\Website\Helpers\UrlHelper::clean_url($section_url)]) ? $all[\Layout\Website\Helpers\UrlHelper::clean_url($section_url)] : new section();
    }
    //get the live record by url; exclude the records from migration
    public static function find_np_live_by_url($section_url) {
        static $all = null;
        if (is_null($all)) {
            $all = Cache::remember("model_cache_all_sections_live_url", self::cached_minutes, function () {
                $np_indexed = [];
                $all_tmp = self::where('np_section_id','>',0)->with('sub_sections')->get();
                if ($all_tmp) {
                    foreach ($all_tmp as $item) {
                        $np_indexed[\Layout\Website\Helpers\UrlHelper::clean_url($item->section_name)] = $item;
                    }
                }
                return $np_indexed;
            });
        }
         return isset($all[\Layout\Website\Helpers\UrlHelper::clean_url($section_url)]) ? $all[\Layout\Website\Helpers\UrlHelper::clean_url($section_url)] : new section();
    }


    public static function get_sections_by_color_id($section_color) {
        return section::where('section_color', $section_color)->with('sub_section')->get();
    }

    //replaces sub_section() relation with the cached one
    public function getSubSectionAttribute() {
        return sub_section::find_by_section_id($this->np_section_id);
    }

    public function getFixColorAttribute() {
        if(!empty($this->section_color)){
            if(strpos($this->section_color,'#') !== false){
                return $this->section_color;
            } else {
               return '#'.$this->section_color;
            }
        }else{
            return '';
        }
    }

    public function name_or_info() {
        return ThemeService::ConfigValue('SECTION_INFO') == 1 ? $this->section_info : $this->section_name;
    }

    public static function find_section($section=0){
        if(is_numeric($section)){
            return self::find_np($section);
        }else{
            return self::find_np_by_url($section);
        }
    }

    public static function get_sections_by_ids($sections_ids) {
        return section::whereIn('np_section_id', $sections_ids)->get();
    }

    /**
     * sets the section->section_url
     */
    public function getSectionUrlAttribute(){
        return UrlHelper::build_seo_url(1, 'section', '', $this->np_section_id);
    }

}
