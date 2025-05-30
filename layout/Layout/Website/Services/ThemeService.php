<?php
/**
 * Created by PhpStorm.
 * User: timur
 * Date: 13.11.2018
 * Time: 12:12
 */

namespace Layout\Website\Services;


use App\Models\bootstrap_rows;
use App\Models\widget;

class ThemeService
{
    const default_theme = 'default_theme';
    const cms_theme = 'cmsonline';
    const views_folder = '/views';
    const config_folder = '/config';
    const public_folder = '/public';
    const routes_folder = '/routes';
    const mmdb_folder = '/database/mmdb';

    public static function Name(){
        return env('APP_THEME', self::default_theme);
    }

	//Get Theme name controller ex : FratmatController
	public static function ThemeController()
    {
        $funcName =  ucwords(str_replace('_', '', self::Name())).'Controller';
        return $funcName;
    }

    public static function DefaultName(){
        return self::default_theme;
    }

    public static function Path(){
        return base_path('Themes/'.self::Name());
    }

    public static function DefaultPath(){
        return base_path('Themes/'.self::default_theme);
    }

    public static function CmsPath(){
        return base_path('Themes/'.self::cms_theme);
    }

    public static function ViewPath(){
        return  self::Path().self::views_folder;
    }
	public static function PublicPath(){
        return  self::Path().self::public_folder;
    }
	public static function PublicMainPath(){
		return base_path(self::public_folder);
    }

	public static function MainMmdbPath(){
		return base_path(self::mmdb_folder);
    }

    public static function DefaultViewPath(){
        return self::DefaultPath().self::views_folder;
    }

    public static function CmsViewPath(){
        return self::CmsPath().self::views_folder;
    }

    public static function RoutesPath(){
        return  self::Path().self::routes_folder;
    }

    public static function RoutesNamespace(){
        return  'Themes\\'.self::Name().'\\controllers';
    }

    public static function Config($config_type){ //("." seperated path as $name)
        //caches the value for the lifetime of the request so once we set the config, the file will not be included again
        static $configs = [];
        if(!isset($configs[$config_type])){
            $config_type_array = explode('.', $config_type);
            $path = '';
            if(is_array($config_type_array)){
                foreach($config_type_array as $node) {
                    $path .= '/' . $node;
                }
            }else{
                $path = $config_type;
            }

            $path.=".php";
            //included file contains a return
            if(!file_exists(self::Path().self::config_folder.$path)){
                return [];
            }

            $configs[$config_type] = include(self::Path().self::config_folder.$path);
        }

        return $configs[$config_type];
    }

    public static function ConfigCMS($config_type){ //("." seperated path as $name)
        //caches the value for the lifetime of the request so once we set the config, the file will not be included again
        static $configs = [];
        if(!isset($configs[$config_type])){
            $config_type_array = explode('.', $config_type);
            $path = '';
            if(is_array($config_type_array)){
                foreach($config_type_array as $node) {
                    $path .= '/' . $node;
                }
            }else{
                $path = $config_type;
            }

            $path.=".php";
            //included file contains a return
            if(!file_exists(self::CmsPath().self::config_folder.$path)){
                return [];
            }

            $configs[$config_type] = include(self::CmsPath().self::config_folder.$path);
        }

        return $configs[$config_type];
    }
    
    public static function ConfigValue($name, $default_value = null, $config_type = ''){
//        first case to get only the specified config value from its file
        $config_type_file= self::Path(). self::config_folder .'/'. $config_type. '.php';
        if($config_type != '' && file_exists($config_type_file)){
            return self::ConfigTypeValue($name, $default_value, $config_type);
        }
//        if $config_type is empty then the default value is theme
        $config_type = 'theme';
//        if $config_type is not specified, check if my_theme.php exists. If my_theme.php exists get the value from it, else get the value from theme.php
        $config_type_file= self::Path(). self::config_folder .'/my_theme.php';
        if (file_exists($config_type_file)) {
          $return_value = self::ConfigTypeValue($name, $default_value, 'my_theme');
//         my_theme.php defines should override theme.php defines. But if define does not exist in my_theme.php then check it  in from theme.php
          if(!is_null($return_value)){
              return $return_value;
          }else{
              return self::ConfigTypeValue($name, $default_value);
          }
        }else{
             return self::ConfigTypeValue($name, $default_value);
        }
    }

    public static function ConfigTypeValue($name, $default_value = null,$config_type='theme'){ //("." seperated path as $name)
        //Environment variables overwrite the config values.
        $env_value  = env($name, null);
        if($env_value!==null){
            return $env_value;
        }
        
        
        $config = self::Config($config_type);
        $config_name_array = explode('.', $name);
        $value = null;
        
        if(is_array($config_name_array)){
            $value = $config;
            foreach($config_name_array as $node){
                if(!isset($value[$node])){
                    $value =  $default_value;
                    break;
                }
                
                $value = $value[$node];
            }
        }else{
            if(isset($config[$name])){
                $value = $config[$name];
            }
        }
       
        
        if(!isset($value)){
            
            $config = self::ConfigCMS($config_type);
            $config_name_array = explode('.', $name);
            
            if(is_array($config_name_array)){
                $value = $config;
                foreach($config_name_array as $node){
                    if(!isset($value[$node])){
                        return $default_value;
                    }
                    $value = $value[$node];
                }
            }else{
                if(isset($config[$name])){
                    $value = $config[$name];
                }
            }
        }
        
        
        
        return $value;
        
    }

    public static function WidgetViewFullName($widget_name){
        return "theme::widgets.".$widget_name;
    }

	public static function CheckThemeFunction($function_name){

		$exist = false;
		//Here we check if we have a function getPostArticlePreview article in Theme controller
		if(!empty($function_name)){
			$theme_controller_class = 'Themes\\'.self::Name().'\\controllers\\'.self::ThemeController();
			if(class_exists($theme_controller_class)){
				$themeController = new $theme_controller_class();
				if(method_exists($themeController, $function_name)) {
					$exist = $themeController ;
				}
			}
		}

		return $exist;
	}
}