<?php

namespace App\Http\Controllers;

use App\Http\Controllers\CommonController;
use App\Models\article;
use App\Models\article_archive;
use App\Models\section;
use App\Models\ads_header_display;
use App\Models\sub_section;
use App\Models\web_data_values;
use Illuminate\Http\Request;
use Layout\Website\Models\PageBootstrap;
use Layout\Website\Services\ThemeService;
use Layout\Website\Services\PageService;
use App\Models\article_visit_count;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Layout\Website\Helpers\UrlHelper;
use Illuminate\Support\Facades\DB;
use sngrl\SphinxSearch\SphinxSearch;

class ArticleController extends Controller {

    public function __construct() {

    }

    public function shortliink() {
//		$articles = article::whereNull('article_shortlink')->get();
//		foreach ($articles as $article){
//			\Themes\dailysabah\controllers\DailysabahController::getShortlinkAction("https://www.dailysabah.com/".$article->permalink, $article->cms_article_id);
//		}
//		echo "done";
//		die;
    }

    public function index(Request $request, $article_id = 0) {

        //This Code to display the preview of an article from NP
        if (null != $request->input('article_data_json')) {

            $article_array = json_decode($request->input('article_data_json'));
            //Here we check if we have a function getPostArticlePreview article in Theme controller
            $theme_controller_class = 'Themes\\' . ThemeService::Name() . '\\controllers\\' . ThemeService::ThemeController();
            if (class_exists($theme_controller_class)) {
                $themeController = new $theme_controller_class();
                if (method_exists($themeController, 'getPostArticlePreview')) {
                    $article = $themeController->getPostArticlePreview($article_array);
                } else {
                    $article = new article();
                }
            }
        } else {
            if (ThemeService::ConfigValue('SPECIAL_URL')) {
                $permalink = $request->getPathInfo();
                $permalink = urldecode(trim($permalink, "/"));

                //Special cases for articles
                $theme_controller_class = 'Themes\\' . ThemeService::Name() . '\\controllers\\' . CommonController::controller_class_name();
                if (class_exists($theme_controller_class)) {
                    $themeController = new $theme_controller_class();
                    if (method_exists($themeController, 'getArticleSpecialCase')) {
                        $function_name = 'getArticleSpecialCase';
                        $article = $themeController->$function_name($permalink);
                    }
                }
                if (empty($article)) {
                    $article = article::where('permalink', $permalink)->first();
                }

                if (empty($article)) {
                    $theme_controller_class = 'Themes\\' . ThemeService::Name() . '\\controllers\\' . CommonController::controller_class_name();
                    if (class_exists($theme_controller_class)) {
                        $themeController = new $theme_controller_class();
                        if (method_exists($themeController, 'getPermalinkAlias')) {
                            $function_name = 'getPermalinkAlias';
                            $url = $themeController->$function_name($permalink);
                            if (!empty($url)) {
                                return redirect()->to($url, 301)->send();
                            }
                        }
                    }
                }

                if (empty($article) && ThemeService::ConfigValue('ARCHIVE_CUTOFF') > 0) {
					$theme_controller_class = 'Themes\\' . ThemeService::Name() . '\\controllers\\' . CommonController::controller_class_name();
					if (class_exists($theme_controller_class)) {
						$themeController = new $theme_controller_class();
						if (method_exists($themeController, 'workWithPermalink')) {
							$function_name = 'workWithPermalink';
							$article_archive = $themeController->$function_name($permalink);
						}else{
							$article_archive = article_archive::where('permalink', $permalink)->first();
						}
					}else{
						$article_archive = article_archive::where('permalink', $permalink)->first();
					}


                    if (isset($article_archive)) {
                        $theme_controller_class = 'Themes\\' . ThemeService::Name() . '\\controllers\\' . CommonController::controller_class_name();
                        if (class_exists($theme_controller_class)) {
                            $themeController = new $theme_controller_class();
                            if (method_exists($themeController, 'getArchiveArticle')) {
                                $function_name = 'getArchiveArticle';
                                $article = $themeController->$function_name($article_archive);
                                //Top check if article is deleted or no
                                if (isset($article->is_active) && $article->is_active == 0) {
                                    return \Illuminate\Support\Facades\Response::view('theme::errors.404', array(), 404);
                                }
                            }
                        }
                    }

                    if (!isset($article_archive)) {
                        $theme_controller_class = 'Themes\\' . ThemeService::Name() . '\\controllers\\' . CommonController::controller_class_name();
                        if (class_exists($theme_controller_class)) {
                            $themeController = new $theme_controller_class();
                            if (method_exists($themeController, 'getUrlAliasArticle')) {
                                $function_name = 'getUrlAliasArticle';
                                $url = $themeController->$function_name($permalink);
                                if (!empty($url)) {
                                    return redirect()->to($url, 301)->send();
                                }
                            }
                        }
                    }
                }
            } else {
                $article = article::find($article_id);


                if(ThemeService::ConfigValue('REDIRECT_FULL_PATH') && !empty($article)){
                    if(null === $request->section_name){
                        if(ThemeService::ConfigValue('URL_ENCODE_REDIRECT')){/*to encode url arabic*/
                            $url = preg_replace_callback(
                            '/([\x{0600}-\x{06ff}]|[\x{0750}-\x{077f}]|[\x{fb50}-\x{fc3f}]|[\x{fe70}-\x{fefc}])+/Uui'
                            ,(function($match) { return urlencode($match[1]); })
                            ,$article->seo_url
                        );
                        }else{
                            $url=$article->seo_url;
                        }
                        return redirect()->to($url, 301)->send();
                        exit;
                    }
                }

                if(!is_null($article) && ThemeService::ConfigValue('REDIRECT_BY_ID_AND_PERMALINK')){
                    $article_live_seo_url = UrlHelper::build_seo_url($article->cms_article_id, 'article', $article->article_title, $article->section_id, $article->sub_section_id, ThemeService::ConfigValue("APP_URL"), $article);
                    $current_seo_url = ThemeService::ConfigValue('APP_URL').trim($request->getPathInfo(),'/');
                    if(($article_live_seo_url != $current_seo_url) && !str_contains($request->getPathInfo(), $article->permalink)){
                        $article = NULL;
                    }

                    if (empty($article)) {
                        $theme_controller_class = 'Themes\\' . ThemeService::Name() . '\\controllers\\' . CommonController::controller_class_name();
                        if (class_exists($theme_controller_class)) {
                            $permalink = $request->getPathInfo();
                            $permalink = urldecode(trim($permalink, "/"));
                            $themeController = new $theme_controller_class();
                            if (method_exists($themeController, 'getPermalinkAlias')) {
                                $function_name = 'getPermalinkAlias';
                                $url = $themeController->$function_name($permalink);
                                if (!empty($url)) {
                                    return redirect()->to($url, 301)->send();
                                }
                            }
                        }
                    }
                }



                if (empty($article)) {
                    if(ThemeService::ConfigValue("PERMALINK_AS_OLD_ID")){
						$old_id = $request->getPathInfo();
						$old_id = urldecode(trim($old_id, "/"));
						$theme_controller_class = 'Themes\\' . ThemeService::Name() . '\\controllers\\' . CommonController::controller_class_name();
						if (class_exists($theme_controller_class)) {
							$themeController = new $theme_controller_class();
							if (method_exists($themeController, 'workWithOldId')) {
								$function_name = 'workWithOldId';
								$article_archive = $themeController->$function_name($old_id);
							}
						}
					}
					elseif(ThemeService::ConfigValue("ARTICLE_ARCHIVE_PERMALINK") && !is_numeric($article_id)){
						$permalink = $request->getPathInfo();
						$permalink = urldecode(trim($permalink, "/"));
						$theme_controller_class = 'Themes\\' . ThemeService::Name() . '\\controllers\\' . CommonController::controller_class_name();
						if (class_exists($theme_controller_class)) {
							$themeController = new $theme_controller_class();
							if (method_exists($themeController, 'workWithPermalink')) {
								$function_name = 'workWithPermalink';
								$article_archive = $themeController->$function_name($permalink);
							}else{
								$article_archive = article_archive::where('permalink', $permalink)->first();
							}
						}else{
							$article_archive = article_archive::where('permalink', $permalink)->first();
						}
					}elseif(ThemeService::ConfigValue("CHECK_ARCHIVE_PERMALINK")){ // to prevent redirect to archive when cms_id is same (example newtimes)
						$article_archive = article_archive::find_np($article_id);
                        $permalink = rtrim(UrlHelper::main_url(), '/') . $request->getPathInfo();
                        if(isset($article_archive)) {
                            $theme_controller_class = 'Themes\\' . ThemeService::Name() . '\\controllers\\' . CommonController::controller_class_name();
                            if (class_exists($theme_controller_class)) {
                                $themeController = new $theme_controller_class();
                                if (method_exists($themeController, 'getArchiveArticle')) {
                                    $function_name = 'getArchiveArticle';
                                    $article = $themeController->$function_name($article_archive);
                                    if($article->seo_url != $permalink){
                                         // if REDIRECT_BY_ID_AND_PERMALINK is true and we do not find the article having the given id and title neither in
                                        // live nor in archive, then before redirecting to 404, try to get the live article only by ID
                                        if(ThemeService::ConfigValue('REDIRECT_BY_ID_AND_PERMALINK')){
                                            $article = article::find($article_id);
                                            if($article){
                                                $article_live_seo_url = UrlHelper::build_seo_url($article->cms_article_id, 'article', $article->article_title, $article->section_id, $article->sub_section_id, ThemeService::ConfigValue("APP_URL"), $article);
                                                return redirect()->to($article_live_seo_url, 301)->send();
                                            }else{
                                                return \Illuminate\Support\Facades\Response::view('theme::errors.404', array(), 404);
                                            }
                                        }
                                        return \Illuminate\Support\Facades\Response::view('theme::errors.404', array(), 404);
                                    }
                                }
                            }
                        }
					}else{
						$article_archive = article_archive::find_np($article_id);
					}

                    if (isset($article_archive)) {
                        $theme_controller_class = 'Themes\\' . ThemeService::Name() . '\\controllers\\' . CommonController::controller_class_name();
                        if (class_exists($theme_controller_class)) {
                            $themeController = new $theme_controller_class();
                            if (method_exists($themeController, 'getArchiveArticle')) {
                                $function_name = 'getArchiveArticle';
                                $article = $themeController->$function_name($article_archive);
                                if (isset($article->is_active) && $article->is_active == 0) {
                                    return \Illuminate\Support\Facades\Response::view('theme::errors.404', array(), 404);
                                }
                            }
                        }
                    }else{
                        return \Illuminate\Support\Facades\Response::view('theme::errors.404', array(), 404);
                    }
                }
            }
        }

//Check log why it's used and remove
        if (empty($article) && empty($article_archive) && ThemeService::ConfigValue('SPECIAL_URL')) {
            if (is_numeric($article_id)) {
                if(ThemeService::ConfigValue('REDIRECT_BY_ID_AND_PERMALINK')){
                    $article = article::find($article_id);
                    if($article){
                        $article_live_seo_url = UrlHelper::build_seo_url($article->cms_article_id, 'article', $article->article_title, $article->section_id, $article->sub_section_id, ThemeService::ConfigValue("APP_URL"), $article);
                        return redirect()->to($article_live_seo_url, 301)->send();
                    }else{
                        return \Illuminate\Support\Facades\Response::view('theme::errors.404', array(), 404);
                    }
                }else {
                    $article = article::find($article_id);
                    if (!empty($article)) {
                        PageService::SetArticle($article);
                        $page = PageService::PageByTitle('article');
                        PageService::SetPage($page);
                        $url = '/' . $article->permalink;
                        $str = view("theme::metatags.article_redirect", ["url" => $url])->render();
                        //to check print of $str later on for now it works properly
//					echo $str;
                        return redirect()->to($url, 301)->send();
                    } else {
                        $article_archive = article_archive::find_np($article_id);
                        if (!empty($article_archive) && !empty($article_archive->permalink)) {
                            return redirect()->to('/' . $article_archive->permalink, 301)->send();
                        } else {
                            header("HTTP/1.0 404 Not Found");
                            $theme_controller_class = 'Themes\\' . ThemeService::Name() . '\\controllers\\' . ThemeService::ThemeController();
                            if (class_exists($theme_controller_class)) {
                                $themeController = new $theme_controller_class();
                                if (method_exists($themeController, 'getErrorPage')) {
                                    $themeController->getErrorPage();
                                }
                            }
                            echo view('theme::errors.404');
                            exit;
                        }
                    }
                }
            } else {
                header("HTTP/1.0 404 Not Found");
                $theme_controller_class = 'Themes\\' . ThemeService::Name() . '\\controllers\\' . ThemeService::ThemeController();
                if (class_exists($theme_controller_class)) {
                    $themeController = new $theme_controller_class();
                    if (method_exists($themeController, 'getErrorPage')) {
                        $themeController->getErrorPage();
                    }
                }
                echo view('theme::errors.404');
                exit;
            }
        }
        $article->article_title = str_replace("&#039;", "'", $article->article_title);

        //$page = PageService::PageByTitle('author');
        if (isset($article_archive) && ThemeService::ConfigValue('SPECIAL_URL')) {
            if (ThemeService::ConfigValue("ARTICLE_FUNCTION") && !empty(ThemeService::ConfigValue("ARTICLE_FUNCTION"))) {
                $theme_controller_class = 'Themes\\' . ThemeService::Name() . '\\controllers\\' . CommonController::controller_class_name();
                if (class_exists($theme_controller_class)) {
                    $themeController = new $theme_controller_class();
                    if (method_exists($themeController, ThemeService::ConfigValue("ARTICLE_FUNCTION"))) {
                        $function_name = ThemeService::ConfigValue("ARTICLE_FUNCTION");
                        $pageTitle = $themeController->$function_name($article);
                        $page = PageService::PageByTitle($pageTitle);
                    }
                }
            } else {
                $page = PageService::PageByTitle('article');
            }
        } elseif ($article->media_gallery_flag == 1 && !empty(ThemeService::ConfigValue('SECTION_PHOTO')) && $article->section_id == ThemeService::ConfigValue('SECTION_PHOTO') && !empty($request->query('p'))) {
            $page = PageService::PageByTitle('album detail');
        } elseif ($article->media_gallery_flag == 1 && !empty(ThemeService::ConfigValue('SECTION_PHOTO')) && $article->section_id == ThemeService::ConfigValue('SECTION_PHOTO')) {
            $page = PageService::PageByTitle('album');
        } elseif ($article->section_id == ThemeService::ConfigValue('SECTION_PHOTO') && !empty(ThemeService::ConfigValue('SECTION_PHOTO'))) {
            $page = PageService::PageByTitle('photo');
        } else if ((!empty(ThemeService::ConfigValue('ARTICLE_PAGE_ID')))) {
            $page = \App\Models\page::where("np_page_id", ThemeService::ConfigValue('ARTICLE_PAGE_ID'))->first();
        } else if (ThemeService::ConfigValue("ARTICLE_FUNCTION") && !empty(ThemeService::ConfigValue("ARTICLE_FUNCTION"))) {
            $theme_controller_class = 'Themes\\' . ThemeService::Name() . '\\controllers\\' . CommonController::controller_class_name();
            if (class_exists($theme_controller_class)) {
                $themeController = new $theme_controller_class();
                if (method_exists($themeController, ThemeService::ConfigValue("ARTICLE_FUNCTION"))) {
                    $function_name = ThemeService::ConfigValue("ARTICLE_FUNCTION");
                    $pageTitle = $themeController->$function_name($article);
                    $page = PageService::PageByTitle($pageTitle);
                }
            }
        } else {
            $page = PageService::PageByTitle('article');
        }

        if (isset($article_archive) && ThemeService::ConfigValue('SPECIAL_URL')) {
            $page->title = $article_archive->article_title . ThemeService::ConfigValue('SEPARATOR') . ThemeService::ConfigValue('NEWSPAPER_PAGE_TITLE');
        } else {
            $page->title = $article->article_title . ThemeService::ConfigValue('SEPARATOR') . ThemeService::ConfigValue('NEWSPAPER_PAGE_TITLE');
        }

        PageService::SetPage($page);

        if (ThemeService::ConfigValue('SPECIAL_URL') && empty($article)) {
            PageService::SetArticleArchive($article_archive);
        } else {
            PageService::SetArticle($article);
        }

        $page->is_article_page = 1;
        $page_bootstrap = new PageBootstrap($page);
        PageService::SetPage($page);

        View::share('page', $page);

        if (ThemeService::ConfigValue('ADS_DISPLAY')) {
            $adsHeaderDisplayInfo = ads_header_display::where('section_id', $article->section_id)->first();
            if (ThemeService::ConfigValue('ADS_DEFAULT')) {
                if (empty($adsHeaderDisplayInfo)) {
                    $adsHeaderDisplayInfo = ads_header_display::where('section_id', 0)->first();
                }
            }
        }
        if (ThemeService::ConfigValue('ADS_DISPLAY')) {
            View::share('articleHeaderScript', $adsHeaderDisplayInfo);
        }

        return view('theme::pages.article', ['page_bootstrap' => $page_bootstrap, 'page' => $page]);
    }

