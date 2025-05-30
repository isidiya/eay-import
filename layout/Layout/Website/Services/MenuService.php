<?php
/**
 * Created by PhpStorm.
 * User: timur
 * Date: 13.11.2018
 * Time: 12:12
 */

namespace Layout\Website\Services;


use App\Models\menu;
use App\Models\menu_item;
use App\Models\section;
use App\Models\article;
use Illuminate\Support\Facades\Cache;
use Layout\Website\Helpers\UrlHelper;

class MenuService
{

	public static function cacheMenu($menu_id, $page_id=0,$sub_menu=0,$section_color=0,$articles=0,$limit=2, $cahced_minutes = 20){
        //caches the value for the lifetime of the request so once we set the page_id in the controller we would be able to access it with this service elsewhere
        $menu = Cache::remember("cacheMenu_".$menu_id."_".$page_id."_".$sub_menu, $cahced_minutes, function ()use ($menu_id, $page_id,$sub_menu,$section_color,$articles,$limit) {
			$menu_tmp = MenuService::menu($menu_id,$page_id,$sub_menu,$section_color,$articles,$limit);
                return $menu_tmp;
			});
		return isset($menu) ? $menu :"";
    }

    public static function menu($menu_id, $page_id=0,$sub_menu=0,$section_color=0,$articles=0,$limit=2){ // method name is the components name
        $menu_items = menu_item::find_by_menu_id($menu_id);
        
        if($section_color){
			foreach ($menu_items as $menu_items_list) { 
                $menu_section = $menu_items_list->section;
                 $menu_items_list['section_color'] = '';
                if(isset($menu_section->section_color)){
                    $menu_items_list['section_color'] = self::section_color($menu_section->section_color);
                }  
				
			}
		}
        
        if($articles){
			foreach ($menu_items as $menu_items_list) { 
				$articles_menu = self::articles_menu($menu_items_list['section_id'],$limit);
				$menu_items_list['articles_menu'] = $articles_menu;
			}
		}

		if($sub_menu){
			foreach ($menu_items as $menu_items_list) {
				$sub_menu_array = MenuService::sub_menu($menu_items_list['np_menu_items_id']);
				$menu_items_list['sub_menu_array'] = $sub_menu_array;
			}
		}
        
        return $menu_items;
    }

    public static function sub_menu($menu_id){ // method name is the components name
        $menu_items = menu_item::find_by_parent_id($menu_id);
        return $menu_items;
    }
    
    public static function section_color($section_color){ // method name is the components name
        if(isset($section_color)){ 
            if(strpos($section_color,'#') !== false){
                return $section_color;
            } else {
               return '#'.$section_color;
            }  
        }else{
            return '';
        }
    }
    
    public static function articles_menu($section_id,$limit){ // method name is the components name
        $articles_menu = article::get_articles_menu($section_id,$limit); 
        return $articles_menu;
    }

	public static function Json_menu($menu_id){ // method name is the components name
		return AmazonService::get_menu($menu_id);
    }
}