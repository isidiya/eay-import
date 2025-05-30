<?php

namespace App\Http\Controllers;

use App\Models\article;
use App\Models\article_multi_section;
use App\Models\author;
use App\Models\bootstrap_rows;
use App\Models\menu_item;
use App\Models\page;
use App\Models\image;
use App\Models\article_author;
use App\Models\pressrelease_contacts;
use App\Models\section;
use App\Models\sub_section;
use App\Models\article_archive;
use App\Models\widget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Layout\Website\Helpers\DateTimeHelper;
use Layout\Website\Models\PageBootstrap;
use Layout\Website\Services\PageService;
use Layout\Website\Services\ThemeService;
use Layout\Website\Services\WidgetService;
use sngrl\SphinxSearch\SphinxSearch;

class ApiController extends Controller {

    public function __construct() {
        
    }

    public function getArticleData(Request $request) {
        if (is_numeric($request->np_article_id)) {
            $articles_ids = array($request->np_article_id);
        } else {
            $articles_ids = explode(',', $request->np_article_id);
        }
        $articles = article::whereIn('np_article_id', $articles_ids)->get();
        $format_type = $request->format_type ?: 'json';
        $articles_array = [];
        if (!empty($articles) && $format_type == 'json') {
            $article_page = 1;
            $articles_array = self::updateJsonArticles($articles, $request, $article_page);
            if(!empty($articles_array) && count($articles_array) > 0 && $request->related_article_image){
                if(!empty($articles_array[0]->related_article)){
                    $articles_array[0]->related_article = self::getArticlesImage($articles_array[0]->related_article, $request);
                }
            }
            if(!empty($articles_array) && count($articles_array) > 0 && $request->get_recommended && $request->related_article_image){
                if(!empty($articles_array[0]->recommended_article)){
                    $articles_array[0]->recommended_article = self::getArticlesImage($articles_array[0]->recommended_article, $request);
                }
            }
            return response()->json(['response' => 'success', 'articles' => $articles_array]);
        } else {
            return response()->json(['response' => 'failed']);
        }
    }

    public function getMenuItemsData(Request $request) {
        $menu_items = menu_item::find_by_menu_id($request->np_menu_id);
        $format_type = $request->format_type ?: 'json';
        if (!empty($menu_items) && $format_type == 'json') {
            foreach ($menu_items as $menu_item) {
                $menu_item['menu_items_link'] = $menu_item->seo_url;
            }
            return response()->json(['response' => 'success', 'menu_items' => $menu_items]);
        } else {
            return response()->json(['response' => 'failed']);
        }
    }

    public static function getWidgetData(Request $request) {
        $widget = widget::find_np($request->widget_id);
        $format_type = $request->format_type ?: 'json';
        $widget_data = [];
        if (!empty($widget)) {

            $page = page::find_np($widget->page_id);
            if (!empty($request->np_article_id)) {
                if (!empty($request->is_old_article) && $request->is_old_article) {
                    $article_archive = article_archive::find_np($request->np_article_id);
                    if (!empty($article_archive)) {
                        $theme_controller_class = 'Themes\\' . ThemeService::Name() . '\\controllers\\' . CommonController::controller_class_name();
                        if (class_exists($theme_controller_class)) {
                            $themeController = new $theme_controller_class();
                            if (method_exists($themeController, 'getArchiveArticle')) {
                                $function_name = 'getArchiveArticle';
                                $article = $themeController->$function_name($article_archive);
                            }
                        }
                    }
                } else {
                    $article = article::find_np($request->np_article_id);
                }

                if (!empty($article)) {
                    PageService::SetArticle($article);
                    $page->is_article_page = true;
                }
            }
            PageService::SetPage($page);

            $page_bootstrap = new PageBootstrap($page);
            $page_bootstrap->render();
            $widget_data['widget_data_info'] = WidgetService::widget_by_widget_data($widget);
            $widget_data['widget_data_info']->handleRender();



            if (!empty($widget_data['widget_data_info']) && !empty($widget_data['widget_data_info']->articles)) {
                $widget_data['widget_data_info']->articles = self::updateJsonArticles($widget_data['widget_data_info']->articles, $request);
            } elseif (!empty($widget_data['widget_data_info']) && !empty($widget_data['widget_data_info']->view_data) && !empty($widget_data['widget_data_info']->view_data->articles)) {
                $widget_data['widget_data_info']->view_data->articles = self::updateJsonArticles($widget_data['widget_data_info']->view_data->articles, $request);
            }

            $widget->widget_data = stripslashes($widget->widget_data);
            $widget->widget_data = json_decode($widget->widget_data, 1);
            $widget_data['widget_info'] = $widget;
            $widget_data['page_info'] = $page;
        }
        if (!empty($widget_data) && $format_type == 'json') {
            return response()->json(['response' => 'success', 'widget_data' => $widget_data]);
        } else {
            return response()->json(['response' => 'failed']);
        }
    }

