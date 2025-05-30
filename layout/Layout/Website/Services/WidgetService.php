<?php
/**
 * Created by PhpStorm.
 * User: timur
 * Date: 13.11.2018
 * Time: 12:12
 */

namespace Layout\Website\Services;

use App\Models\widget;
use Layout\Website\Models\WebsiteWidget;
use App\Http\Controllers\CommonController;

class WidgetService
{
    public static function widget_by_id($widget_id){
        $widget = widget::find_np($widget_id);
        return self::website_widget_instance($widget);
    }

	public static function widget_by_widget_data($widget){
        return self::website_widget_instance($widget);
    }

    public static function widget_by_parent_id($widget_parent_id){
        $widget = widget::parent_np($widget_parent_id);
		return $widget;
    }

    public static function widgets($widget_id_array){
        $widgets = widget::find_np($widget_id_array);
        $instances = [];
        foreach($widgets as $widget){
            $instances[] = self::website_widget_instance($widget);
        }
        return $instances;
    }

    public static function widget(widget $widget){
        return self::website_widget_instance($widget);
    }

    /**
     * @param widget $widget
     * @return WebsiteWidget
     */
    private static function website_widget_instance($widget){
        $widget_class_name = 'Themes\\'.ThemeService::Name().'\\widgets\\'.self::widget_class_name($widget->widget_style);

        if(class_exists($widget_class_name)){
            return new $widget_class_name($widget);
        }
        $widget_class_name = 'Themes\\'.ThemeService::DefaultName() .'\\widgets\\'.self::widget_class_name($widget->widget_style);


        if(class_exists($widget_class_name)){
            return new $widget_class_name($widget);
        }

        $widget_class_name = 'Themes\\'.ThemeService::Name().'\\widgets\\defaultWidget';
        if(class_exists($widget_class_name)){
            return new $widget_class_name($widget);
        }

        $widget_class_name = 'Themes\\'.ThemeService::DefaultName().'\\widgets\\defaultWidget';
        return new $widget_class_name($widget);
    }

    private static function widget_class_name($widget_style)
    {
        $funcName = str_replace(' ', '', ucwords(str_replace('_', ' ', $widget_style)));
        $funcName = lcfirst($funcName);

        return $funcName;
    }

    public static function widget_view_name($widget_style)
    {
        $viewName = str_replace('_', '-', $widget_style);

        return $viewName;
    }

    public static function PageWidgets($page_id){
        $widgets = widget::where('page_id',$page_id)->get();

        return $widgets;
    }

    public static function stackCss(){
        return array_values(self::css());
    }

    public static function pushCss($css){
        if(is_array($css)){
            foreach($css as $css_row){
                self::css($css_row);
            }
            return true;
        }
        return self::css($css);
    }

    private static function css($css=null){
        static $css_array = [];
        if(!empty($css)) {
            $hash_filename = md5($css);
            if(!isset($css_array[$hash_filename])){
                $css_array[$hash_filename] = $css;
            }
            return true;
        }

        return $css_array;
    }

    public static function stackJs(){
        return array_values(self::js());
    }

    public static function pushJs($js){
        if(is_array($js)){
            foreach($js as $js_row){
                self::js($js_row);
            }
            return true;
        }
        return self::js($js);
    }

    private static function js($js=null){
        static $js_array = [];
        if(!empty($js)) {
            $hash_filename = md5($js);
            if(!isset($js_array[$hash_filename])){
                $js_array[$hash_filename] = $js;
            }
            return true;
        }

        return $js_array;
    }

	public static function stackWidget(){
        return array_values(self::setWidget());
    }

    public static function pushWidget($widget_name){
        if(is_array($widget_name)){
            foreach($widget_name as $widget){
                self::setWidget($widget);
            }
            return true;
        }
        return self::setWidget($widget_name);
    }

    private static function setWidget($widget_name=null){
        static $widget_array = [];
        if(!empty($widget_name)) {
            $hash_filename = md5($widget_name);
            if(!isset($widget_array[$hash_filename])){
                $widget_array[$hash_filename] = $widget_name;
            }
            return true;
        }

        return $widget_array;
    }

}