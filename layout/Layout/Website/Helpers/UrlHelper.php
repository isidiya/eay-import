<?php

/**
 * Created by PhpStorm.
 * User: timur
 * Date: 15.11.2018
 * Time: 13:53
 */

namespace Layout\Website\Helpers;

use App\Models\article;
use App\Models\article_archive;
use App\Models\image;
use App\Models\menu_item;
use App\Models\page;
use App\Models\section;
use App\Models\sub_section;
use Layout\Website\Services\ThemeService;
use App\Http\Controllers\CommonController;

class UrlHelper {

    public static function main_url() {
        return ThemeService::ConfigValue('APP_URL');
    }

    public static function build_seo_url($id, $type = 'article', $seo_title = '', $section_id = '', $sub_section_id = '', $main_url = '',$object = 0) {
        if (empty($main_url)) {
            $main_url = self::main_url();
        }

        if (empty($id)) {
            return $main_url;
        }

        $url = $main_url ;
        switch ($type) {
            case 'author':
                $author_id = $id;
                $author_name = $seo_title;
                if(empty($author_name)){
                    $author = \App\Models\author::find_np($id);
                    $author_name = $author->author_name;
                }
                $theme_controller_class = 'Themes\\'.ThemeService::Name().'\\controllers\\'.  CommonController::controller_class_name();
                if(class_exists($theme_controller_class)){
                    $themeController = new $theme_controller_class();
                    if(method_exists($themeController, 'getAuthorUrl')) {
                        $function_name ='getAuthorUrl';
                        $url .= $themeController->$function_name($author_id,$author_name);
                        break;
                    }
                }

                if ($author_id < 0) {
                    $author_name = str_replace("/", "|", $author_name);
                    $author_name = str_replace("\n", " ", $author_name);
                } else {
                    $author_name = str_replace(" ", "-", $author_name);
                }

                // Special author url name
                if( ThemeService::ConfigValue('AUTHOR_SPECIAL_URL')){
                    $author_page_name = ThemeService::ConfigValue('AUTHOR_SPECIAL_URL');
                }else{
                    $author_page_name = 'author';
                }
                $url .=  $author_page_name . '/' . $author_id . '/';
                $url .=  self::clean_url($author_name);
                break;
            case 'authorPage': {
                    $article_id = $id;
                    $article = article::find($article_id);
                    if (!empty($section_id) && is_numeric($section_id)) {
                        $section = section::find_np($section_id);

                        if (!empty($sub_section_id) && is_numeric($sub_section_id)) {
                            $sub_section = sub_section::find_np($sub_section_id);
                        }
                    } else {
                        $section = $article->section;
                        $sub_section = $article->sub_section;
                    }

                    $section_name = !empty($section) ? $section->section_name : '';
                    $sub_section_name = !empty($sub_section) ? $sub_section->sub_section_name : '';

                    $article_meta_title = !empty($seo_title) ? $seo_title : $article->seo_meta_title;

                    $url .= self::clean_url('author-page/' . $article_id . '/' . $section_name . '/' . $sub_section_name . '/' . $article_meta_title);
                }
                break;
            case 'gallery':
            case 'video':
            case 'article': {

                    if (ThemeService::ConfigValue('SPECIAL_URL')) {
                        $article_id = $id;
						if($object){
							$article =$object;
						}else{
							$article = article::find($article_id);
						}
                        if ($article) {
                            if (strlen($article->permalink) > 4 ) {
                                $url .= $article->permalink;
                            } else {
                                if(!empty($article->sub_section_id)){
                                    $url_build = $article->sub_section_id . "/";
                                }else{
                                    $url_build = $article->section_id . "/";
                                }
                                $url_build .= $article->cms_article_id."-";
                                $url_build .= self::clean_url($article->article_title);

								$theme_controller_class = 'Themes\\'.ThemeService::Name().'\\controllers\\'.  CommonController::controller_class_name();
								if(class_exists($theme_controller_class)){
									$themeController = new $theme_controller_class();
									if(method_exists($themeController, 'updatePermalink')) {
										$function_name ='updatePermalink';
										$url_build = $themeController->$function_name($article);
									}
								}
								$url .= $url_build;
                            }
                        }
                    } else {
                        $custom_path = '';
                        if (ThemeService::ConfigValue('CUSTOM_ARTICLE_PATH')) {
                            $custom_path = $type == 'article' ? ThemeService::ConfigValue('CUSTOM_ARTICLE_PATH') . '/' : 'media/' . $type . '/';
                        } else {
                            $custom_path = $type . '/';
                        }

                        if (!empty($section_id) && is_numeric($section_id)) {
                            $section = section::find_np($section_id);

                            if (!empty($sub_section_id) && is_numeric($sub_section_id)) {
                                $sub_section = sub_section::find_np($sub_section_id);
                            }
                        } else {
							$article_id = $id;
							$article = article::find($article_id);
							if($article->section_id > 0){
								$section = $article->section;
							}
							if($article->sub_section_id > 0){
								$sub_section = $article->sub_section;
							}
                        }

                        $section_name = isset($section->section_name) ? $section->section_name : '';
                        $sub_section_name =  isset($sub_section->sub_section_name) ? $sub_section->sub_section_name : '';

                        $article_meta_title = !empty($seo_title) ? $seo_title : $article->seo_meta_title;

                        $url .= $custom_path . $id . '/';
                         if ($section_name) {
                            $url .=self::clean_url($section_name) . '/';
                         }
                        if ($sub_section_name) {
                            $url .= self::clean_url($sub_section_name) . '/';
                        }
                        $url .= self::clean_url($article_meta_title);
                    }
                }
                break;
            case 'article_archive': {
                    if (ThemeService::ConfigValue('SPECIAL_URL')) {
                        $article_id = $id;
						if($object){
							$article_archive = $object;
						}else{
							$article_archive = article_archive::find_np($article_id);
						}
                        if($article_archive){
                            if (strlen($article_archive->permalink) > 4 ) {
                                $url .= $article_archive->permalink;
                            } else {
                                if(!empty($article_archive->sub_section_id)){
                                    $url_build = $article_archive->sub_section_id . "/";
                                }else{
                                    $url_build = $article_archive->section_id . "/";
                                }
                                $url_build .= $article_archive->cms_article_id."-";
                                $clean_url = self::clean_url($article_archive->article_title);
                                $url_build .= strtolower($clean_url);

                                $theme_controller_class = 'Themes\\'.ThemeService::Name().'\\controllers\\'.  CommonController::controller_class_name();
                                if(class_exists($theme_controller_class)){
                                    $themeController = new $theme_controller_class();
                                    if(method_exists($themeController, 'updateArchivePermalink')) {
                                        $function_name ='updateArchivePermalink';
                                        $url_build = $themeController->$function_name($article_archive);
                                    }
                                }
                                $url .= $url_build;
                            }
                        }
                    }else{
                        $article_id = $id;
                        $url .= 'article/'. $article_id ;
                        if($section_id){
                            $url .='/'. $section_id;
                        }
                        $url .='/'.self::clean_url($seo_title);
                    }
                }
                break;
            case 'page': {

                    $theme_controller_class = 'Themes\\'.ThemeService::Name().'\\controllers\\'.  CommonController::controller_class_name();
                    if(class_exists($theme_controller_class)){
                        $themeController = new $theme_controller_class();
                        if(method_exists($themeController, 'getPageUrl')) {
                            $function_name ='getPageUrl';
                            $url .= $themeController->$function_name($url,$id,$seo_title,$section_id,$sub_section_id);
                            break;
                        }
                    }

                    if ($id instanceof page) {
                        $page = $id;
                    } else {
                        $page = page::find_np($id);
                    }

                    $page_meta_title = $seo_title;
                    if (empty($page_meta_title) && !empty($page)) {
                        $page_meta_title = $page->page_title;
                    }

                    $url .= self::clean_url($page_meta_title);
                }
                break;
            case 'image': {
                    $url = self::main_url();
                    $image = $id;

                    $url .= 'Image/' . $image->id;

                    $image_caption = $seo_title;
                    if (empty($image_caption) && !empty($image)) {
                        $image_caption = $image->image_caption;
                    }

                    /**
                     * CommonController::returnYoutubeIdFromURL() will check if the text sent is a valid url or not
                     *
                     * If it was a valid url (assumed youtube link), it will parse it to return the youtube video ID
                     *
                     * Otherwise it will return the text as is, that would be the cases of
                     * 1- normal text caption
                     * 2- A youtube id
                     */
                    $image_caption = self::youtube_id_from_url($image_caption);

                    $url .= '/' . self::clean_url($image_caption);
                }
            break;
            case 'morein':
                $theme_controller_class = 'Themes\\'.ThemeService::Name().'\\controllers\\'.  CommonController::controller_class_name();
                if(class_exists($theme_controller_class)){
                    $themeController = new $theme_controller_class();
                    if(method_exists($themeController, 'getMoreIn')) {
                        $function_name ='getMoreIn';
                        $url = $themeController->$function_name($url,$section_id,$sub_section_id);
                        break;
                    }
                }

                $subsection_id = empty($sub_section_id) ? 0 : $sub_section_id;

                if ($section_id == 'widget') {
                    $url .= 'morearticles/';
                    $url .= 'widget';
                    $url .= '/' . $subsection_id;
                } else {
                    $section = section::find_np($section_id);
                    if ($section && is_numeric($section_id)) {

						if((!empty(ThemeService::ConfigValue('MOREARTICLE_PAGE_ID')))){
							$page = page::where("np_page_id",ThemeService::ConfigValue('MOREARTICLE_PAGE_ID'))->first();
							$url .= $page->page_title . '/';
						}else{
							$url .= 'morearticles/';
						}

                        $section_name = $section->name_or_info();
                        if (empty($section_name)) {
                            $url = '';
                        } else {
                            $url .= self::clean_url($section_name);
                        }
                    }

                    $sub_section = sub_section::find_np($subsection_id);

                    if ($sub_section && is_numeric($subsection_id) && $subsection_id > 0) {
                        $sub_section_name = $sub_section->sub_section_name;
                        if (!empty($sub_section_name)) {
                            $url .= '/' . self::clean_url($sub_section_name);
                        }
                        else{
                            $url .= '/' . $subsection_id;
                        }
                    }

                }


                break;
            case 'section':
                $theme_controller_class = 'Themes\\'.ThemeService::Name().'\\controllers\\'.  CommonController::controller_class_name();
                if(class_exists($theme_controller_class)){
                    $themeController = new $theme_controller_class();
                    if(method_exists($themeController, 'getPageUrlBySection')) {
                        $function_name ='getPageUrlBySection';
                        $url = $themeController->$function_name($url,$section_id);
                        break;
                    }
                }

                $menu_item = menu_item::find_by_section_id($section_id);
                $page_info = page::find_np($menu_item->page_id);
                if (isset($page_info->page_title) && $page_info->page_title) { // Check if found in another menu (the floating menu)
                    $url .= self::clean_url($page_info->page_title);
                } else {
                    $url = "";
                }
                break;
            case 'sub_section':
                $sub_section_id = $id;

                $menu_item = menu_item::find_by_sub_section_id($sub_section_id);
                $page_info = page::find_np($menu_item->page_id);
                if (isset($page_info->page_title) && $page_info->page_title) {  // Check if found in another menu (the floating menu)
                    $url .= self::clean_url($page_info->page_title);
                } else {
                    $url = "";
                }
                break;
            case 'pageBySection':
				if($id==2){ // order by Desc
					$page = page::find_by_last_section($section_id);
				}else{
					$page = page::find_by_section($section_id);
				}
                if ($page) {
                    $url .= self::clean_url($page->page_title);
                } else {
                    $url = "";
                }
                break;
            case 'pageByTitle':
                $page = page::where('page_title', $seo_title)->first();
                if ($page) {
                    $url .= self::clean_url($page->page_title);
                } else {
                    $url = "";
                }

                break;
            default:
                break;
        }

        return $url;
    }

