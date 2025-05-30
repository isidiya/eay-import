<?php

namespace Layout\Website\Services;

use App\Models\article;
use App\Models\related_articles;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use sngrl\SphinxSearch\SphinxSearch;
use App\Models\article_multi_section;
use Illuminate\Support\Facades\Cache;
use Layout\Website\Models\WebsiteWidget;
use App\Http\Controllers\CommonController;

class WidgetDataService {

    const type_paginator_widget = 'paginator_widget';
    const type_articles_filter = 'articles_filter';
    const type_static_articles = 'static_articles';
    const type_static_widget = 'static_widget';
    const type_most_read = 'most_read';
    const type_menu = 'menu';
    const type_tabs = 'tabs';
    const type_wysiwyg = 'wysiwyg';
    const type_free_dynamic_html_text = 'free_dynamic_html_text';
    const type_article_details = 'article_details';
    const type_related_articles = 'related_articles';
    const type_dynamic_related_articles = 'dynamic_related_articles';
    const type_latest_news = 'latest_news';
    const type_standalone_image_widget = 'standalone_image_widget';

    private $website_widget;
    private $page;

    public function __construct(WebsiteWidget $website_widget) {
        $this->website_widget = $website_widget;
        $this->page = PageService::Page();
    }

    public function fetch() {
        $data = new \stdClass();

        $pagination = $this->website_widget->enable_widget_pagination() ? true : false;

        $skip_article_without_image = $this->website_widget->skip_article_without_image() ? true : false;

        $homepage_article_flag = $this->website_widget->homepage_article_flag() ? true : false;

        $count_articles_flag = $this->website_widget->count_articles_flag() ? true : false;
        try {
            switch ($this->website_widget->GetWidgetDataSourceType()) {
                case self::type_static_widget :
                    $data = array();
                    break;
                case self::type_articles_filter :
                    $data->articles = $this->articlesFilter($pagination, false, $skip_article_without_image, $homepage_article_flag);

                    if ($count_articles_flag) {
                        if ($this->page->is_home_page) {
                            $data->count_articles = false;
                        } else {
                            $data->count_articles = $this->articlesFilter(false, true, $skip_article_without_image, $homepage_article_flag);
                        }
                    }

                    break;
                case self::type_static_articles :
                    $data->articles = $this->staticArticles();
                    break;
                case self::type_most_read :
                    $data->articles = $this->mostReadArticles();
                    break;
                case self::type_menu :
                    $data->menu = $this->menu();
                    break;
                case self::type_tabs :
                    $data = $this->tabs();
                    break;
                case self::type_free_dynamic_html_text :
                    $data->html = $this->wysiwyg();
                    break;
                case self::type_wysiwyg :
                    $data->html = $this->wysiwyg();
                    break;
                case self::type_article_details :
                    $data->article = $this->articleDetails();
                    break;
                case self::type_related_articles :
                    $data->articles = $this->relatedArticles($pagination);
                    break;
                case self::type_dynamic_related_articles :
                    $data->articles = $this->dynamicRelatedArticles();
                    break;
                case self::type_latest_news :
                    $data->articles = $this->latestNews();
                    break;
                case self::type_standalone_image_widget :
                    $data->html = $this->standaloneImageWidget();
                    break;



                default :
                    break;
            }
        } catch (\ErrorException $ex) {
            $data = [];
            $data['ex'] = $ex;
            //$data['ex'] = $ex;
        }
        return $data;
    }

    private function staticArticles() {
        $article_ids = $this->website_widget->GetWidgetDataValue('articles_ids');
        $articles_order = $this->website_widget->GetWidgetDataValue('articles_order');
        if (ThemeService::ConfigValue('ENABLE_MAX_PUBLISH_TIME') && (\Illuminate\Support\Facades\Schema::hasColumn("article", "max_publish_time"))) {
            $articles = article::whereIn('np_article_id', $article_ids)->where('max_publish_time', '<', DB::raw('now()'))->get();
        } else {
            $articles = article::whereIn('np_article_id', $article_ids)->where('publish_time', '<', DB::raw('now()'))->get();
        }

        if (!empty($articles_order)) {
            $articles = $this->sort_by_widget_order($article_ids, $articles_order, $articles);
        }

        return $articles->take($this->website_widget->GetLimit());
    }

