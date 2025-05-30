<?php

namespace App\Models;

use App\Http\Controllers\CommonController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Layout\Website\Helpers\UrlHelper;
use Layout\Website\Services\PageService;
use Layout\Website\Services\ThemeService;

class page extends Model {

    protected $table = 'page';
    protected $primaryKey = 'cms_page_id';
    public $timestamps = false;

    const cached_minutes = 10;

    public function __construct(array $attributes = []) {
        parent::__construct($attributes);
        $this->page_title = "";
    }

    public static function find_np($np_id) {
        static $all = null;
        if (is_null($all)) {
            //caches the value for the lifetime of the request so once we set the page_id in the controller we would be able to access it with this service elsewhere
            $all = Cache::remember("model_cache_all_pages", self::cached_minutes, function () {
                        $np_indexed = [];
                        $all_tmp = self::all();
                        if ($all_tmp) {
                            foreach ($all_tmp as $item) {
                                $np_indexed[$item->np_page_id] = $item;
                            }
                        }
                        return $np_indexed;
                    });
        }
        if ($np_id == 0) {
            return $all;
        }
        return isset($all[$np_id]) ? $all[$np_id] : new page();
    }

    public static function find_widgets_np($np_id) {
        static $all = null;
        if (is_null($all)) {
            //caches the value for the lifetime of the request so once we set the page_id in the controller we would be able to access it with this service elsewhere
            $all = Cache::remember("model_cache_widget_pages_" . $np_id, 1, function () use($np_id) {
                        $np_indexed = [];
                        $all_tmp = widget::where("page_id", $np_id)->get();
                        if ($all_tmp) {
                            foreach ($all_tmp as $item) {
                                $np_indexed[$np_id][$item->np_widget_id] = $item;
                            }
                        }
                        return $np_indexed;
                    });
        }
        if ($np_id == 0) {
            return $all;
        }
        return isset($all[$np_id]) ? $all[$np_id] : new page();
    }

    public static function find_by_title($page_title) {
        //caches the value for the lifetime of the request so once we set the page_id in the controller we would be able to access it with this service elsewhere
        static $all = null;
        if (is_null($all)) {
            $all = Cache::remember("model_cache_all_pages_name", self::cached_minutes, function () {
                        $np_indexed = [];
                        $all_tmp = self::find_np(0);
                        if ($all_tmp) {
                            foreach ($all_tmp as $item) {
                                $np_indexed[strtolower($item->page_title)] = $item;
                            }
                        }
                        return $np_indexed;
                    });
        }
        if ($page_title == '') {
            return $all;
        }
        return isset($all[$page_title]) ? $all[$page_title] : new page();
    }

    public static function find_by_section($section_id) {
        $page = self::where('page_section_id', $section_id)->first();
        return $page;
    }

    public static function find_by_last_section($section_id) {
        $page = self::where('page_section_id', $section_id)->orderBy('np_page_id', 'DESC')->first();
        return $page;
    }

    public function getSimpleUrlAttribute() {
        return url('/') . '/' . UrlHelper::clean_text($this->page_title);
    }

    public function page_title($isHomePage, $pageTitle, $seoMetaTitle) {
        if ($isHomePage) {
            return ThemeService::ConfigValue('NEWSPAPER_PAGE_TITLE') . ThemeService::ConfigValue('SEPARATOR') . (strlen(trim($seoMetaTitle)) > 0 ? $seoMetaTitle : $pageTitle);
        } else {
            return (strlen(trim($seoMetaTitle)) > 0 ? $seoMetaTitle : $pageTitle) . ThemeService::ConfigValue('SEPARATOR') . ThemeService::ConfigValue('NEWSPAPER_PAGE_TITLE');
        }
    }

    public function getPageBodyCleanAttribute() {

        $re = '/\[caption (.*)\](.*)\[\/caption\]/';
        $this->page_body = preg_replace($re, '', $this->page_body);

        return $this->page_body;

    }

    public function getSeoMetaKeywordsAttribute($value) {
        //this is laravel logic of modifying an existing table field value
        return !empty($value) && !ctype_space($value) ? $value : ThemeService::ConfigValue('META_KEY');
    }

    public function getSeoMetaDescriptionAttribute($value) {
        //this is laravel logic of modifying an existing table field value
        $separator = (ThemeService::ConfigValue('META_DESC_SEPARATOR')) ? ThemeService::ConfigValue('META_DESC_SEPARATOR') : " ";
        return !empty($value) && !ctype_space($value) ? $value : ThemeService::ConfigValue('META_DESC') . $separator . $this->page_title;
    }

    public function getHeaderScriptAttribute($value) {
        //this is laravel logic of modifying an existing table field value
        return !empty($value) ? $value : ThemeService::ConfigValue('HEADER');
    }

    public function getExtraScriptsAttribute() {
        $ces_header_script =  \App\Models\cms_extra_scripts::find_extra_script();
        if(!empty($ces_header_script)){
            return $ces_header_script->ces_header_scripts;
        }else{
            return '';
        }
    }

    public function getSeoUrlAttribute() {
        return UrlHelper::build_seo_url($this->np_page_id, 'page');
    }

    public function getPageSectionInfoAttribute() {
        return section::find_np($this->page_section_id);
    }

}