    public static function youtube_id_from_url($youtube_link) {
        if (filter_var($youtube_link, FILTER_VALIDATE_URL)) {
            $youtube_video_id = self::parse_url($youtube_link)['query']['v']; // https://www.youtube.com/watch?v=KlAl5RHAAns

            if (empty($youtube_video_id)) { // https://youtu.be/Xptf8bATL_I
                $youtube_link = explode('/', $youtube_link);
                $youtube_video_id = $youtube_link[count($youtube_link) - 1];
            }
        } else { // Assuming only the id is sent
            $youtube_video_id = $youtube_link;
        }

        return $youtube_video_id;
    }

    public static function parse_url($url) {
        $url_components = parse_url($url);
        $url_components['query'] = explode('&', $url_components['query']);
        $url_components['query'] = array_map(function($item) {
            return array(explode('=', $item)[0] => explode('=', $item)[1]);
        }, $url_components['query']);

        foreach ($url_components['query'] as $key => $value) {
            $url_components['query'][array_keys($value)[0]] = array_values($value)[0];
            unset($url_components['query'][$key]);
        }

        return $url_components;
    }

    public static function clean_text($text) {
        $text = str_replace(['&lsquo;',
            '&rsquo;',
            '&laquo;',
            '&raquo;',
            '&quot;',
            '&#039;',
            'ً',
            '«',
            '»',
            'َ',
            'ً',
            'ُ',
            'ٌ',
            'ِ',
            'ٍ',
            'ّ',
            'إ',
            'à',
            'á',
            'â',
            'À',
            'Á',
            'È',
            'É',
            'è',
            'é',
            'ù',
            'ú',
            'û',
            '.'], ["'",
            "'",
            "",
            "",
            "",
            "'",
            "",
            "''",
            "''",
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            'a',
            'a',
            'a',
            'A',
            'A',
            'E',
            'E',
            'e',
            'e',
            'u',
            'u',
            'u',
            ''], $text);
        $text = str_replace(['.', ',', ':', ';', '"', '&', '\/', '\\', '\'', ' ', '/'], ['',
            '',
            '',
            '',
            '',
            '-',
            '',
            '',
            '',
            '-',
            '-'], $text);

        $text = strip_tags($text);
        return $text;
    }

