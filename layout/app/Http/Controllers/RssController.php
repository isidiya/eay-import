<?php

namespace App\Http\Controllers;

use App\Models\article_multi_section_archive;
use App\Models\section;
use App\Models\sub_section;
use App\Models\article;
use App\Models\image;
use App\Models\page;
use App\Models\bootstrap_rows;
use App\Models\article_archive;
use Layout\Website\Services\MenuService;
use Layout\Website\Services\ThemeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\CommonController;
use Illuminate\Support\Facades\Cookie;
use \Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use App\Models\article_multi_section;

class RssController extends Controller
{

    public function rssFeed(Request $request)
    {   
        $theme_controller_class = 'Themes\\'.ThemeService::Name().'\\controllers\\'.  CommonController::controller_class_name();
        if(class_exists($theme_controller_class)){
            $themeController = new $theme_controller_class();
            if(method_exists($themeController, 'rssFeed')) {
                $function_name ='rssFeed';
                return $themeController->$function_name($request);
            }
        }
        
        $view_name='rssFeed';
        if($request->route()->named('newsRssFeed')){
            $view_name='newsRssFeed';
        }
		if(isset($request->section_name) && strtolower($request->section_name) == 'home-page'){
			$page = page::where("is_home_page", 1)->first();
			$subSection='';
			$section='0';
			$articles=self::getArticleFromPage($page);
		}else{
			if(ThemeService::ConfigValue('MULTI_COUNTRIES') && empty($article_authors)) {
				if(isset($request->sub_section_name)){
					$section = is_numeric($request->section_name) ?  section::find_np($request->section_name) :  section::find_np_by_name($request->section_name);
					$subSection = is_numeric($request->sub_section_name) ?  sub_section::find_np($request->sub_section_name) :  sub_section::find_np_by_name($request->sub_section_name);

					$articles  = article::rssArticlesCache($section,$subSection, 1);

				}elseif(isset($request->section_name) && !empty($request->section_name)) {
					$section = is_numeric($request->section_name) ?  section::find_np($request->section_name) :  section::find_np_by_name($request->section_name);
					$subSection = is_numeric($request->sub_section_name) ?  sub_section::find_np($request->sub_section_name) :  sub_section::find_np_by_name($request->sub_section_name);
					$articles  = article::rssArticlesCache($section,$subSection, 1);
				}else{
					$subSection='';
					$section='0';
					$articles = article::where('publish_time', '<', DB::raw(ThemeService::ConfigValue('GIVEN_TIME')))->orderBy('publish_time', 'DESC')->limit(50)->get();
				}
			}else{
				if(isset($request->sub_section_name)){
					$section = is_numeric($request->section_name) ?  section::find_np($request->section_name) :  section::find_np_by_name($request->section_name);
					$subSection = is_numeric($request->sub_section_name) ?  sub_section::find_np($request->sub_section_name) :  sub_section::find_np_by_name($request->sub_section_name);
					$articles = article::where('sub_section_id', $subSection->np_sub_section_id)->where('publish_time', '<', DB::raw(ThemeService::ConfigValue('GIVEN_TIME')))->orderBy('publish_time', 'DESC')->limit(50)->get();
				}elseif(isset($request->section_name) && !empty($request->section_name)) {
					$section = is_numeric($request->section_name) ?  section::find_np($request->section_name) :  section::find_np_by_name($request->section_name);
					$subSection='';
					$articles = article::where('section_id', $section->np_section_id)->where('publish_time', '<', DB::raw(ThemeService::ConfigValue('GIVEN_TIME')))->orderBy('publish_time', 'DESC')->limit(50)->get();
				}else{
                    $limitarticles= ThemeService::ConfigValue('LIMIT_RSS_LATEST_ARTICLES') ? ThemeService::ConfigValue('LIMIT_RSS_LATEST_ARTICLES') : 50 ;
					$subSection='';
					$section='0';
					$articles = article::where('publish_time', '<', DB::raw(ThemeService::ConfigValue('GIVEN_TIME')))->orderBy('publish_time', 'DESC')->limit($limitarticles)->get();
				}
			}
		}
        $content =  view('theme::pages.'. $view_name, ['articles'=>$articles,'section'=>$section,'subSection'=>$subSection])->render();
		$response = Response::make($content);
		$response->header('Content-Type', 'text/xml');
		return $response;

    }

    public function rssIa(Request $request)
    {   
        $theme_controller_class = 'Themes\\'.ThemeService::Name().'\\controllers\\'.  CommonController::controller_class_name();
        if(class_exists($theme_controller_class)){
            $themeController = new $theme_controller_class();
            if(method_exists($themeController, 'rssIa')) {
                $function_name ='rssIa';
                return $themeController->$function_name($request);
            }
        }
        
        $articles_query = article::where('publish_time', '<', DB::raw(ThemeService::ConfigValue('GIVEN_TIME')));
        $articles_query = $articles_query->orderBy('publish_time', 'DESC');
        $articles_query = $articles_query->limit(100);
        $articles = $articles_query->get();

		$content = view('theme::pages.rssIa', ['articles'=>$articles])->render();
        $response = Response::make($content);
		$response->header('Content-Type', 'text/xml');
		return $response;
    }