    private function mostReadArticles() {
        $data = $this->website_widget->GetWidgetData();
        $limit = $this->website_widget->GetLimit() ? $this->website_widget->GetLimit() : '20';
        $day_limit = isset($data->date_limit)? $data->date_limit: null;
        $sections = isset($data->section) ? array($data->section) : (isset($data->art_sec) ? array_unique(array_filter($data->art_sec)) : []); // sections id

        $articles = article::with(['image']);

        if (!empty($sections) && !in_array(-1, $sections)) {
            $articles = $articles->whereIn('section_id', $sections);
        }
        if (ThemeService::ConfigValue('GIVEN_TIME_MOST_READ')) {
            if (ThemeService::ConfigValue('ENABLE_MAX_PUBLISH_TIME')) {
                $articles = $articles->where('max_publish_time', '>', DB::raw(ThemeService::ConfigValue('GIVEN_TIME_MOST_READ')));
            } else {
                $articles = $articles->where('publish_time', '>', DB::raw(ThemeService::ConfigValue('GIVEN_TIME_MOST_READ')));
            }
        }
        elseif(isset($day_limit)){
            if (ThemeService::ConfigValue('ENABLE_MAX_PUBLISH_TIME')) {
                $articles = $articles->where('max_publish_time', '>', DB::raw('NOW()-INTERVAL '. $day_limit .' DAY'));
            } else {
                $articles = $articles->where('publish_time', '>', DB::raw('NOW()-INTERVAL '. $day_limit .' DAY'));
            }
        }


        if (ThemeService::ConfigValue('ENABLE_MAX_PUBLISH_TIME')) {
            $articles = $articles->where('max_publish_time', '<', DB::raw('now()'))->orderBy('visit_count', 'DESC')->limit($limit)->get();
        } else {
            $articles = $articles->where('publish_time', '<', DB::raw('now()'))->orderBy('visit_count', 'DESC')->limit($limit)->get();
        }

        return $articles;
    }

