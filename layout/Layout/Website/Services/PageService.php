<?php
/**
 * Created by PhpStorm.
 * User: timur
 * Date: 13.11.2018
 * Time: 12:12
 */

namespace Layout\Website\Services;


use App\Http\Controllers\CommonController;
use App\Models\article;
use App\Models\article_archive;
use App\Models\page;
use App\Models\widget;
use Illuminate\Support\Facades\View;

class PageService
{
    const page_type_index = 'index';
    const page_type_article = 'article';
    const page_type_static = 'static';

    public static function PageType(){
        if(self::Article()){
            return self::page_type_article;
        }
        if(self::ArticleArchive()){
            return self::page_type_article;
        }

        if(!empty(self::PageID())){
            return self::page_type_index;
        }

        return self::page_type_static;
    }

    /**
     * @param $page_name
     * @return \App\Models\page
     */
    public static function PageByName($page_name='',$request = null){

        if(is_numeric($page_name) && $page_name > 0 && !isset($request->var1)){
            $page = page::where("np_page_id",$page_name)->first();
            if(empty($page)){
                $article = article::find($page_name);
                if(empty($article)){
                    $article = article_archive::find($page_name);
                }
                if(!empty($article)){
                    if (ThemeService::ConfigValue('SPECIAL_URL')) {
                        $url ='/' . $article->permalink;
                    }else{
                        $url ='/article/' . $article->cms_article_id;
                    }
                    return redirect()->to($url, 301)->send();
                }
            }
        }elseif($page_name){
            $page = self::PageByTitle($page_name);
		}elseif((!empty(ThemeService::ConfigValue('HOME_PAGE_ID')))){
			$page = page::where("np_page_id",ThemeService::ConfigValue('HOME_PAGE_ID'))->first();
			$page->is_home_page = 1;
        }else{
			if(is_numeric($page_name) && $page_name == 0){
				header("HTTP/1.0 404 Not Found");
				echo view('theme::errors.404');
				exit;
			}
            $page = page::where("is_home_page", 1)->first();
        }

        if(!$page){
			header("HTTP/1.0 404 Not Found");
            echo view('theme::errors.404');
			exit;
        }

        return $page;
    }
    public static function getHomePage($page_name=''){

		$page = page::where("is_home_page",1)->first();

        if(!$page){
            return abort(404);
        }

        return $page;
    }

	public static function Json_PageByName($page_name){
		$pages_data= AmazonService::get_pages();

		if(is_numeric($page_name) && $page_name > 0){
			foreach ($pages_data as $key => $page_data){
				if($key == $page_name){
					$page = $page_data;
				}
			}
		}elseif($page_name){
            $page_name = strtolower(str_replace("-"," ", $page_name));
			foreach ($pages_data as $page_data){
				if($page_name == strtolower($page_data['pageTitle'])){
					$page = $page_data;
				}
			}
		}else{
			foreach ($pages_data as $page_data){
				if($page_data['isHomePage'] == 1){
					$page = $page_data;
				}
			}
		}
		if(empty($page)){
            return abort(404);
        }
        $page = AmazonService::get_page($page['npPageId']);
        return $page;
    }

	public static function Page_JsonBootstrap($page_data){
		$page = $page_data['page'];
		$bootstrap_data = json_decode($page_data['bootstrap_data'],true);

		$widgets								= $page_data['widgets'];
		$bootstrap_rows							= [];
        foreach($bootstrap_data as $row){
            $bootstap_row = new \stdClass();
            $first_widget_id					= (!empty($row[0]) && !empty($row[0][0])) ? $row[0][0] : 0;
            $bootstap_row->is_full_container	= 0;
			$bootstap_row->is_container = false;
			$bootstap_row->fluid_classes = "";
            $bootstap_row->columns				= [];
            foreach($row as $column){
                $bootstrap_column				= new \stdClass();
                $bootstrap_column->class		= $column['class'];
                unset($column['class']); // $column array contains 0 to N index keys and a "class" key, getting rid of the "class" before the loop"

				if(isset($column['fluid']) ){
					if($column['fluid'] == 1 && $bootstap_row->is_full_container == 0 ){
						$bootstap_row->is_full_container		= 1;
					}
					unset($column['fluid']);
				}

                $bootstrap_column->widgets		= [];
				foreach($column as $widget_id){
					$widget						= new widget();
					$widget->np_widget_id		= $widget_id;
					$widget->widget_type		= $widgets[$widget_id]['widgetType'];
					$widget->widget_data		= $widgets[$widget_id]['widgetData'];
					$widget->widget_style		= $widgets[$widget_id]['widgetStyle'];
					$widget->parent_widget_id	= $widgets[$widget_id]['parentWidgetId'];
					$widget->cms_widget_id		= $widgets[$widget_id]['cmsWidgetId'];


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

				    $bootstrap_column->widgets[] = WidgetService::widget($widget);
				}
                $bootstap_row->columns[] = $bootstrap_column;
            }

            $bootstrap_rows[] = $bootstap_row;
        }

		return View::make('theme::bootstrap.with_container', ['rows'=>$bootstrap_rows])->render();
        //return $bootstrap_data;
    }