    public function ampArticle(Request $request, $article_id = 0) {
        $article = article::find_np($article_id);
        PageService::SetStaticPage($article->article_title);
        return view('theme::components.google_amp', ['article' => $article]);
    }

    public function search(Request $request) {
        $page_data = PageService::SetStaticPage('Search');

        $sections = section::all();
        $q = $request->q;
        $qtag = $request->qtag;
        $qguid = $request->qguid;
        $qsort = $request->qsort;
        $DateRange_DDL = $request->DateRange_DDL;
        $qrec = $request->qrec;
        $fDay = $request->fDay;
        $fMonth = $request->fMonth;
        $fYear = $request->fYear;
        $tDay = $request->tDay;
        $tMonth1 = $request->tMonth1;
        $tYear = $request->tYear;
        $radio = $request->QDR;
        //dd($qrec);

        return view('theme::pages.search', [
            'sections' => $sections,
            'q' => $q,
            'qtag' => $qtag,
            'qguid' => $qguid,
            'qrec' => $qrec,
            'DateRange_DDL' => $DateRange_DDL,
            'qsort' => $qsort,
            'fDay' => $fDay,
            'fMonth' => $fMonth,
            'fYear' => $fYear,
            'tDay' => $tDay,
            'tMonth1' => $tMonth1,
            'tYear' => $tYear,
            'radio' => $radio,
        ]);
    }