    public function articlesFilter($pagination = false, $count_articles_flag = false, $skip_article_without_image = false, $homepage_article_flag = true) {
        $data = $this->website_widget->GetWidgetData();
        if (!empty($data->number_of_articles) && $data->number_of_articles == -1) {
            return [$data];
        } else {
            $limit = $this->website_widget->GetLimit();
            $offset = $this->website_widget->GetOffset();

            $article_authors = !empty($data->article_author) ? array_unique(array_filter($data->article_author)) : []; // authors id
            $sections = !empty($data->art_sec) ? array_unique(array_filter($data->art_sec)) : []; // sections id
            $sub_sections = !empty($data->art_sub_sec) ? array_unique(array_filter($data->art_sub_sec)) : []; // subsections id

            if (isset($_COOKIE['country_id']) && $_COOKIE['country_id'] > 0) {
                $countryId = $_COOKIE['country_id'];
                if (!empty($data->art_country)) {
                    $countryId = $data->art_country;
                }

                $countryIds = $countryId > 0 ? explode(',', $countryId) : [0];
            }

            if (in_array(-1, $sections)) {
                $sections = (!empty(PageService::Article()) && !empty(PageService::Article()->section_id)) ? [PageService::Article()->section_id] : [];
            }
            if (in_array(-1, $sub_sections)) {
                $sub_sections = (!empty(PageService::Article()) && !empty(PageService::Article()->sub_section_id)) ? [PageService::Article()->sub_section_id] : [];
            }

            $sort_by = !empty($data->sort_by) ? $data->sort_by : '';

            if (ThemeService::ConfigValue('ENABLE_MAX_PUBLISH_TIME')) {
                $order_by = 'max_publish_time';
            } else {
                $order_by = 'publish_time';
            }

            switch ($sort_by) {
                default:
                case 'issue_date':
                    if (ThemeService::ConfigValue('ENABLE_MAX_PUBLISH_TIME')) {
                        $order_by = 'max_publish_time';
                    } else {
                        $order_by = 'publish_time';
                    }
                    break;
                case 'article_name':
                    $order_by = 'article_name';
                    break;
                case 'section':
                    $order_by = 'section_id';
                    break;
            }

            // Get articles
            $append_conditions = [];
            if (ThemeService::ConfigValue('POLL_SECTION_ID')) { 
                $append_conditions[] = ['section_id', '<>', ThemeService::ConfigValue('POLL_SECTION_ID')];
            }
            
            if (ThemeService::ConfigValue('POLL_SUB_SECTION_ID')) {
                $append_conditions[] = ['sub_section_id', '<>', ThemeService::ConfigValue('POLL_SUB_SECTION_ID')];
            }

            if (ThemeService::ConfigValue('BREAKING_NEWS_SKIP')) { 
                $append_conditions[] = ['breaking_news', '<>', 1];
            }

            //$articles_query = $articles_query->where('is_old_article', 0);
            //$append_conditions[] = ['is_old_article', '=', 0];

            if (!empty(PageService::Article())) {
                //$articles_query = $articles_query->where('cms_article_id', '<>', PageService::Article()->cms_article_id);
                $append_conditions[] = ['cms_article_id', '<>', PageService::Article()->cms_article_id];
            }

            if (ThemeService::ConfigValue('MEDIA_GALLERY_FLAG')) {
                //$articles_query = $articles_query->where('media_gallery_flag', 0);
                $append_conditions[] = ['media_gallery_flag', '=', 0];
            }
            
            if (ThemeService::ConfigValue('USE_SUB_ARTICLES')) {  
                $append_conditions[] = ['article_parent_id','=',0];
            } 

            if (ThemeService::ConfigValue('MULTI_COUNTRIES') && empty($article_authors)) {



                $articles_query = article_multi_section::distinct('ams_article_id')->select('ams_article_id');

                $articles_query = $articles_query->where('ams_article_date', '<', DB::raw(ThemeService::ConfigValue('GIVEN_TIME')));

                if(isset($countryIds) && count($countryIds) > 0 ){
                    $articles_query = $articles_query->whereIn('ams_country_id', $countryIds);
                }

                if (!empty($sub_sections)) {
                    $articles_query = $articles_query->whereIn('ams_subsection_id', $sub_sections);
                }else if (!empty($sections)) {
                    $articles_query = $articles_query->whereIn('ams_section_id', $sections);
                }

                $articles_query  = $articles_query->orderBy("ams_article_date","desc");


                $articles_query = $articles_query->limit($limit + 300);
                if($offset){
                    $articles_query = $articles_query->offset($offset);
                }

                $article_data = article::select("cms_article_id")->whereIn("cms_article_id",$articles_query->get()->toArray());


                foreach ($append_conditions as $c) {
                    $article_data = $article_data->where($c[0], $c[1], $c[2]);
                }

                if (isset($this->page->static_articles_id)) {
                    $article_data = $article_data->whereNotIn('np_article_id', $this->page->static_articles_id);
                }

                if ($skip_article_without_image) {
                    if (\Illuminate\Support\Facades\Schema::hasColumn("article", "cropped_image")) {
                        $article_data = $article_data->where('cropped_image', 1);
                    } else {
                        $article_data = $article_data->whereRaw('LENGTH(image_path) > 5');
                    }
                }
                if ($homepage_article_flag) {
                    $article_data = $article_data->where('homepage_article_flag', '=', 1);
                }

                if (ThemeService::ConfigValue('ENABLE_MAX_PUBLISH_TIME')) {
                    $article_data = $article_data->where('max_publish_time', '<', DB::raw(ThemeService::ConfigValue('GIVEN_TIME')))
                        ->orderBy("max_publish_time","desc");
                }else{
                    $article_data = $article_data->where('publish_time', '<', DB::raw(ThemeService::ConfigValue('GIVEN_TIME')))
                        ->orderBy("publish_time","desc");
                }

                $article_data = $article_data->limit($limit);

                if(ThemeService::ConfigValue('CACHE_MULTISECTION')){
                    $article_data = Cache::remember("article_data_cache_" . $data->element_id, ThemeService::ConfigValue('CACHE_MULTISECTION'), function () use ($article_data) {
                        return $article_data->get();
                    });
                }else{
                    $article_data = $article_data->get();
                }

                $articles = article::whereIn("cms_article_id",$article_data);


                if (ThemeService::ConfigValue('ENABLE_MAX_PUBLISH_TIME')) {
                    $articles = $articles->where('max_publish_time', '<', DB::raw(ThemeService::ConfigValue('GIVEN_TIME')))
                        ->orderBy("max_publish_time","desc");
                    if( ThemeService::ConfigValue('SAME_PUBLISH_TIME') ){
                        $articles = $articles->orderBy('alt_publish_time', 'desc');
                    }
                }else{
                    $articles = $articles->where('publish_time', '<', DB::raw(ThemeService::ConfigValue('GIVEN_TIME')))
                        ->orderBy("publish_time","desc");
                    if( ThemeService::ConfigValue('SAME_PUBLISH_TIME') ){
                        $articles = $articles->orderBy('alt_publish_time', 'desc');
                    }
                }

                $articles =  $articles->get();

                $count_articles = count($articles);

                if ($limit > $count_articles) {
                    $count_fromarchive = $limit - $count_articles;
                    $theme_controller_class = 'Themes\\' . ThemeService::Name() . '\\controllers\\' . CommonController::controller_class_name();
                    if (class_exists($theme_controller_class)) {
                        $themeController = new $theme_controller_class();
                        if (method_exists($themeController, 'getArticlesArchiveWithMultiSection')) {
                            $function_name = 'getArticlesArchiveWithMultiSection';
                            $article_archive = $themeController->$function_name($count_fromarchive, $sections, $sub_sections);
                            foreach ($article_archive as $article_arch) {
                                $articles[] = $article_arch;
                            }
                            $articles = collect($articles);
                        }
                    }
                }
            } else {
                $articles_query = article::with(['image']);

                if (ThemeService::ConfigValue('SKIP_ARTICLES_LOGIC')) {
                    if (PageService::Page()->is_home_page) {
                        $articles_query = $articles_query->has('article.image');
                    }
                }
                foreach ($append_conditions as $c) {
                    $articles_query = $articles_query->where($c[0], $c[1], $c[2]);
                }
                if (!empty($sections)) {
                    $articles_query = $articles_query->whereIn('section_id', $sections);
                }

                if (!empty($sub_sections)) {
                    $articles_query = $articles_query->whereIn('sub_section_id', $sub_sections);
                }

                if (!empty($article_authors)) {
                    $articles_query = $articles_query->whereIn('author_id', $article_authors);
                }

                if ($homepage_article_flag) {
                    $articles_query = $articles_query->where('homepage_article_flag', '=', 1);
                }

                if ($skip_article_without_image) {
                    $articles_query = $articles_query->whereRaw('LENGTH(image_path) > 5');
                }

                if (ThemeService::ConfigValue('ENABLE_MAX_PUBLISH_TIME')) {
                    $articles_query = $articles_query->where('max_publish_time', '<', DB::raw(ThemeService::ConfigValue('GIVEN_TIME')));
                } else {
                    $articles_query = $articles_query->where('publish_time', '<', DB::raw(ThemeService::ConfigValue('GIVEN_TIME')));
                }
                if (isset($this->page->static_articles_id)) {
                    $articles_query = $articles_query->whereNotIn('np_article_id', $this->page->static_articles_id);
                }

                if ($pagination) {
                    $articles_query = $articles_query->orderBy($order_by, 'desc');
                    if( ThemeService::ConfigValue('SAME_PUBLISH_TIME') ){
                        $articles_query = $articles_query->orderBy('alt_publish_time', 'desc');
                    }
                    $articles = $articles_query->paginate($limit, ['*'], 'pgno');
                } else {
                    if ($count_articles_flag) {
                        $count_articles = $articles_query->count();
                        return $count_articles;
                    }
                    $articles_query = $articles_query->orderBy($order_by, 'desc')
                        ->offset($offset)
                        ->limit($limit);
                    if( ThemeService::ConfigValue('SAME_PUBLISH_TIME') ){
                        $articles_query = $articles_query->orderBy('alt_publish_time', 'desc');
                    }
                    $articles = $articles_query->get();
                }



            }
            
            $count_articles = count($articles);
            if ($limit > $count_articles) {
                $count_fromarchive = $limit - $count_articles;
                $theme_controller_class = 'Themes\\' . ThemeService::Name() . '\\controllers\\' . CommonController::controller_class_name();
                if (class_exists($theme_controller_class)) {
                    $themeController = new $theme_controller_class();
                    if (method_exists($themeController, 'getArticlesArchiveWithWidgetArticlesFilterWithSubsections')) {
                        $function_name = 'getArticlesArchiveWithWidgetArticlesFilterWithSubsections';
                        $articles_archive = $themeController->$function_name($count_fromarchive, $sections,$sub_sections, $this->website_widget->np_widget_id, $offset);
                        if (!empty($articles_archive)) {
                            foreach ($articles_archive as $article_arch) {
                                $articles[] = $themeController->getArchiveArticle($article_arch);
                            }
                        }
                        $articles = collect($articles);

                    } elseif (method_exists($themeController, 'getArticlesArchiveWithWidgetArticlesFilter')){
                        $function_name = 'getArticlesArchiveWithWidgetArticlesFilter';
                        $articles_archive = $themeController->$function_name($count_fromarchive, $sections,$this->website_widget->np_widget_id,$offset);
                        if(!empty($articles_archive)){ 
                            foreach ($articles_archive as $article_arch) {
                                $articles[] = $themeController->getArchiveArticle($article_arch);
                            }
                        }
                        $articles = collect($articles);
                    }
                }
            }

            return $articles;
        }
    }