    public static function getAllPageData(Request $request) {
        $page = page::find_np(0);
        $format_type = $request->format_type ?: 'json';

        $page_data['page'] = $page;
        if (!empty($page_data) && $format_type == 'json') {
            return response()->json(['response' => 'success', 'page_data' => $page_data]);
        } else {
            return response()->json(['response' => 'failed']);
        }
    }

    public static function getPageData(Request $request) {
        $page_data = [];
        $format_type = $request->format_type ?: 'json';

        $page = page::find_np($request->np_page_id);
        if (!empty($page)) {
            if (strtolower($page->page_title) == 'article') {
                $page->is_article_page = true;
            }
            PageService::SetPage($page);
            $page_bootstrap = new PageBootstrap($page);
            $page_bootstrap->render();
            $page_data['page'] = $page;
            if (!empty($request->get_widgets) && $request->get_widgets) {
                $bootstrap_rows = bootstrap_rows::where('page_id', $request->np_page_id)->first();
                if (!empty($bootstrap_rows) && !empty($bootstrap_rows->bootstrap_tags)) {
                    $bootstrap_tags = json_decode($bootstrap_rows->bootstrap_tags);
                    if (!empty($bootstrap_tags)) {
                        $widgets_order = [];
                        foreach ($bootstrap_tags as $bootstrap_key => $bootstrap_tag) {
                            foreach ($bootstrap_tag as $row_key => $row) {
                                foreach ($row as $row_key => $widgetId) {
                                    if (is_integer($widgetId) && is_numeric($row_key)) {
                                        $widgets_order[] = $widgetId;
                                    }
                                }
                            }
                        }
                    }

                    if (!empty($widgets_order)) {
                        $widgets = widget::whereIn('np_widget_id', $widgets_order)->orderBy(DB::raw('FIELD(np_widget_id,' . implode(',', $widgets_order) . ')'))->get();
                    }

                    if (!empty($widgets)) {
                        foreach ($widgets as $widget_key => $widget) {
                            if (!empty($widget)) {
                                $page_data['widgets'][$widget_key]['widget_data_info'] = WidgetService::widget_by_widget_data($widget);
                                if (!empty($page_data['widgets'][$widget_key]['widget_data_info']) && !empty($page_data['widgets'][$widget_key]['widget_data_info']->view_data) && !empty($page_data['widgets'][$widget_key]['widget_data_info']->view_data->articles)) {
                                    if (!empty($request->get_articles)) {
                                        $page_data['widgets'][$widget_key]['widget_data_info']->view_data->articles = self::updateJsonArticles($page_data['widgets'][$widget_key]['widget_data_info']->view_data->articles, $request);
                                    } else {
                                        unset($page_data['widgets'][$widget_key]['widget_data_info']->view_data->articles);
                                    }
                                }
                                $widget->widget_data = stripslashes($widget->widget_data);
                                $widget->widget_data = json_decode($widget->widget_data, 1);
                                $page_data['widgets'][$widget_key]['widget_info'] = $widget;
                            }
                        }
                    }
                }
            }
        }

        if (!empty($page_data) && $format_type == 'json') {
            return response()->json(['response' => 'success', 'page_data' => $page_data]);
        } else {
            return response()->json(['response' => 'failed']);
        }
    }