    public function countArticle(Request $request) {
        $articleId = $request->article_id;
        $initial_key = 1;
        $counter_mod = ThemeService::ConfigValue('VISIT_COUNT_INCREMENT') ? ThemeService::ConfigValue('VISIT_COUNT_INCREMENT') : Cache::counter_mod;
        $cache_value = Cache::get('article_counter_' . $articleId);
        if ($cache_value) {
            $visit_count = Cache::increment('article_counter_' . $articleId);
        } else {
            Cache::put('article_counter_' . $articleId, $initial_key, 99999);
        }
        $cache_final_value = Cache::get('article_counter_' . $articleId);
        echo 'Article Id: ' . $articleId . '- Visit_count: ' . $cache_final_value . '</br>';
        echo 'counter_mod: ' . $counter_mod;


        if ($cache_final_value % $counter_mod == 0) {
            echo 'increment: ' . $cache_final_value;
			if(ThemeService::ConfigValue('VISIT_COUNT_UPDATE_DATE')){
				$article = article::where('cms_article_id', $articleId)->first();
                if($article->exists()){
                    $visit_count = $article->visit_count + $counter_mod;
                    $article->update(['visit_count_update_date'=> \Illuminate\Support\Facades\DB::raw('now()'),'visit_count' => $visit_count]);
                    if(ThemeService::ConfigValue('VISIT_COUNT_TABLE_UPDATE')){ // if you want to use this value in theme other than al-marsd we have to fix comment_count column
                        $avc = article_visit_count::where('np_article_id', $article->np_article_id)->first();
                        if($avc){
                            $avc->update(['visit_count' => $visit_count, 'comment_count'=> $article->comments_count]);
                        }else{
                            $avc = article_visit_count::create(['np_article_id'=> $article->np_article_id, 'visit_count' => $visit_count, 'comment_count'=> $article->comments_count, 'max_publish_time'=>$article->max_publish_time]);
                        }
                    }
                }
            }else{
				$article = article::where('cms_article_id', $articleId)->first();
                if($article->exists()){
                    $visit_count = $article->visit_count + $counter_mod;
					$article->update(
						['visit_count_update_date'=> \Illuminate\Support\Facades\DB::raw('now()'),
						'visit_count' => $visit_count]);
                    if(ThemeService::ConfigValue('VISIT_COUNT_TABLE_UPDATE')){// if you want to use this value in theme other than al-marsd we have to fix comment_count column
                        $avc = article_visit_count::where('np_article_id', $article->np_article_id)->first();
                        if($avc){
                            $avc->update(['visit_count' => $article->visit_count, 'comment_count'=> $article->comments_count]);
                        }else{
                            $avc = article_visit_count::create(['np_article_id'=> $article->np_article_id, 'visit_count' => $article->visit_count, 'comment_count'=> $article->comments_count, 'max_publish_time'=>$article->max_publish_time]);
                        }
					}
                }
			}

        }
    }