    public function wysiwyg() {
        $data = $this->website_widget->GetWidgetData();
        if(!empty($data->wysiwyg_text)){
            $html = $data->wysiwyg_text;
        }elseif(!empty($data->selected_wysiwyg_text)){
            $html = $data->selected_wysiwyg_text;
        }else{
            $html = '';
        }

        return $html;
    }

    public function tabsOld() {

        $data = $this->website_widget->GetWidget();
        $widgetParent = WidgetService::widget_by_parent_id($data->np_widget_id);
        $widgetParent[0]->active_tab = 1;

        $view_data['tabs_view'] = '';
        foreach ($widgetParent as $key => $subWidget) {
            $widget_data = new WebsiteWidget($subWidget);
            $widget_data->active = isset($subWidget->active_tab) ? $subWidget->active_tab : 0;
            $subWidgetData = json_decode($subWidget->widget_data);
            $view_data['tabs_menu'][$key]['name'] = $subWidgetData->name;
            $view_data['tabs_menu'][$key]['id'] = $subWidget->np_widget_id;
            $view_data['tabs_menu'][$key]['active'] = isset($subWidget->active_tab) ? $subWidget->active_tab : 0;
            $view_data['tabs_menu'][$key]['articles'] = $widget_data->view_data->articles;
            $view_data['tabs_view'] .= View('theme::widgets.' . WidgetService::widget_view_name($subWidget->widget_style), ['widget_data' => $widget_data]);
        }

        return $view_data;
    }