	public function ampArticle(Request $request)
    {
        $theme_controller_class = 'Themes\\'.ThemeService::Name().'\\controllers\\'.  CommonController::controller_class_name();
        if(class_exists($theme_controller_class)){
            $themeController = new $theme_controller_class();
            if(method_exists($themeController, 'ampArticle')) {
                $function_name ='ampArticle';
                return $themeController->$function_name($request);
            }
        }

		$article = article::where('cms_article_id', $request->cms_article_id)->first();
        if(empty($article) && ThemeService::ConfigValue('ARCHIVE_CUTOFF') > 0 ){
            $article = article_archive::where('cms_article_id', $request->cms_article_id)->first();
        }
        return view('theme::pages.ampArticle', ['article'=>$article]);

    }

	public static function getArticleFromPage($page){
		$widgets = page::find_widgets_np($page->np_page_id);

		$page_bootstrap = bootstrap_rows::where('page_id', $page->np_page_id)->first();
        if($page_bootstrap){
            if(!empty($page_bootstrap->bootstrap_tags)){
                $rows = json_decode($page_bootstrap->bootstrap_tags, true);
            }
        }

        $articles = [];
		//Get Static articles id to temove from Articles Filter
		if( ThemeService::ConfigValue("SKIP_STATIC_ARTICLES") == 1){
			static $static_articles_id = [];
			foreach($rows as $row){//
				foreach($row as $column){
					$bootstrap_column = new \stdClass();
					foreach($column as $key => $widget_id){
						if(is_integer($key)){
							$widget = $widgets[$widget_id];
							$widgetClasses= json_decode($widget->widget_data);
							if(isset($widgetClasses->articles_ids)){
								foreach($widgetClasses->articles_ids as $article_id){
									$static_articles_id[] = $article_id;
								}
							}
						}

					}
				}
			}
			$static_articles_id = array_unique($static_articles_id);
			\Layout\Website\Services\PageService::SetStaticArticlesPage($static_articles_id);
		}
		$array_data = [];
        foreach($rows as $row){
            $bootstap_row = new \stdClass();
            $first_widget_id = (!empty($row[0]) && !empty($row[0][0])) ? $row[0][0] : 0;
            $bootstap_row->is_full_container = \Layout\Website\Models\WebsiteWidget::is_full_container($first_widget_id);
            $bootstap_row->columns = [];
			$bootstap_row->is_container = false;
			$bootstap_row->fluid_classes = "";
            foreach($row as $column){
                $bootstrap_column = new \stdClass();
                $bootstrap_column->class = $column['class'];
                unset($column['class']); // $column array contains 0 to N index keys and a "class" key, getting rid of the "class" before the loop"

				if(isset($column['fluid']) ){
					if($column['fluid'] == 1 && $bootstap_row->is_full_container == 0 ){
						$bootstap_row->is_full_container		= 1;
					}
					unset($column['fluid']);
				}

				foreach($column as $key => $widget_id){
					if(is_integer($key)){
						$widget = $widgets[$widget_id];
						$widgetClasses= json_decode($widget->widget_data);
						if(isset($widgetClasses->css_classes)){
							$widgetClasses = $widgetClasses->css_classes;
							$widgetClasses = explode( ",",$widgetClasses);
							foreach ($widgetClasses as $widgetClass){
								if(is_numeric(strpos(strtolower($widgetClass), "container"))){
									$bootstap_row->is_container =true;
									if(is_numeric(strpos(strtolower($widgetClass), "[")) && is_numeric(strpos(strtolower($widgetClass), "]"))){
										$bootstap_row->fluid_classes = str_replace(array("container[","]"), array("",""), $widgetClass);
									}
								}
							}
						}
					}
				}

                foreach($column as $key => $widget_id){
					if(is_integer($key)){
						$array_data_x =	\Layout\Website\Services\WidgetService::widget_by_widget_data($widgets[$widget_id]);

						if ( is_object( $array_data_x ) ) {
							$array_data[]	= json_decode( json_encode($array_data_x), true );
						}


					}
                }

            }
        }

		$data[] = self::array_value_recursive('cms_article_id',$array_data);

		$articles_ids = [];
		if(!empty($data[0])){
			foreach ($data[0] as $article_id ){
				if(is_integer($article_id)){
					$articles_ids[] =$article_id;
				}
			}
		}
		$articles_ids = array_unique($articles_ids);

		$articles = article::whereIn("cms_article_id" ,$articles_ids)->orderBy('max_publish_time', 'DESC')->get();
		return $articles;
	}


	public static function array_value_recursive($key, array $arr){

		$val = array();
		array_walk_recursive($arr, function($v, $k) use($key, &$val){

			if($k == $key)
			{
				array_push($val, $v);
			}
		});
		return count($val) > 1 ? $val : array_pop($val);
	}
    
    public function rssCustomFeed(Request $request)
    {
        $view_name='rssCustomFeed'; 
       
        if(!empty($request->input('date'))){
            $date=$request->input('date');
        } else {
            $date=date("Y-m-d");
        } 
        
        $date_from=date($date.' 00:00:00');
        $date_to=date($date.' 23:59:59'); 
		  
        $articles = article::whereBetween('publish_time',[$date_from, $date_to])->orderBy('publish_time', 'DESC')->get();
        
        foreach ($articles as $article){
            $all_images=image::where('np_related_article_id', $article->np_article_id)
                    ->where('image_is_deleted', 0)
                    ->orderBy('media_order', 'asc')
                    ->orderBy('np_image_id', 'desc')
                    ->get(); 
            $article['allimages']=$all_images;
        } 
        
        $content =  view('theme::pages.'. $view_name, ['articles'=>$articles,'date'=>$date])->render();
		$response = Response::make($content);
		$response->header('Content-Type', 'text/xml');
		return $response;

    }




}
?>
