<?php
/**
 * Created by PhpStorm.
 * User: timur
 * Date: 13.11.2018
 * Time: 12:30
 */

namespace Layout\Website\Models;

use App\Models\widget;
use Exception;
use Illuminate\Support\Facades\View;
use Layout\Website\Helpers\UrlHelper;
use Layout\Website\Services\ThemeService;
use Layout\Website\Services\WidgetDataService;
use Layout\Website\Services\WidgetService;
use App\Http\Controllers\CommonController;
use Illuminate\Support\Facades\Cache;

class WebsiteWidget
{
    protected $view="";
    protected $css=[];
    protected $js=[];
    protected $limit=20;
    protected $enable_widget_pagination = false;
    protected $skip_article_without_image = false;
    protected $home_page_article_flag= false; // if it is 1, then article will show in homepage
    protected $count_articles_flag = false;
    protected $offset=0;

    protected $widget;
    protected $widget_data;
    protected $widget_data_source_type;

    public $active_tabs;
    public $id;
    public $title;
    public $link;
    public $html='';

    public $view_data = [];

    public function __construct(widget $widget)
    {
        $this->execute_widget_initial();
        $this->widget = $widget;
        $this->widget_data = $this->GetWidgetData();
        $this->id = $this->widget->cms_widget_id;
        $this->np_widget_id = $this->widget->np_widget_id;
        //The view of the widget can be overwritten in widget data
        $this->_setView();

        //follow the number of articles in the widget if exist
        if(!empty($this->widget_data->number_of_articles)) {
            $this->limit = $this->widget_data->number_of_articles;
        }

        if((!empty($this->widget_data->start)) && is_numeric($this->widget_data->start)) {
            $this->offset = $this->widget_data->start - 1;
        }

        $this->widget_data_source_type = $this->widget->widget_type;
		if(ThemeService::ConfigValue("USE_JSON_FILES")){
			$widget_data_source = new WidgetDataService($this);
			if($this->widget_data->widget_type == 'wysiwyg'){
				$this->html =html_entity_decode($this->widget_data->wysiwyg_text);
			}
			$this->view_data =$this->np_widget_id;

			$this->title = self::_getWidgetTitle($this->widget_data->name, $this->widget_data->titleRadio, $this->view_data);
		}else{
			$widget_data_source = new WidgetDataService($this);

			$this->view_data = $widget_data_source->fetch();
            
            $widgetClasses= json_decode($widget->widget_data);
            if(isset($widgetClasses->css_classes)){
                $this->widgetClasses = $widgetClasses->css_classes;
            }

			$titleRadio = isset($this->widget_data->titleRadio) ? $this->widget_data->titleRadio : 0;
            $widget_data_name = isset($this->widget_data->name) ? $this->widget_data->name : '';
            
			$this->title = self::_getWidgetTitle($widget_data_name, $titleRadio, $this->view_data);
			$this->link = !empty($this->title_link()) ? $this->title_link() :   $this->morein_link();
		}


        WidgetService::pushCss($this->css);
        WidgetService::pushJs($this->js);

    }

    public function render(){
        $this->execute_widget_handle();

		try {
			if(isset($this->cached_minutes) && $this->cached_minutes > 0) {

                $theme_controller_class = 'Themes\\' . ThemeService::Name() . '\\controllers\\' . CommonController::controller_class_name();
                if (class_exists($theme_controller_class)) {
                    $themeController = new $theme_controller_class();
                    if (method_exists($themeController, 'renderCacheRemember')) {
                        $function_name = 'renderCacheRemember';
                        $cache_remember_name = $themeController->$function_name($this->view,$this->np_widget_id,$this->extra_param);
                        return Cache::remember($cache_remember_name, $this->cached_minutes, function () {
                            return View::make('theme::widgets.' . $this->view, $this->to_array())
                                ->render();
                        });
                    }
                }

				return Cache::remember($this->view."_".$this->np_widget_id, $this->cached_minutes, function () {
					return View::make('theme::widgets.' . $this->view, $this->to_array())
						   ->render();
				});
			}
            return View::make('theme::widgets.' . $this->view, $this->to_array())
                       ->render();
        }
        catch (Exception $e) {
            return View::make('theme::widgets.default-widget', $this->to_array())
                       ->render();
        }
    }