    public function tabs() {

        $data = $this->website_widget->GetWidget();
        $widgetParent = WidgetService::widget_by_parent_id($data->np_widget_id);
        $widgetParent[0]->active_tab = 1;
        $view_data['tabs_view'] = array();
        foreach ($widgetParent as $key => $subWidget) {
            $widget_data_articles = new WebsiteWidget($subWidget);
            $widget_data = WidgetService::widget($subWidget)->render();
            $sub_widget_data = !empty($subWidget['widget_data']) ? json_decode($subWidget['widget_data']) : [];
            $subWidgetData = json_decode($subWidget->widget_data);
            $view_data['tabs_menu'][$key]['name'] = $subWidgetData->name;
            $view_data['tabs_menu'][$key]['id'] = $subWidget->np_widget_id;
            $view_data['tabs_menu'][$key]['active'] = isset($subWidget->active_tab) ? $subWidget->active_tab : 0;
            $view_data['tabs_menu'][$key]['sub_widget_data'] = $sub_widget_data;
            if (isset($widget_data_articles->view_data->articles)) {
                $view_data['tabs_menu'][$key]['articles'] = $widget_data_articles->view_data->articles;
            }
            $view_data['tabs_view'][] = $widget_data;
        }
        return $view_data;
    }

    public function articleDetails() {
        return [];
    }