    public static function getCustomRelatedArticles($article, $limit){
        $articles = array();
        if(!empty($article) && !empty($article->custom_fields->article_type) && !empty($article->custom_fields->article_type[0]) && in_array(strtolower($article->custom_fields->article_type[0]), array('video','gallery'))){
            $article_ids = web_data_values::distinct('cms_article_id')->select('cms_article_id')->where('cms_article_id', '<>', $article->cms_article_id)->where('data_key', 'article_type')->where('data_value', $article->custom_fields->article_type[0]);
            $articles = article::whereIn('cms_article_id', $article_ids)
                ->where('max_publish_time', '<', DB::raw(ThemeService::ConfigValue('GIVEN_TIME')))
                ->where('section_id', $article->section_id)
                ->orderBy('max_publish_time', 'desc')
                ->limit($limit)
                ->get();
        }elseif(!empty($article)) {
            $related_ids = [];
            $sphinx_field = ThemeService::ConfigValue('SPHINX_RELATED_ARTICLES_COLUMN')?: 'article_tags' ;
            if (!empty($article->$sphinx_field)) {
                $sphinx = new SphinxSearch();
                $index_from = 0;
                $query = "@" .$sphinx_field. "'" . str_replace(",", "' | @" .$sphinx_field ." '", $article->$sphinx_field) . "'";
                $sphinx->search($query, ThemeService::ConfigValue('WEBSITE_FULL').",".ThemeService::ConfigValue('WEBSITE_ARCHIVE_FULL').",". ThemeService::ConfigValue('WEBSITE_DELTA'));
                $sphinx->limit($limit, $index_from, 1000000, 0);
                $sphinx->setMatchMode(\Sphinx\SphinxClient::SPH_MATCH_EXTENDED2);
                $sphinx->setSortMode(\Sphinx\SphinxClient::SPH_SORT_EXTENDED, "publish_time DESC");
                $results = $sphinx->get();
                if (isset($results['matches'])) {
                    $result_count = $results['total'];
                    foreach ($results['matches'] as $key => $match) {
                        if ($match['attrs']['is_old_article'] == 0 && $key != $article->cms_article_id) {
                            $related_ids[] = $match['attrs']['np_article_id'];
                        }
                        if ($match['attrs']['is_old_article'] == 1 && $key != $article->cms_article_id) {
                            $archive_related_ids[] = $key;
                        }
                    }
                }
            }
            if(count($related_ids) < $limit && empty($archive_related_ids) && ThemeService::ConfigValue('RELATED_SECTION_ARTICLE')){
                $section_limit = $limit - count($related_ids);
                $section_articles = article::where('np_article_id', '<>', $article->np_article_id)
                    ->where('section_id', $article->section_id);
                if($article->sub_section_id > 0){
                    $section_articles = $section_articles->where('sub_section_id', $article->sub_section_id);
                }
                $section_articles = $section_articles->where('max_publish_time', '<', DB::raw(ThemeService::ConfigValue('GIVEN_TIME')))
                    ->orderBy('max_publish_time', 'desc')
                    ->limit($section_limit)
                    ->get();

                foreach ($section_articles as $section_article) {
                    $related_ids[] = $section_article->np_article_id;
                }
            }elseif(count($related_ids) < $limit && !empty($archive_related_ids)){
                $archive_limit = $limit - count($related_ids);
                $archive_related_ids = array_slice($archive_related_ids,0,$archive_limit);
                $archive_articles_query = article_archive::whereIn('cms_article_id' ,$archive_related_ids )->orderByRaw('FIELD(cms_article_id,'. implode(",", $archive_related_ids).')')->get();
            }
            if(count($related_ids) > 0){
                $articles = article::whereIn('np_article_id', $related_ids)
                    ->where('np_article_id', '<>', $article->np_article_id)
                    ->orderBy('max_publish_time', 'desc')->get();
            }
            if(!empty($archive_articles_query) && count($archive_articles_query) > 0){
                $theme_controller_class = 'Themes\\' . ThemeService::Name() . '\\controllers\\' . CommonController::controller_class_name();
                if (class_exists($theme_controller_class)) {
                    $themeController = new $theme_controller_class();
                    if (method_exists($themeController, 'getArchiveArticle')) {
                        $function_name = 'getArchiveArticle';
                        foreach ($archive_articles_query as $article_archive) {
                            $articles[] = $themeController->$function_name($article_archive);
                        }
                    }
                }
            }

        }
        return $articles;
    }

