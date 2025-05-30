<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Layout\Website\Helpers\UrlHelper;
use App\Http\Controllers\CommonController;
use Layout\Website\Services\ThemeService;

class menu_item extends Model
{
	protected $table = 'menu_item';
    protected $primaryKey = 'cms_menu_items_id';
    public    $timestamps = false;
    protected $fillable =['menu_id', 'menu_items_name', 'parent_id','page_id','section_id','subsection_id'];

    const cached_minutes = 5;

    public static function find_np($np_id){
        $np_indexed = self::all_indexed('np_menu_item_id');
        return isset($np_indexed[$np_id]) ? $np_indexed[$np_id] : new menu_item();
    }
    
    public function section(){
        $relation = $this->hasMany('App\Models\section','np_section_id','section_id');
        return $relation;
    }

    public function subsection(){
        $relation = $this->hasMany('App\Models\sub_section','np_sub_section_id','subsection_id');
        return $relation;
    }

    public static function find_by_section_id($section_id){
        $np_indexed = self::all_indexed('section_id');
        return isset($np_indexed[$section_id]) ? $np_indexed[$section_id] : new menu_item();
    }

    public static function find_by_sub_section_id($sub_section_id){
        $np_indexed = self::all_indexed_by_subsection($sub_section_id);
        return $np_indexed;
    }

    public static function find_by_parent_id($parent_id){
        $np_indexed = self::all_indexed('parent_id');
        return isset($np_indexed[$parent_id]) ? $np_indexed[$parent_id] : [];
    }

    public static function find_by_menu_id($menu_id){
        $np_indexed = self::all_indexed('menu_id');
        return isset($np_indexed[$menu_id]) ? $np_indexed[$menu_id] : [];
    }
    
    public static function find_by_page_id($page_id,$menu_id = 0,$parent_id = 0){
        return menu_item::where('page_id', $page_id)->get();
    }
    public static function find_menu_by_page_id($page_id,$menu_id = 0,$parent_id = 0,$section_id=0,$sub_section_id=0){

        $menu_query = menu_item::where('page_id', $page_id)->where('menu_id',$menu_id);
        if($parent_id){
            $menu_query = $menu_query->where('parent_id','>',0);
        }else{
            $menu_query = $menu_query->where('parent_id',0);
        }
        if($section_id){
            $menu_query = $menu_query->where('section_id',$section_id);
        }
        if($sub_section_id){
            $menu_query = $menu_query->where('sub_section_id',$sub_section_id);
        }
        return $menu_query->get();
    }

    public static function find_menu_by_section_id($section_id=0,$sub_section_id = 0,$menu_id = 0){

        $menu_query = menu_item::where('menu_id',$menu_id);
        if($section_id){
            $menu_query = $menu_query->where('section_id',$section_id);
        }
        if($sub_section_id){
            $menu_query = $menu_query->where('subsection_id',$sub_section_id);
        }else{
			$menu_query = $menu_query->where('subsection_id',0);
		}
        return $menu_query->first();
    }


    protected static function all_indexed($index){
        //caches the value for the lifetime of the request so once we set the page_id in the controller we would be able to access it with this service elsewhere
        static $all = null;
        if(is_null($all)){
            $all = Cache::remember("model_cache_all_menu_items", self::cached_minutes, function () {
                $np_indexed = [];
                $all_tmp = self::with('section')->orderBy("menu_items_order","asc")->get();
                if($all_tmp){
                    foreach ($all_tmp as $item){
                        //index for np_id
                        $np_indexed['np_menu_item_id'][$item->np_menu_items_id] = $item;
                        //index for sections
                        if(!isset($np_indexed['section_id'][$item->section_id])) {
                            $np_indexed['section_id'][ $item->section_id ] = $item; //we need only the first for this
                        }
                        //index for sub menus
                        if(!empty($item->parent_id)) {
                            if(!isset($np_indexed['parent_id'][$item->parent_id])){
                                $np_indexed['parent_id'][$item->parent_id] = [];
                            }
                            $np_indexed['parent_id'][$item->parent_id][self::order_key_index($item->np_menu_items_id, $item->menu_items_order)] = $item;
                        }
                        //index for menu_id
                        if(!empty($item->menu_id) && $item->parent_id==0) {
                            if(!isset($np_indexed['menu_id'][$item->menu_id])){
                                $np_indexed['menu_id'][$item->menu_id] = [];
                            }
                            $np_indexed['menu_id'][$item->menu_id][self::order_key_index($item->np_menu_items_id, $item->menu_items_order)] = $item;
                        }
                    }
					if(isset($np_indexed['parent_id'])){
						ksort($np_indexed['parent_id']);
					}
                    ksort($np_indexed['menu_id']);
                }
                return $np_indexed;
            });
        }
        return isset($all[$index]) ? $all[$index] : new menu_item();
    }

    protected static function all_indexed_by_subsection($index){
        //caches the value for the lifetime of the request so once we set the page_id in the controller we would be able to access it with this service elsewhere
        static $all = null;
        if(is_null($all)){
            $all = Cache::remember("model_cache_all_menu_items_by_subsection", self::cached_minutes, function () {
                $np_indexed = [];
                $all_tmp = self::with('subsection')->where('subsection_id','>',0)->orderBy("menu_items_order","asc")->get();
                if($all_tmp) {
                    foreach ($all_tmp as $item) {
                        //index for subsection_id
                        $np_indexed[$item->subsection_id] = $item;
                    }
                }
                return $np_indexed;
            });
        }
        return isset($all[$index]) ? $all[$index] : new menu_item();
    }