    public static function getAuthorData(Request $request) {

        $format_type = $request->format_type ?: 'json';
        $np_author_id = $request->np_author_id;
        $limit = ($request->limit && $request->limit <= 100) ? $request->limit : 20;
        $author = author::find_np($np_author_id);

        if ($request->get_articles && $np_author_id) {
            $articles = article::where('author_id', $np_author_id)->paginate($limit, ['*'], 'page');
            $author['articles'] = $articles;
        }

        if (!empty($author) && $format_type == 'json') {
            return response()->json(['response' => 'success', 'author_data' => $author]);
        } else {
            return response()->json(['response' => 'failed']);
        }
    }

    public static function getArticlesData(Request $request) {

        $format_type = $request->format_type ?: 'json';
        $np_section_id = $request->np_section_id;
        $np_sub_section_id = $request->np_sub_section_id;
        $limit = ($request->limit && $request->limit <= 100) ? $request->limit : 20;
        $order_by = $request->order_by ? $request->order_by : 'publish_time';

        $skip = $request->skip;
        $page = $request->page ?? "1";

        if ($skip) {
            $skip_articles = article::select('np_article_id')->where('max_publish_time', '<', DB::raw(ThemeService::ConfigValue('GIVEN_TIME')));
            if ($np_section_id) {
                $skip_articles = $skip_articles->where('section_id', $np_section_id);
            }
            if ($np_sub_section_id) {
                $skip_articles = $skip_articles->where('sub_section_id', $np_sub_section_id);
            }
            $skip_articles = $skip_articles->orderBy($order_by, 'DESC')->limit($skip)->get();
            if (!empty($skip_articles)) {
                $skip_article_ids = array_pluck($skip_articles, 'np_article_id');
            }
        }


        if ($np_section_id) {

            $articles = article::where('section_id', $np_section_id);
            if ($np_sub_section_id) {
                $articles = $articles->where('sub_section_id', $np_sub_section_id);
            }
            if (!empty($skip_article_ids)) {
                $articles = $articles->whereNotIn('np_article_id', $skip_article_ids);
            }
            $articles = $articles->where('max_publish_time', '<', DB::raw(ThemeService::ConfigValue('GIVEN_TIME')))->orderBy($order_by, 'DESC')->paginate($limit, ['*'], 'page', $page);
        } else {
            $articles = article::where('max_publish_time', '<', DB::raw(ThemeService::ConfigValue('GIVEN_TIME')));
            if (!empty($skip_article_ids)) {
                $articles = $articles->whereNotIn('np_article_id', $skip_article_ids);
            }
            $articles = $articles->orderBy($order_by, 'DESC')->paginate($limit, ['*'], 'page', $page);
        }


        if (!empty($articles) && !empty($articles[0]) && $format_type == 'json') {
            $articles = self::updateJsonArticles($articles, $request);
            return response()->json(['response' => 'success', 'articles_data' => $articles]);
        } else {
            return response()->json(['response' => 'failed']);
        }
    }