    public static function getRecommendedArticlesSection($article, $limit){
        $articles = array();
        if(!empty($article)) {
            $section_limit = $limit;
            $section_articles = article::where('np_article_id', '<>', $article->np_article_id)
                ->where('section_id', $article->section_id);
            if($article->sub_section_id > 0){
                $section_articles = $section_articles->where('sub_section_id', $article->sub_section_id);
            }
            $section_articles = $section_articles->where('max_publish_time', '<', DB::raw(ThemeService::ConfigValue('GIVEN_TIME')))
                ->orderBy('max_publish_time', 'desc')
                ->limit($section_limit)
                ->get();

            foreach ($section_articles as $section_article) {
                $related_ids[] = $section_article->np_article_id;
            }
            if(count($related_ids) < $limit){
                $archive_limit = $limit - count($related_ids);
                $section = section::find_np($article->section_id);
                if(!empty($section) && $section->exists) {
                    $section_archives = article_archive::where('section_id', $section->cms_section_id);
                    if ($article->sub_section_id > 0) {
                        $subsection = sub_section::find_np($article->sub_section_id);
                        if(!empty($subsection) && $subsection->exists) {
                            $section_archives = $section_archives->where('sub_section_id', $subsection->cms_sub_section_id);
                        }
                    }
                    $archive_articles_query = $section_archives->orderByDesc('publish_time')->limit($archive_limit)->get();
                }
            }
            if(count($related_ids) > 0){
                $articles = article::whereIn('np_article_id', $related_ids)
                    ->where('np_article_id', '<>', $article->np_article_id)
                    ->orderBy('max_publish_time', 'desc')->get();
            }
            if(!empty($archive_articles_query) && count($archive_articles_query) > 0){
                $theme_controller_class = 'Themes\\' . ThemeService::Name() . '\\controllers\\' . CommonController::controller_class_name();
                if (class_exists($theme_controller_class)) {
                    $themeController = new $theme_controller_class();
                    if (method_exists($themeController, 'getArchiveArticle')) {
                        $function_name = 'getArchiveArticle';
                        foreach ($archive_articles_query as $article_archive) {
                            $articles[] = $themeController->$function_name($article_archive);
                        }
                    }
                }
            }

        }
        return $articles;
    }

}