    public function getMenuAttribute()
    {
        return menu::find_np($this->section_id);
        //return $this->belongsTo('App\Models\menu','menu_id','np_menu_id');
    }

    public function getPageAttribute()
    {
        return page::find_np($this->page_id);
        //return $this->belongsTo('App\Models\page','page_id','np_page_id');
    }

    public function getParentAttribute()
    {
        return menu_item::find_np($this->parent_id);
        //return $this->belongsTo('App\Models\menu_item', 'np_menu_items_id', 'parent_id');
    }

    public function getSectionAttribute()
    {
        return section::find_np($this->section_id);
        //return $this->hasOne('App\Models\section','np_section_id', 'section_id');
    }

    public function getSubSectionAttribute()
    {
        return sub_section::find_np($this->sub_section_id);
        //return $this->hasOne('App\Models\sub_section','np_sub_section_id','sub_section_id');
    }

    public function getSubMenuItemsAttribute()
    {
        return self::find_by_parent_id($this->np_menu_items_id);
        //return $this->hasMany('App\Models\menu_item', 'parent_id', 'np_menu_items_id')->orderBy('menu_items_order','asc');
    }

    public function media_info(){
        try {
            return json_decode($this->menu_items_media_info);
        }
        catch (Exception $e) {

        }

        return new \stdClass();
    }

    public function active_page($page_id){
        $active = false;
        if($page_id != 0 && ( $page_id == $this->page_id || ( $this->parent && $page_id == $this->parent->page_id ))){
            $active = true;
        }

        return $active;
    }

    public function active_section($section_id){
        $active = false;
        if($section_id != 0 && $this->section->np_section_id == $section_id && empty($this->subsection_id) ){
            $active = true;
        }
        return $active;
    }

    public function getSeoUrlAttribute(){
        if(empty($this->parent_id) && !empty($this->menu_items_link)){ //main menu
            // if(is_numeric(strpos($this->menu_items_link,"https://")) || is_numeric(strpos($this->menu_items_link,"http://"))){
            //     return $this->menu_items_link . '"target=" _blank';
            // }
            return $this->menu_items_link;
        }

        if(!empty($this->parent_id) && $this->page_id==0){ //sub menu with no page id
            if(is_numeric(strpos($this->menu_items_link,"https")) || is_numeric(strpos($this->menu_items_link,"http"))){
                return(urldecode($this->menu_items_link) . '"  target="_blank');
            }
            return($this->menu_items_link);
        }

        if ( $this->page_id == -1 && $this->section_id > 0 ){
            return UrlHelper::build_seo_url(1, 'morein', '', $this->section_id, $this->subsection_id);
        }

        if ( $this->page_id == 0 ){
            return '';
        }

        return  !empty($this->page) ? UrlHelper::build_seo_url($this->page, 'page', $this->page->page_title) : ''; //for header menu
        //return  UrlHelper::build_seo_url($this->page_id, 'page', $this->menu_items_name);    //for footter menu
    }

    /**
     * sets the menu_item->page_url_by_section()
     */
    public function getPageUrlBySectionAttribute(){

        $theme_controller_class = 'Themes\\' . ThemeService::Name() . '\\controllers\\' . CommonController::controller_class_name();
        if (class_exists($theme_controller_class)) {
            $themeController = new $theme_controller_class();
            if (method_exists($themeController, 'getPageUrlBySectionAttribute')) {
                $function_name = 'getPageUrlBySectionAttribute';
                $url =  $themeController->$function_name($this,'menu_item');
                if(!empty($url)){
                    return $url;
                }
            }
        }

        if(empty($this->parent_id) && !empty($this->menu_items_link)){ //main menu
            return $this->menu_items_link;
        }

        if(!empty($this->parent_id) && $this->page_id==0){ //sub menu with no page id
            if(is_numeric(strpos($this->menu_items_link,"https")) || is_numeric(strpos($this->menu_items_link,"http"))){
                return($this->menu_items_link . '"  target="_blank');
            }
            return($this->menu_items_link);
        }
        if($this->section_id){
            $section = section::find_np($this->section_id);
            if(!empty($section)){
                $section_name = mb_strtolower(section::find_np($this->section_id)->section_name);
            }
        }

        if($this->subsection_id > 0){
            $sub_section = sub_section::find_np($this->subsection_id);
            if(!empty($sub_section)){
                $sub_section_name = $sub_section->sub_section_name;
            }
        }


        if($this->subsection_id > 0  && !empty($this->sub_section)){
            $sub_section_name = mb_strtolower(sub_section::find_np($this->subsection_id)->sub_section_name);
        }

        if ( $this->page_id == -1 && $this->subsection_id > 0  && !empty($this->sub_section)){
            return  ThemeService::ConfigValue('APP_URL') .  str_replace(' ','-',$section_name) . '/'.  str_replace(' ','-',$sub_section_name);
        }elseif ( $this->page_id == -1 && $this->section_id > 0){
            return  ThemeService::ConfigValue('APP_URL') .  str_replace(' ','-',$section_name);
        }

        if ( $this->page_id == 0 ){
            return '';
        }

        return  !empty($this->page) ? UrlHelper::build_seo_url($this->page, 'page', $this->page->page_title) : ''; //for header menu
    }

    private static function order_key_index($id, $order){
        return ( $order > 0 ? 0 : 1000000000 ) + $order*1000000 + $id; // order with 0 to the end
    }

    public function getSectionColorAttribute(){
        if(!empty($this->section_id)){
            $section = section::find_np($this->section_id);
            if(!empty($section)){
                $section_color =  $section->section_color;
            }
        }
        return !empty($section_color) ? $section_color : '';
    }
}