    public function handleRender(){
        return $this->execute_widget_handle();
    }

    public function GetWidget(){
        return $this->widget;
    }

    public function GetWidgetData(){
        return !empty($this->widget->widget_data) ? json_decode($this->widget->widget_data) : [];
    }

    public function GetWidgetDataValue($key){
        return !empty($this->widget_data->{$key} ) ? $this->widget_data->{$key} : [];
    }

    public function GetWidgetDataSourceType(){
        return $this->widget_data_source_type;
    }

    public function SetWidgetDataSourceType($type){
        $this->widget_data_source_type = $type;
    }

    public function GetLimit(){
        return $this->limit;
    }

    public function GetOffset(){
        return $this->offset;
    }

    public function enable_widget_pagination(){
        return $this->enable_widget_pagination;
    }

    public function skip_article_without_image(){
        return $this->skip_article_without_image;
    }

    public function homepage_article_flag(){
        return $this->home_page_article_flag;
    }

    public function count_articles_flag(){
        return $this->count_articles_flag;
    }

    public static function is_full_container($widget_id){
        $full_container_widgets = ThemeService::ConfigValue("WIDGET_FULL_CONTAINER");

        if($full_container_widgets){
            $full_container_widgets_array = array_filter(explode(",", $full_container_widgets));
            return in_array($widget_id, $full_container_widgets_array);

        }

        return false;
    }

    public function title_link(){
        return !empty($this->widget_data->link_to_page) ? ( UrlHelper::build_seo_url($this->widget_data->link_to_page, 'page','',!empty($this->widget_data->section) ? $this->widget_data->section : 0,!empty($this->widget_data->cms_section_subsection) ? $this->widget_data->cms_section_subsection : 0 ) ): '';
    }

    public function morein_link(){

        $more_in_ids_array['section_id'] = !empty($this->widget_data->section) ? $this->widget_data->section : '';
        $more_in_ids_array['sub_section_id'] = !empty($this->widget_data->cms_section_subsection) ? $this->widget_data->cms_section_subsection : '';

        return !$more_in_ids_array['section_id'] ? $this->title_link() : UrlHelper::build_seo_url(1, 'morein', '', $more_in_ids_array['section_id'], $more_in_ids_array['sub_section_id']);
    }

    protected function _setView(){
        if(!empty($this->widget_data->style)){
            $this->view = $this->widget_data->style;
        }elseif(empty($this->view)){
            $this->view = WidgetService::widget_view_name($this->widget->widget_style);
        }
    }

    protected function execute_widget_handle(){
        if(method_exists($this, 'handle')) {
            $widgets_view_data = $this->handle();
            if(!empty($widgets_view_data) && is_array($widgets_view_data)) {
                foreach ($widgets_view_data as $key => $value) {
                    $this->view_data->{$key} = $value;
                }
            }
        }
    }
    protected function execute_widget_initial(){
        if(method_exists($this, 'initial')) {
            $widgets_view_data = $this->initial();
            if(!empty($widgets_view_data) && is_array($widgets_view_data)) {
                foreach ($widgets_view_data as $key => $value) {
                    $this->view_data->{$key} = $value;
                }
            }
        }
    }

    private static function _getWidgetTitle($name, $title_radio = 0, $view_data)
    {
        switch ($title_radio) {
            case 2:
                if(!empty($view_data->articles) && $view_data->articles->first()){
                    $title = $view_data->articles->first()->section_name;
                }else{
                    $title='';
                }
                break;
            case 3:
                $title = '';
                break;
            default:
                $title = $name; // Default value, or if 1
                break;
        }

        return $title;
    }

    protected function to_array(){
        return call_user_func('get_object_vars', $this);
    }
}