    // function clean all special caracteres and replace space by -
     public static function clean_url($text) {

        $text = trim($text);
        $text = str_replace(['&#039;','&amp'],["",""],$text);

         //Theme Define For Special Character
        if(!empty(ThemeService::ConfigValue('SPECIAL_CHAR_ARRAY')) && !empty(ThemeService::ConfigValue('SPECIAL_CHAR_REPLACE'))){
            $text = str_replace(ThemeService::ConfigValue('SPECIAL_CHAR_ARRAY'), ThemeService::ConfigValue('SPECIAL_CHAR_REPLACE'), $text);

        }

        $text = str_replace(["Ş", "Ğ", "ı","İ","Í","Ö","Ç","Ü","Ä","ä","ẞ","ß","í"],["s","g","i","i","i","o","c","u","a","a","b","b","i"],$text);
        if( strtolower( ThemeService::ConfigValue("LANGUAGE")) !="ar"){
            $text = mb_strtolower($text);
        }
        
		$text = str_replace(["ç","é","ê","è","ë", "î", "ï","ñ"],["c","e","e","e","e","i","i","n"],$text);
                

		$text = str_replace(["û", "ù", "ü","ô","â", "à"],["u","u","u","o","a","a"],$text);
		$text = str_replace(["ş", "ğ", "ı","i","ö","ç"],["s","g","i","i","o","c"],$text);
        $regex = sprintf('/[^%s]/u', preg_quote("ءآأؤإئابةتثجحخدذرزسشصضطظعغفقكلمنهوىي٠١٢٣٤٥٦٧٨٩ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789- ", '/'));
        
        $url = preg_replace($regex, '', $text);
       
        $url = str_replace(array('“','”',"'",'"'),array('','','',''), $url);
        $url = preg_replace('/-+/', '-', $url);
        $url = preg_replace('/ +/', ' ', $url);
        $url = str_replace(' ', '-' , $url);
            
        $url = mb_strtolower($url, 'UTF-8');/* You can't use strtolower on UTF-8 encoded string, only on ISO 8859-1. Use mb_strtolower() instead. */
        return $url;
     }

}
