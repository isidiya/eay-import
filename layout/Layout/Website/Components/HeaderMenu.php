<?php
/**
 * Created by PhpStorm.
 * User: timur
 * Date: 25.11.2018
 * Time: 21:11
 */

namespace Layout\Website\Components;


use Layout\Website\Models\WebsiteComponent;
use Layout\Website\Services\MenuService;
use Layout\Website\Services\ThemeService;
use App\Providers\AppServiceProvider;

class HeaderMenu extends WebsiteComponent
{
    protected $name = WebsiteComponent::header_menu;
    protected $cached_minutes = 5;

    public $header_menu;
    public $header_mobile_menu;
    public $page_id;
    public $section_info;
    public $sub_section_info;
    public $page_section_info;

    protected function handle(){
        $this->page_id = \Layout\Website\Services\PageService::PageID();
		if(ThemeService::ConfigValue("USE_JSON_FILES")){
			$this->header_menu = MenuService::Json_menu(ThemeService::ConfigValue('WEB_MENU_ID'));
			$this->header_mobile_menu = MenuService::Json_menu(ThemeService::ConfigValue('MOBILE_MAIN_MENU_ID'));//MOBILE_WEB_MENU_ID
		}else{
			$this->header_menu = MenuService::menu(ThemeService::ConfigValue('WEB_MENU_ID'));
		}
        $this->add_cache_key_variant($this->page_id);

		$this->section_info	= AppServiceProvider::$section_info;
		$this->sub_section_info	= AppServiceProvider::$sub_section_info;
		$this->page_section_info	= AppServiceProvider::$page_section_info;
    }
}