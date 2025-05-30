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

class FooterMenu extends WebsiteComponent
{
    protected $name = WebsiteComponent::footer_menu;
    protected $cached_minutes = 60*24;

    public $footer_menu;
    public $footer_mobile_menu;


    protected function handle(){
		if(ThemeService::ConfigValue("USE_JSON_FILES")){
			$this->footer_menu = MenuService::Json_menu(ThemeService::ConfigValue('WEB_FOOTER_ID'));
            $this->footer_mobile_menu = MenuService::Json_menu(ThemeService::ConfigValue('MOBILE_FOOTER_MENU_ID'));
		}else{
			$this->footer_menu = MenuService::menu(ThemeService::ConfigValue('WEB_FOOTER_ID'));
		}
    }
}