    /**
     * @param $page_name
     * @return \App\Models\page
     */
    public static function PageByTitle($page_title){
        $page_title =  str_replace("-"," ", $page_title);
        $page = page::where("page_title",$page_title)->first();

        $theme_controller_class = 'Themes\\'.ThemeService::Name().'\\controllers\\'.ThemeService::ThemeController();
        if(class_exists($theme_controller_class)){
            $themeController = new $theme_controller_class();
            if(method_exists($themeController, 'getDynamicPage')) {
                $page = $themeController->getDynamicPage($page,$page_title);
            }
        }

        if(!$page){
            $function = ThemeService::ConfigValue("PAGETITLE_ARCHIVE_FUNCTION");
            if($function){
                $data = ThemeService::CheckThemeFunction($function)->$function(Request());
                if($data){
                   echo $data;
                   exit;
                }
            }
            
            view()->share('body_class', 'errorPage'); 
            header("HTTP/1.0 404 Not Found");
            $theme_controller_class = 'Themes\\'.ThemeService::Name().'\\controllers\\'.ThemeService::ThemeController();
            if(class_exists($theme_controller_class)){
                $themeController = new $theme_controller_class();
                if(method_exists($themeController, 'getErrorPage')) {
                    $themeController->getErrorPage();
                }
            }
            echo view('theme::errors.404');
            exit;
        }

        return $page;
    }

    public static function SetPage(page $page){
        self::Page($page);

        return true;
    }

    public static function SetStaticPage($page_title){
        $page = new page();
        $page->page_title = $page_title;
        self::Page($page);

        return true;
    }

	public static function SetStaticArticlesPage($static_articles_id){
        $page = self::Page();
        $page->static_articles_id = $static_articles_id;
        return true;
    }

    /**
     * @param \App\Models\page|null $page_model
     * @return \App\Models\page|null
     */
    public static function Page(page $page_model = null){
        //caches the value for the lifetime of the request so once we set the page_id in the controller we would be able to access it with this service elsewhere
        static $page = null;
        if(is_null($page) || !is_null($page_model)){
            $page = $page_model;
        }

        return !is_null($page) ? $page : new page();
    }

    public static function PageID(){
        return self::Page()->np_page_id;
    }

    public static function SetArticle(article $article_model){
        self::Article($article_model);
        return true;
    }
    public static function SetArticleArchive(article_archive $article_archive_model){
        self::ArticleArchive($article_archive_model);
        return true;
    }

    /**
     * @param \App\Models\article|null $article_model
     * @return \App\Models\article|null
     */
    public static function Article(article $article_model = null){
        //caches the value for the lifetime of the request so once we set the page_id in the controller we would be able to access it with this service elsewhere
        static $article = null;
        if(is_null($article)){
            $article = $article_model;
        }

        return $article;
    }

    /**
     * @param \App\Models\article_archive|null $article_archive_model
     * @return \App\Models\article_archive|null
     */
    public static function ArticleArchive(article_archive $article_archive_model = null){
        //caches the value for the lifetime of the request so once we set the page_id in the controller we would be able to access it with this service elsewhere
        static $article_archive = null;
        if(is_null($article_archive)){
            $article_archive = $article_archive_model;
        }

        return $article_archive;
    }

    public static function SetMessage($message_text){
        self::Message($message_text);
        return true;
    }

    /**
     * @param string $message_text
     * @return string
     */
    public static function Message($message_text=''){
        //caches the value for the lifetime of the request so once we set the page_id in the controller we would be able to access it with this service elsewhere
        static $message = '';
        if(empty($message) && !empty($message_text)){
            $message = $message_text;
        }

        return $message;
    }

	public static function getExtraScripts(){
        //caches the value for the lifetime of the request so once we set the page_id in the controller we would be able to access it with this service elsewhere
        static $extrascripts = null;
        if(is_null($extrascripts)){
            $extrascripts = \App\Models\cms_extra_scripts::find_extra_script();
        }

        return $extrascripts;
    }

}