    public function relatedArticles($pagination = false) {

        $article = PageService::Article();
        if (empty($article) && ThemeService::ConfigValue('ARCHIVE_CUTOFF') > 0) {
            $article = PageService::ArticleArchive();
        }

        $limit = $this->website_widget->GetLimit();
        $offset = $this->website_widget->GetOffset();

        $section_id = $article->section_id;
        $subsection_id = $article->sub_section_id;


        if (ThemeService::ConfigValue('ENABLE_MAX_PUBLISH_TIME')) {
            $order_by = 'max_publish_time';
        } else {
            $order_by = 'publish_time';
        }


        // Get articles
        $append_conditions = [];

        // Skipinng Breaking News Articles
        if (ThemeService::ConfigValue('BREAKING_NEWS_SKIP')) {
            $append_conditions[] = ['breaking_news', '<>', 1];
        }

        if (ThemeService::ConfigValue('POLL_SECTION_ID')) {
            $append_conditions[] = ['section_id', '<>', ThemeService::ConfigValue('POLL_SECTION_ID')];
        }
        
        if (ThemeService::ConfigValue('POLL_SUB_SECTION_ID')) {
            $append_conditions[] = ['sub_section_id', '<>', ThemeService::ConfigValue('POLL_SUB_SECTION_ID')];
        }

        //$append_conditions[] = ['is_old_article', '=', 0];

        $append_conditions[] = ['cms_article_id', '<>', $article->cms_article_id];

        if (ThemeService::ConfigValue('MEDIA_GALLERY_FLAG')) {
            $append_conditions[] = ['media_gallery_flag', '=', 0];
        }


        $articles_query = article::with(['image']);

        if (ThemeService::ConfigValue('ENABLE_MAX_PUBLISH_TIME')) {
            $articles_query = $articles_query->where('max_publish_time', '<', DB::raw(ThemeService::ConfigValue('GIVEN_TIME')));
        } else {
            $articles_query = $articles_query->where('publish_time', '<', DB::raw(ThemeService::ConfigValue('GIVEN_TIME')));
        }


        if ($section_id) {
            $articles_query = $articles_query->where('section_id', $section_id);
        }


        if ($subsection_id) {
            $articles_query = $articles_query->where('sub_section_id', $subsection_id);
        }

        foreach ($append_conditions as $c) {
            $articles_query = $articles_query->where($c[0], $c[1], $c[2]);
        }

        if ($pagination) {
            $articles_query = $articles_query->orderBy($order_by, 'desc');
            $articles = $articles_query->paginate($limit, ['*'], 'pgno');
            ;
        } else {
            $articles_query = $articles_query->orderBy($order_by, 'desc')
                ->offset($offset)
                ->limit($limit);
            $articles = $articles_query->get();
        }
        return $articles;
    }

