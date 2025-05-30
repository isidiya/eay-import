<?php

/**
 * Created by PhpStorm.
 * User: timur
 * Date: 18.11.2018
 * Time: 21:24
 */

namespace Layout\Website\Models;

use App\Http\Controllers\CommonController;
use App\Models\bootstrap_rows;
use App\Models\page;
use Illuminate\Support\Facades\View;
use Layout\Website\Services\ThemeService;
use Layout\Website\Services\WidgetService;
use Layout\Website\Services\PageService;

class PageBootstrap {

    private $rows = [];

    public function __construct(page $page) {
        $page_bootstrap = bootstrap_rows::where('page_id', $page->np_page_id)->first();
        if ($page_bootstrap) {
            if (!empty($page_bootstrap->bootstrap_tags)) {
                $this->rows = json_decode($page_bootstrap->bootstrap_tags, true);
            }
        }
    }

    public function render() {
        $page = PageService::Page();
        $widgets = page::find_widgets_np($page->np_page_id);

        $bootstrap_rows = [];
        //Get Static articles id to temove from Articles Filter
        if (ThemeService::ConfigValue("SKIP_STATIC_ARTICLES") == 1) {
            static $static_articles_id = [];
            foreach ($this->rows as $row) {//
                foreach ($row as $column) {
                    $bootstrap_column = new \stdClass();
                    foreach ($column as $key => $widget_id) {
                        if (is_integer($key)) {
                            $widget = $widgets[$widget_id];
                            $widgetClasses = json_decode($widget->widget_data);
                            if ($widgetClasses->widget_type == "static_articles" &&  isset($widgetClasses->articles_ids) && isset($widgetClasses->articles_order) && isset($widgetClasses->number_of_articles) && !empty($widgetClasses->articles_order) && $widgetClasses->articles_order[0]) {
                                $i = 100;
                                if (!empty($widgetClasses->article_publish_date)) {
                                    $dates = json_decode($widgetClasses->article_publish_date, true);
                                }
                                foreach($widgetClasses->articles_order as $order){
                                       if($order < $i){
                                           $i = $order;
                                       }                             
                                }
                                if(isset($dates)){
                                    $article_limit = 0; //this var to check the number of articles set into static_articles_var
                                    for( $i;$i <= count($widgetClasses->articles_ids);$i++){
                                        foreach($widgetClasses->articles_order as $key => $order_num){
                                            $id = $widgetClasses->articles_ids[$key];
                                            if($order_num == $i && $article_limit < $widgetClasses->number_of_articles){ // stop at nb of articles
                                                if (isset($dates[$id]) && $dates[$id] <= date('Y-m-d H:i:s')) { // check for future dates
                                                    $static_articles_id[] = $id;
                                                    $article_limit++;
                                                }
                                            }
                                        }
                                    }
                                }else {
                                    $widgetClasses->number_of_articles += $i-1;
                                    for ($i; $i <= $widgetClasses->number_of_articles; $i++) {
                                        foreach ($widgetClasses->articles_order as $key => $order_num) {
                                            if ($order_num == $i) {
                                                $static_articles_id[] = $widgetClasses->articles_ids[$key];
                                            }
                                        }
                                    }
                                }
                            }elseif($widgetClasses->widget_type == "tabs"){
                                $children_widgets = \App\Models\widget::where('parent_widget_id', $widget->np_widget_id)->get();
                                foreach ($children_widgets as $key_child => $child_widget){
                                    $child_widget_data = json_decode($child_widget->widget_data);
                                    if ($child_widget_data->widget_type == "static_articles" &&  isset($child_widget_data->articles_ids) && isset($child_widget_data->articles_order) && isset($child_widget_data->number_of_articles) && !empty($child_widget_data->articles_order) && $child_widget_data->articles_order[0]) {
                                        $i = 100;
                                        if (!empty($child_widget_data->article_publish_date)) {
                                            $dates = json_decode($child_widget_data->article_publish_date, true);
                                        }
                                        foreach($child_widget_data->articles_order as $order){
                                               if($order < $i){
                                                   $i = $order;
                                               }                             
                                        }
                                        if(isset($dates)){
                                            $article_limit = 0; //this var to check the number of articles set into static_articles_var
                                            for( $i;$i <= count($child_widget_data->articles_ids);$i++){
                                                foreach($child_widget_data->articles_order as $key => $order_num){
                                                    $id = $child_widget_data->articles_ids[$key];
                                                    if($order_num == $i && $article_limit < $child_widget_data->number_of_articles){ // stop at nb of articles
                                                        if (isset($dates[$id]) && $dates[$id] <= date('Y-m-d H:i:s')) { // check for future dates
                                                            $static_articles_id[] = $id;
                                                            $article_limit++;
                                                        }
                                                    }
                                                }
                                            }
                                        }else {
                                            $child_widget_data->number_of_articles += $i-1;
                                            for ($i; $i <= $child_widget_data->number_of_articles; $i++) {
                                                foreach ($child_widget_data->articles_order as $key => $order_num) {
                                                    if ($order_num == $i) {
                                                        $static_articles_id[] = $child_widget_data->articles_ids[$key];
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                } 
            }
            $static_articles_id = array_unique($static_articles_id);
            PageService::SetStaticArticlesPage($static_articles_id);
        }
		$row_number= ThemeService::ConfigValue("ROW_NUMBER");
        foreach ($this->rows as $key_row => $row) {
            $bootstap_row = new \stdClass();
            $first_widget_id = (!empty($row[0]) && !empty($row[0][0])) ? $row[0][0] : 0;
            $bootstap_row->is_full_container = WebsiteWidget::is_full_container($first_widget_id);
            $bootstap_row->columns = [];
            $bootstap_row->is_container = false;
            $bootstap_row->fluid_classes = "";
            foreach ($row as $column) {
                $bootstrap_column = new \stdClass();
                $bootstrap_column->class = $column['class'];
                unset($column['class']); // $column array contains 0 to N index keys and a "class" key, getting rid of the "class" before the loop"

                if (isset($column['fluid'])) {
                    if ($column['fluid'] == 1 && $bootstap_row->is_full_container == 0) {
                        $bootstap_row->is_full_container = 1;
                    }
                    unset($column['fluid']);
                }

                foreach ($column as $key => $widget_id) {
                    if (is_integer($key)) {
                        $widget = $widgets[$widget_id];
                        $widgetClasses = json_decode($widget->widget_data);
                        if (isset($widgetClasses->css_classes)) {
                            $widgetClasses = $widgetClasses->css_classes;
                            $widgetClasses = explode(",", $widgetClasses);
                            foreach ($widgetClasses as $widgetClass) {
                                if (is_numeric(strpos(strtolower($widgetClass), "container"))) {
                                    $bootstap_row->is_container = true;
                                    if (is_numeric(strpos(strtolower($widgetClass), "[")) && is_numeric(strpos(strtolower($widgetClass), "]"))) {
                                        $bootstap_row->fluid_classes = str_replace(array("container[", "]"), array("", ""), $widgetClass);
                                    }
                                } else if(is_numeric(strpos(strtolower($widgetClass), "fluid["))) {
                                    $bootstap_row->fluid_classes = str_replace(array("fluid[", "]"), array("", ""), $widgetClass);
                                }
                            }
                        }
                    }
                }

                $bootstrap_column->widgets = [];
                foreach ($column as $key => $widget_id) {
                    if (is_integer($key)) {
						if(ThemeService::ConfigValue("ROW_NUMBER") && is_array(ThemeService::ConfigValue("AJAX_PAGES"))
						&& in_array($page->np_page_id, ThemeService::ConfigValue("AJAX_PAGES"))
						&& $key_row >= $row_number)
						{
							$bootstrap_column->widgets[] = "";
						}else{
							$bootstrap_column->widgets[] = WidgetService::widget_by_widget_data($widgets[$widget_id]);
						}
                    }
                }
//                $bootstrap_column->widgets = WidgetService::widgets($column);
                $bootstap_row->columns[] = $bootstrap_column;
            }
            $bootstrap_rows[] = $bootstap_row;
        }

        $special_classes = [];
        $page = PageService::Page();
        $theme_controller_class = 'Themes\\' . ThemeService::Name() . '\\controllers\\' . CommonController::controller_class_name();
        if (class_exists($theme_controller_class)) {
            $themeController = new $theme_controller_class();
            if (method_exists($themeController, 'getBootstrapSpecialClasses')) {
                $function_name = 'getBootstrapSpecialClasses';
                $special_classes = $themeController->$function_name($page);
            }
        }

        if (ThemeService::ConfigValue("WITH_CONTAINER")) {
            return View::make('theme::bootstrap.with_container', ['rows' => $bootstrap_rows, 'special_classes' => $special_classes])->render();
        }

        return View::make('theme::bootstrap.without_container', ['rows' => $bootstrap_rows])->render();
    }
}