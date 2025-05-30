<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Layout\Website\Helpers\UrlHelper;

class sub_section extends Model
{
    protected $table = 'sub_section';
    protected $primaryKey = 'cms_sub_section_id';
    public    $timestamps = false;

    const cached_minutes = 5;

    public static function find_np($np_id){
        //caches the value for the lifetime of the request so once we set the page_id in the controller we would be able to access it with this service elsewhere
        static $all = null;
        if(is_null($all)){
            $all = Cache::remember("model_cache_all_sub_sections", self::cached_minutes, function () {
                $np_indexed = [];
                $all_tmp = self::all();
                if($all_tmp){
                    foreach ($all_tmp as $item){
                        $np_indexed[$item->np_sub_section_id] = $item;
                    }
                }
                return $np_indexed;
            });
        }
        if($np_id >0){
            return isset($all[$np_id]) ? $all[$np_id] : new sub_section();
        }
        return $all;


    }

    public static function find_by_cms_id($cms_id){
        //caches the value for the lifetime of the request so once we set the page_id in the controller we would be able to access it with this service elsewhere
        static $all = null;
        if(is_null($all)){
            $all = Cache::remember("model_cache_all_sub_sections_cms", self::cached_minutes, function () {
                $cms_indexed = [];
                $all_tmp = self::find_np(0);
                if($all_tmp){
                    foreach ($all_tmp as $item){
                        $cms_indexed[$item->cms_sub_section_id] = $item;
                    }
                }
                return $cms_indexed;
            });
        }
        if($cms_id >0){
            return isset($all[$cms_id]) ? $all[$cms_id] : new sub_section();
        }
        return $all;


    }

	public static function find_np_by_name($sub_section_name){
        //caches the value for the lifetime of the request so once we set the page_id in the controller we would be able to access it with this service elsewhere
        static $all = null;
        if(is_null($all)){
            $all = Cache::remember("model_cache_all_sub_sections_name", self::cached_minutes, function () {
                $np_indexed = [];
                $all_tmp = self::find_np(0);
                if($all_tmp){
                    foreach ($all_tmp as $item){
                        $np_indexed[strtolower($item->sub_section_name)] = $item;
                    }
                }
                return $np_indexed;
            });
        }
        return isset($all[$sub_section_name]) ? $all[$sub_section_name] : new sub_section();
    }

    //find_np_by_url
    // find sub_section_name contain space and replaced by -
    public static function find_np_by_url($sub_section_url){
        //caches the value for the lifetime of the request so once we set the page_id in the controller we would be able to access it with this service elsewhere
        static $all = null;
        if(is_null($all)){
            $all = Cache::remember("model_cache_all_sub_sections_url", self::cached_minutes, function () {
                $np_indexed = [];
                $all_tmp = self::find_np(0);
                if($all_tmp){
                    foreach ($all_tmp as $item){
                        $np_indexed[\Layout\Website\Helpers\UrlHelper::clean_url($item->sub_section_name)] = $item;
                    }
                }
                return $np_indexed;
            });
        }
        return isset($all[$sub_section_url]) ? $all[$sub_section_url] : new sub_section();
    }
    //get the live record by url; exclude the records from migration
    public static function find_np_live_by_url($sub_section_url){
        //caches the value for the lifetime of the request so once we set the page_id in the controller we would be able to access it with this service elsewhere
        static $all = null;
        if(is_null($all)){
            $all = Cache::remember("model_cache_all_sub_sections_live_url", self::cached_minutes, function () {
                $np_indexed = [];
                $all_tmp = self::where('np_sub_section_id','>',0)->get();
                if($all_tmp){
                    foreach ($all_tmp as $item){
                        $np_indexed[\Layout\Website\Helpers\UrlHelper::clean_url($item->sub_section_name)] = $item;
                    }
                }
                return $np_indexed;
            });
        }
        return isset($all[$sub_section_url]) ? $all[$sub_section_url] : new sub_section();
    }

    public static function find_by_section_id($section_id){
        $np_indexed = self::all_indexed('section_id');
        return isset($np_indexed[$section_id]) ? $np_indexed[$section_id] : [];
    }

    //function to get sub section by url and section_id (in case sub section name is duplicated in db)
    public static function find_by_url_section($sub_section_url,$section_id){
        $all = self::all_indexed('section_id');
        if(isset($all)){
            if(isset($all[$section_id])){
                $sub_sections = $all[$section_id];
                foreach($sub_sections as $sub){
                    if(\Layout\Website\Helpers\UrlHelper::clean_url($sub->sub_section_name) == $sub_section_url){
                        return $sub;
                    }
                }
            }
            return new sub_section();
        };
        
        return new sub_section();
    }

    protected static function all_indexed($index){
        //caches the value for the lifetime of the request so once we set the page_id in the controller we would be able to access it with this service elsewhere
        static $all = null;
        if(is_null($all)){
            $all = Cache::remember("model_cache_all_sub_section", self::cached_minutes, function () {
                $np_indexed = [];
                $all_tmp    = self::find_np(0);
                if ($all_tmp) {
                    foreach ($all_tmp as $item) {
                        //index for np_id
                        $np_indexed['np_sub_section_id'][$item->np_sub_section_id] = $item;
                        //index for sections
                        if(!empty($item->section_id)) {
                            if (!isset($np_indexed['section_id'][ $item->section_id ])) {
                                $np_indexed['section_id'][ $item->section_id ] = [];
                            }
                            $np_indexed['section_id'][ $item->section_id ][] = $item;
                        }
                    }
                }
                return $np_indexed;
            });
        }
		if(!empty($all))
			return $all[$index];
		else
			return;
	}

    public static function get_subsection($subsection_name,$section_id=0){
        if ($section_id > 0) {
            return self::where('section_id', $section_id)->where('sub_section_name',$subsection_name)->first();
        } else {
            return self::where('sub_section_name',$subsection_name)->first();
        }

    }

    /**
     * sets the sub_section->section_name
     */
    public function getSectionNameAttribute(){
        $section = section::find_np($this->section_id);
        return !empty($section->section_name) ? $section->section_name : '';
    }

    public static function get_subsections_by_ids($subsections_ids) {
        return self::whereIn('np_sub_section_id', $subsections_ids)->get();
    }

    /**
     * sets the section->sub_section_url
     */
    public function getSubSectionUrlAttribute(){
        return UrlHelper::build_seo_url(1, 'sub_section', '', $this->np_sub_section_id);
    }


}