    public function dynamicRelatedArticles() {

        $data = $this->website_widget->GetWidgetData();
        $limit = $this->website_widget->GetLimit();
        $offset = $this->website_widget->GetOffset(); 

        $article = PageService::Article();
        $time_field = (ThemeService::ConfigValue('ENABLE_MAX_PUBLISH_TIME')) ? 'max_publish_time' : 'publish_time';


        $related_ids = array();
        $results = array();
        $articles = array();
//       1- check if article has related articles in article table
        if (!empty($article->related_articles_ids)) {
            $related_articles_in_article_table = explode(',', $article->related_articles_ids);
            foreach ($related_articles_in_article_table as $article_id) {
                $related_ids [] = $article_id;
            }
        }
//        2- check related_articles table
        if (ThemeService::ConfigValue('RELATED_ARTICLES_TYPE') && (ThemeService::ConfigValue('RELATED_ARTICLES_TYPE') == 'CMS')) {
            if (count($related_ids) < $limit) {
                $relatedArticle = related_articles::where('article_id', $article->cms_article_id)->first();
                if ($relatedArticle && !empty($relatedArticle->related_ids)) {
                    $relatedArticlesCMSids = explode(",", $relatedArticle->related_ids);
                    foreach ($relatedArticlesCMSids as $article_id) {
                        if (count($related_ids) < $limit) {
                            $NPRecord = article::find($article_id);
                            if ($NPRecord) {
                                $related_ids [] = $NPRecord->np_article_id;
                            }
                        }
                    }
                }
            }
        } else {
            if (count($related_ids) < $limit) {
                $relatedArticle = related_articles::where('article_id', $article->np_article_id)->first();
                if ($relatedArticle && !empty($relatedArticle->related_ids)) {
                    $relatedArticlesNPids = explode(",", $relatedArticle->related_ids);
                    foreach ($relatedArticlesNPids as $article_id) {
                        if (count($related_ids) < $limit) {
                            $related_ids [] = $article_id;
                        }
                    }
                }
            }
        }





//        3- add config and check in sphinx
        if (count($related_ids) < $limit && ThemeService::ConfigValue('SPHINX_RELATED_ARTICLES_COLUMN')) {
            $sphinx_limit = $limit - count($related_ids);
            $sphinx_field = ThemeService::ConfigValue('SPHINX_RELATED_ARTICLES_COLUMN');
            if (!empty($article->{$sphinx_field})) {
                $sphinx = new SphinxSearch();
                $index_from = 0;
                $query = "@" . $sphinx_field . " '" . str_replace(",", "' | @" . $sphinx_field . " '", $article->{$sphinx_field}) . "'";
                $sphinx->search($query, ThemeService::ConfigValue('WEBSITE_FULL'));
                $sphinx->limit($sphinx_limit, $index_from, 1000000, 0);
                $sphinx->setMatchMode(\Sphinx\SphinxClient::SPH_MATCH_EXTENDED2);
                $sphinx->setSortMode(\Sphinx\SphinxClient::SPH_SORT_EXTENDED, "publish_time DESC");
                $results = $sphinx->get();
                if (isset($results['matches'])) {
                    $result_count = $results['total'];
                    foreach ($results['matches'] as $key => $match) {
                        if ($match['attrs']['is_old_article'] == 0 && ($match['attrs']['np_article_id'] != $article->np_article_id)) {
                            $related_ids[] = $match['attrs']['np_article_id'];
                        }
                    }
                }
            }
        }
//        4- section or sub section articles
        if (count($related_ids) < $limit) {
            $section_articles_limit = $limit - count($related_ids);
            $section_articles = article::where('np_article_id', '<>', $article->np_article_id)
                ->where('section_id', $article->section_id)
                ->where('sub_section_id', $article->sub_section_id)
                ->whereNotIn('np_article_id', $related_ids)
                ->where($time_field, '<', DB::raw(ThemeService::ConfigValue('GIVEN_TIME')))
                ->orderBy($time_field, 'desc')
                ->limit($section_articles_limit)
                ->get();
            foreach ($section_articles as $section_article) {
                $related_ids[] = $section_article->np_article_id;
            }
        }

        if (count($related_ids) > 0) {
            $articles = article::whereIn('np_article_id', $related_ids);
            if (isset($article->np_article_id)) {
                $articles = $articles->where('np_article_id', '<>', $article->np_article_id);
            }
            $articles = $articles->orderBy($time_field, 'desc')
                ->offset($offset)
                ->limit($limit)
                ->get();
        }

        $count_articles = count($articles);
        if ($limit > $count_articles) {
            $count_fromarchive = $limit - $count_articles;
            $theme_controller_class = 'Themes\\' . ThemeService::Name() . '\\controllers\\' . CommonController::controller_class_name();
            if (class_exists($theme_controller_class)) {
                $themeController = new $theme_controller_class();
                if (method_exists($themeController, 'getArticlesArchiveWithWidgetDynamicRelatedArticles')){
                    $function_name = 'getArticlesArchiveWithWidgetDynamicRelatedArticles';
                    $articles_archive = $themeController->$function_name($count_fromarchive, $article->section_id, $article->sub_section_id,$this->website_widget->np_widget_id,$offset);
                    foreach ($articles_archive as $article_arch) {
                        $articles[] = $themeController->getArchiveArticle($article_arch);
                    }
                    $articles = collect($articles);
                }
            }
        }
        return $articles;
    }