    public static function getSectionData(Request $request) {

        $format_type = $request->format_type ?: 'json';
        $np_section_id = $request->np_section_id;
        $np_sub_section_id = $request->np_sub_section_id;
        $limit = ($request->limit && $request->limit <= 100) ? $request->limit : 20;
        $page = $request->page ? $request->page : 1;

        $section = section::find_np($np_section_id, true)->toArray();
        if (!empty($section) && !empty($section['np_section_id']) && $np_sub_section_id) {
            $sub_section = sub_section::find_np($np_sub_section_id)->toArray();
            if (!empty($sub_section) && !empty($sub_section['np_sub_section_id'])) {
                $section['sub_sections'] = $sub_section;
            } else {
                $section['sub_sections'] = [];
            }
        }

        if ($request->get_articles && $np_section_id) {
            $articles = article::where('section_id', $np_section_id);
            if ($np_sub_section_id) {
                $articles = $articles->where('sub_section_id', $np_sub_section_id);
            }
            $articles = $articles->paginate($limit, ['*'], 'page');
            $section['articles'] = $articles;
        }

        if (!empty($section) && !empty($section['np_section_id']) && $format_type == 'json') {
            return response()->json(['response' => 'success', 'section_data' => $section]);
        } else {
            return response()->json(['response' => 'failed']);
        }
    }

    public static function getSearchData(Request $request) {

        $format_type = $request->format_type ?: 'json';
        if (empty(ThemeService::ConfigValue('SEARCH_WIDGET_ID'))) {
            return response()->json(['response' => 'failed', 'message' => 'SEARCH WIDGET ID IS REQUIRED']);
        }

        $widget = widget::find_np(ThemeService::ConfigValue('SEARCH_WIDGET_ID'));
        $widget_data = [];
        if (!empty($widget)) {
            $widget_data['widget_data_info'] = WidgetService::widget_by_widget_data($widget);
            $widget_data['widget_data_info']->handleRender();
            if (!empty($widget_data['widget_data_info']) && !empty($widget_data['widget_data_info']->articles)) {
                $articles = self::updateJsonArticles($widget_data['widget_data_info']->articles, $request);
            }
        }
        if (!empty($articles) && $format_type == 'json') {
            return response()->json(['response' => 'success', 'articles_data' => $articles]);
        } else {
            return response()->json(['response' => 'failed']);
        }
    }

    public static function updateJsonArticles($articles, $request, $article_page = 0) {
        if(!empty($articles)){
            foreach ($articles as $article_key => $article) {
                if (!empty($articles[$article_key]->image_path) && !empty($request->image_thumbs)) {
                    if($request->embed){
                        $image_view = $articles[$article_key]->image_src(true, $request->image_thumbs, $request->embed, $request->is_focal_point, 'article', false);
                    }else{
                        $image_view = $articles[$article_key]->image_src(true, $request->image_thumbs, false, $request->is_focal_point, 'article', false);
                    }
                    if (!is_string($image_view) && !empty($image_view)) {
                        $articles[$article_key]->image_html_view = $image_view->render();
                    }
                }
                if (!empty($request->author_image) && $articles[$article_key]->author_id > 0) {
                    $articles[$article_key]->author_image = $articles[$article_key]->author_image_src;
                }
                $articles[$article_key]->custom_fields = json_decode($article->article_custom_fields);
                $articles[$article_key]->permalink = is_numeric(strpos('http', $articles[$article_key]->permalink)) ? $articles[$article_key]->permalink : ThemeService::ConfigValue('APP_URL') . $articles[$article_key]->permalink;
                if(!empty($articles[$article_key]->image_path)){
                    $articles[$article_key]->image_path = stripslashes($article->image_path);
                    $articles[$article_key]->image_path = json_decode($article->image_path);
                    if (isset($articles[$article_key]->image_path->image_cropping)) {
                        $articles[$article_key]->image_path->image_cropping = json_decode($articles[$article_key]->image_path->image_cropping);
                        foreach ((array)$articles[$article_key]->image_path->image_cropping as $key => $img_cropped) {
                            if ($key <> "original_image" && $key <> "focal_point") {
                                $articles[$article_key]->image_path->image_cropping->$key->path = str_replace(basename($articles[$article_key]->image_path->image_path), "thumbs/" . $key . "/" . basename($articles[$article_key]->image_path->image_path), $articles[$article_key]->image_path->image_path);
                            }
                        }
                    }
                }else{
                    $articles[$article_key]->image_path = NULL;
                }

                if($request->date_format){
                    if($request->lang){
                        $articles[$article_key]->formatted_date = DateTimeHelper::getDisplayDate(strtotime($articles[$article_key]->publish_time),false,false,$request->lang,array('date_format' => $request->date_format));
                    }else {
                        $articles[$article_key]->formatted_date = DateTimeHelper::getDisplayDate(strtotime($articles[$article_key]->publish_time), false, false, true, array('date_format' => $request->date_format));
                    }
                }
                if($request->with_section){
                    $articles[$article_key]->section = $article->section;
                }
                //Article Body
                $articles[$article_key]->article_body = $article->article_body_info();
                $articles[$article_key]->article_body_clean = $articles[$article_key]->article_body_clean();
                $articles[$article_key]->article_authors = self::getArticleAuthors($article);
                $articles[$article_key]->article_sections = self::getArticleSections($article);
                //Related Articles
                if ($article_page) {
                    $limit = 5;
                    if($request->related_limit){
                        $limit = $request->related_limit;
                    }
                    $articles[$article_key]->related_article = ArticleController::getCustomRelatedArticles($article, $limit);
                    if($request->get_recommended){
                        $articles[$article_key]->recommended_article = ArticleController::getRecommendedArticlesSection($article, $limit);
                    }
                }
            }
        }
        return $articles;
    }