    public function latestNews(){
        $data = $this->website_widget->GetWidgetData();
        $articles_query_array = [];
        if (ThemeService::ConfigValue('ENABLE_MAX_PUBLISH_TIME')) {
            $order_by = 'max_publish_time';
        } else {
            $order_by = 'publish_time';
        }

        $quit_funtion = true;
        if(!empty($data->limit_stories)){
            foreach($data->limit_stories as $section_id => $limit){
                if($limit){
                    $quit_funtion = false;
                    if (ThemeService::ConfigValue('ENABLE_MAX_PUBLISH_TIME')) {
                        $articles_query = article::with(['image'])->where('max_publish_time', '<', DB::raw(ThemeService::ConfigValue('GIVEN_TIME')));
                    } else {
                        $articles_query = article::with(['image'])->where('publish_time', '<', DB::raw(ThemeService::ConfigValue('GIVEN_TIME')));
                    }

                    $articles_query = $articles_query->where('section_id',$section_id);

                    if (ThemeService::ConfigValue('ENABLE_MAX_PUBLISH_TIME')) {
                        $articles_query->orderBy('max_publish_time','desc');
                    } else {
                        $articles_query->orderBy('publish_time','desc');
                    }

                    $articles_query = $articles_query->limit($limit);
                    $articles_query_array[] = $articles_query;
                }
            }

            if($quit_funtion){
                return array();
            }

            $union_query = '';
            foreach($articles_query_array as $key => $limit_stories_query){
                if($key !=0){
                    $union_query->unionAll($limit_stories_query);
                }
                if($key ==0){
                    $union_query = $limit_stories_query;
                }
            }

            if (ThemeService::ConfigValue('ENABLE_MAX_PUBLISH_TIME')) {
                $union_query->orderBy('max_publish_time','desc');
            } else {
                $union_query->orderBy('publish_time','desc');
            }

            $articles = $union_query->get();
        }

        return $articles;
    }

    public function menu() {
        $menu_items = MenuService::menu($this->website_widget->GetWidgetDataValue('menu_id'));

        return $menu_items;
    }

    public function standaloneImageWidget() {

        $data = $this->website_widget->GetWidgetData();

        if(!empty($data->image_widget_name)){
            $html = ThemeService::ConfigValue('CDN_URL') . "uploads/images/widgets/".$this->website_widget->np_widget_id."_".$data->image_widget_name;
        }
        else{
            $html = '';
        }

        return $html;
    }

    public static function sort_by_widget_order($article_ids, $articles_order, Collection $articles) {
        $sorted_articles = [];
        $article_id_widget_order_map = [];
        $article_id_arr = [];
        for ($i = 0; $i < count($article_ids); $i++) {
            if(isset($article_ids[$i]) && isset($articles_order[$i]))
            {
                $article_id_widget_order_map[$article_ids[$i]] = $articles_order[$i];
                $article_id_arr[$articles_order[$i]] = $article_ids[$i];
            }
        }
        foreach ($articles as $article) {
            if(in_array($article->np_article_id, $article_id_arr)){
                $article->widget_order = $article_id_widget_order_map[$article->np_article_id];
                $sorted_articles[$article_id_widget_order_map[$article->np_article_id]] = $article;
            }
        }
        ksort($sorted_articles);

        return collect(array_values($sorted_articles));
    }

}