    public static function getArticlesImage($articles, $request) { //to handle related articles images
        if(!empty($articles)){
            foreach ($articles as $article_key => $article) {
                if (!empty($articles[$article_key]->image_path) && !empty($request->image_thumbs)) {
                    if($request->embed){
                        $image_view = $articles[$article_key]->image_src(true, $request->image_thumbs, $request->embed, $request->is_focal_point, 'article', false);
                    }else{
                        $image_view = $articles[$article_key]->image_src(true, $request->image_thumbs, false, $request->is_focal_point, 'article', false);
                    }
                    if (!is_string($image_view) && !empty($image_view)) {
                        $articles[$article_key]->image_html_view = $image_view->render();
                    }
                }
            }
        }
        return $articles;
    }

    public static function getAllBrands(Request $request) {
        $format_type = $request->format_type ?: 'json';
        $brands_data = \App\Models\brands::all();
        if (!empty($brands_data) && $format_type == 'json') {
            return response()->json(['response' => 'success', 'brands_data' => $brands_data]);
        } else {
            return response()->json(['response' => 'failed']);
        }
    }

    public static function getAllAgencies(Request $request) {
        $agencies_data = \App\Models\agency::all();
        $format_type = $request->format_type ?: 'json';
        if (!empty($agencies_data) && $format_type == 'json') {
            return response()->json(['response' => 'success', 'agencies_data' => $agencies_data]);
        } else {
            return response()->json(['response' => 'failed']);
        }
    }

    public static function getContactsRelatedToAgency(Request $request) {
        $format_type = $request->format_type ?: 'json';
        $agency_contact_data = \App\Models\pressrelease_contacts::get_agency_contacts($request->agency_id);
        if (!empty($agency_contact_data) && $format_type == 'json') {
            return response()->json(['response' => 'success', 'agency_contact_data' => $agency_contact_data]);
        } else {
            return response()->json(['response' => 'failed']);
        }
    }

    public static function getCustomFieldsData(Request $request) {

        $format_type = $request->format_type ?: 'json';
        $data_key = $request->data_key;
        $data_value = $request->data_value;
        $limit = $request->limit ?: 10;

        if (empty($data_key) && empty($data_value)) {
            if ($format_type == 'json') {
                return response()->json(['response' => 'failed', 'message' => 'Data Key or Data Value is required']);
            }
        }

        $cusom_field_query = DB::table('web_data_values');
        if (!empty($data_key)) {
            if (strpos($data_key, ',') !== false) {
                $cusom_field_query = $cusom_field_query->where('data_key', $data_key);
            } else {
                $data_key_array = explode(',', $data_key);
                $cusom_field_query = $cusom_field_query->whereIn('data_key', $data_key_array);
            }
        }

        if (!empty($data_value)) {
            if (strpos($data_value, ',') !== false) {
                $cusom_field_query = $cusom_field_query->where('data_value', $data_value);
            } else {
                $data_value_array = explode(',', $data_value);
                $cusom_field_query = $cusom_field_query->whereIn('data_value', $data_value_array);
            }
        }

        $custom_fields = $cusom_field_query->limit($limit)->get();
        if (!empty($custom_fields)) {
            $cms_article_ids = array_pluck($custom_fields, 'cms_article_id');
            $custom_fields_articles = article::whereIn('cms_article_id', $cms_article_ids)->get();
        }

        if (!empty($custom_fields) && count($custom_fields) < $limit && !empty($data_value)) {
            $sphinx = new SphinxSearch();
            $sphinx_limit = $limit - count($custom_fields);
            if (strpos($data_value, ',') !== false) {
                $sphinx->search($data_value, ThemeService::ConfigValue('WEBSITE_FULL') . "," . ThemeService::ConfigValue('WEBSITE_ARCHIVE_FULL') . "," . ThemeService::ConfigValue('WEBSITE_DELTA'));
            } else {
                $sphinx->search($data_value, ThemeService::ConfigValue('WEBSITE_FULL') . "," . ThemeService::ConfigValue('WEBSITE_ARCHIVE_FULL') . "," . ThemeService::ConfigValue('WEBSITE_DELTA'));
            }

            $sphinx->setMatchMode(\Sphinx\SphinxClient::SPH_MATCH_EXTENDED);
            $sphinx->limit($sphinx_limit, 0, 10000, 0);
            $sphinx->setSortMode(\Sphinx\SphinxClient::SPH_SORT_EXTENDED, "publish_time DESC");
            $results = $sphinx->get();
            if (!empty($results['matches'])) {
                $result_count = $results['total'];
                foreach ($results['matches'] as $key => $match) {
                    $ids[] = $key;
                    if ($match['attrs']['is_old_article'] == 0) {
                        $article_ids[] = $key;
                    } else {
                        $article_archive_ids[] = $key;
                    }
                }
                if (count($article_ids) > 0) {
                    $articles = array();
                    $sphinx_articles = article::whereIn('cms_article_id', $article_ids)->where('max_publish_time', '<', DB::raw(ThemeService::ConfigValue('GIVEN_TIME')))->orderByRaw('FIELD(cms_article_id,' . implode(",", $article_ids) . ')')->get();
                }
                $path_url = 'custom_fields';
                $sphinx_articles_ = new \Illuminate\Pagination\LengthAwarePaginator($sphinx_articles, $result_count, $sphinx_limit, 1, ['path' => url($path_url)]);
                $next_page_url = $sphinx_articles_->nextPageUrl();
                $next_step_url = $sphinx_articles_->nextStepUrl();
                $total_matches = $result_count;
                $view_data['total_matches'] = $result_count;
            }
        }

        if (!empty($sphinx_articles)) {
            $articles = $custom_fields_articles->merge($sphinx_articles);
        } else {
            $articles = $sphinx_articles;
        }


        if (!empty($articles) && $format_type == 'json') {
            return response()->json(['response' => 'success', 'articles' => $articles]);
        } else {
            return response()->json(['response' => 'failed']);
        }
    }

    public static function workWithArticleBody($article) {
        $bodytext = $article->article_body;

        $re = "/(\*\*media\[(\d|,)*]\*\*)/";
        $matches = array();
        preg_match_all($re, $bodytext, $matches);
        if (!empty($matches[0])) {
            $matches = $matches[0];
            $json_media = array();
            for ($k = 0; $k < count($matches); $k++) {
                $strToSearch = $matches[$k];
                $npImageIds = str_replace("**media[", "", $matches[$k]);
                $npImageIds = str_replace("]**", "", $npImageIds);
                $strToReplace = '';
                if ($npImageIds) {
                    $npImageIds = explode(",", $npImageIds);
                    $images = image::whereIn('np_image_id', $npImageIds)->where('image_is_deleted', 0)->get();
                    if ($images) {
                        foreach ($images as $key => $image) {
                            $json_media[] = array(
                                "url" => ThemeService::ConfigValue("APP_URL") . $image->image_path,
                                "caption" => $image->image_caption
                            );
                        }
                        $strToReplace = "**media[" . json_encode($json_media) . "]**";
                    }
                    if (isset($images[0])) {
                        $value = str_replace($strToSearch, $strToReplace, $bodytext);
                        $bodytext = $value;
                    } else {
                        /* if idForImage exist but image not exist in table remove **media[idForImage]** */
                        $value = str_replace($strToSearch, '', $bodytext);
                        $bodytext = $value;
                    }
                } else {
                    $bodytext = str_replace($strToSearch, '', $bodytext);
                }
            }
        }

        $re = "/(\*\*carousel\[(\d|,)*]\*\*)/";
        $matches = array();
        preg_match_all($re, $bodytext, $matches);
        if (!empty($matches[0])) {
            $matches = $matches[0];
            $json_media = array();
            for ($k = 0; $k < count($matches); $k++) {
                $strToSearch = $matches[$k];
                $npImageIds = str_replace("**carousel[", "", $matches[$k]);
                $npImageIds = str_replace("]**", "", $npImageIds);


                if ($npImageIds) {
                    $npImageIds = explode(",", $npImageIds);
                    $images = image::whereIn('np_image_id', $npImageIds)->where('image_is_deleted', 0)->orderBy('media_order')->get();
                    if ($images) {

                        foreach ($images as $key => $image) {
                            $json_media[] = array(
                                "url" => ThemeService::ConfigValue("APP_URL") . $image->image_path,
                                "caption" => $image->image_caption
                            );
                        }
                        $strToReplace = "**carousel[" . json_encode($json_media) . "]**";
                    }
                    if (isset($images[0])) {
                        $value = str_replace($strToSearch, $strToReplace, $bodytext);
                        $bodytext = $value;
                    } else {
                        /* if idForImage exist but image not exist in table remove **media[idForImage]** */
                        $value = str_replace($strToSearch, '', $bodytext);
                        $bodytext = $value;
                    }
                }
            }
        }
        return $bodytext;
    }

    public static function getArticleAuthors($article) {
        $authors = article_author::select("np_author_id as author_id", "author_name")->where("np_article_id", $article->np_article_id)->where("np_author_id", ">", 0)->get();
        if (isset($article->custom_fields->author[0]) && isset(json_decode($article->custom_fields->author[0])[0]->city)) {
            $cities = json_decode($article->custom_fields->author[0])[0]->city;
            $custom_author = json_decode($article->custom_fields->author[0])[0]->author;
            $combine = array_combine($custom_author, $cities);
            foreach ($authors as $key => $author) {
                if (in_array($author->author_id, $custom_author)) {
                    $author->city = $combine[$author->author_id];
                }
            }
        }
        return $authors;
    }

    public static function getArticleSections($article) {
        $sections = article_multi_section::where('ams_article_id', $article->cms_article_id)->get();

        return $sections;
    }

    public static function contactSendMail(Request $request){

        if(!empty($request->contact_id && $request->np_article_id)){
            $theme_controller_class = 'Themes\\' . ThemeService::Name() . '\\controllers\\' . CommonController::controller_class_name();
            if (class_exists($theme_controller_class)) {
                $themeController = new $theme_controller_class();
                if (method_exists($themeController, 'contactSendMail')) {
                    $function_name = 'contactSendMail';
                    $result = $themeController->$function_name($request->contact_id,$request->np_article_id);
                    if($result){
                        return 'true';
                    }else{
                        return 'false';
                    }
                }
            }
        }
    }
}
