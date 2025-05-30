<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Layout\Website\Helpers\ImageHelper;
use Layout\Website\Helpers\UrlHelper;
use Layout\Website\Services\ThemeService;
use App\Http\Controllers\CommonController;
use \Illuminate\Support\Arr;
use Layout\Website\Services\WidgetService;

class article_archive extends Model {

    protected $table = "article_archive";
    protected $primaryKey = 'cms_article_id';
    public $timestamps = false;

    public static function find_np($np_id) {
        return self::where('cms_article_id', $np_id)->first();
    }

    public function getSectionAttribute() {
        return section::find_np($this->section_id);
        //return $this->hasOne('App\Models\section', 'np_section_id', 'section_id');
    }

    public function getSubSectionAttribute() {
        return sub_section::find_np($this->sub_section_id);
        //return $this->hasOne('App\Models\sub_section','np_sub_section_id', 'sub_section_id');
    }

    public function author() {
        //return $this->hasMany('App\Models\image','np_related_article_id','cms_article_id');
        $relation = $this->hasOne('App\Models\author', 'cms_author_id', 'author_id');
        //$relation->getQuery()->where('is_old_image',$this->is_old_article)->where('image_is_deleted', 0);
        return $relation;
    }

    public function section() {
        //return $this->hasMany('App\Models\image','np_related_article_id','cms_article_id');
        $relation = $this->hasMany('App\Models\section', 'np_section_id', 'section_id');
        //$relation->getQuery()->where('is_old_image',$this->is_old_article)->where('image_is_deleted', 0);
        return $relation;
    }

    public function image() {
        //return $this->hasMany('App\Models\image_archive','np_related_article_id','cms_article_id');
        $relation = $this->hasMany('App\Models\image_archive', 'np_related_article_id', 'cms_article_id');
        //$relation->getQuery()->where('is_old_image',$this->is_old_article)->where('image_is_deleted', 0);
        return $relation;
    }

    public function text_inside_paragraph($article_body = '', $paragraph = 2, $view_name = '', $get_found_paragraph = false, $articles_array = array()) {

        if (empty($article_body)) {
            return false;
        }
        if (!$get_found_paragraph) {
            $view_name = WidgetService::widget_view_name($view_name);
            $view_style = 'theme::widgets.' . $view_name;
            $html = View($view_style, array(
                'articles' => $articles_array
                    ))->render();
        } else {
            $html = '';
        }
        $replace_with = '</p>';
        $not_found_paragraph = 0;
        switch ($replace_with) {
            case '</p>':
                $content_table = explode($replace_with, $article_body);

                if (count($content_table) > $paragraph) {
                    $html = '<p>' . $html;
                    array_splice($content_table, $paragraph, 0, $html);
                     if (ThemeService::ConfigValue("PHP_7_4")) {
                        $content = implode($replace_with, $content_table);
                    } else {
                        $content = implode($content_table, $replace_with);
                    }
                    break;
                }
                $replace_with = '<br><br>';
            case '<br><br>':
                $content_table = explode($replace_with, $article_body);
                if (count($content_table) > $paragraph) {
                    $content_table[$paragraph] .= $html;
                     if (ThemeService::ConfigValue("PHP_7_4")) {
                        $content = implode($replace_with, $content_table);
                    } else {
                        $content = implode($content_table, $replace_with);
                    }
                    break;
                }
                $replace_with = '<br>';
            case '<br>':
                $content_table = explode($replace_with, $article_body);
                if (count($content_table) > $paragraph) {
                    $content_table[$paragraph] .= $html;
                     if (ThemeService::ConfigValue("PHP_7_4")) {
                        $content = implode($replace_with, $content_table);
                    } else {
                        $content = implode($content_table, $replace_with);
                    }
                    break;
                }
            default:
                $not_found_paragraph = 1;
                $content = $article_body;
                break;
        }
        if ($not_found_paragraph && $get_found_paragraph) {
            return false;
        }
        $content = str_replace("\n", "<br>", $content);
        return $content;
    }

    public function text_inside_paragraph_amp($article_body = '', $paragraph = 2, $get_found_paragraph = false, $relatedArticles = array()) {

        if (empty($article_body)) {
            return false;
        }
        if (!$get_found_paragraph) {
            $view_name = \Layout\Website\Services\WidgetService::widget_view_name('related_article_inside_body_amp');
            $view_style = 'theme::widgets.' . $view_name;
            $html = View($view_style, array(
                'articles' => $relatedArticles
                    ))->render();
        } else {
            $html = '';
        }
        $replace_with = '</p>';
        $not_found_paragraph = 0;
        switch ($replace_with) {
            case '</p>':
                $content_table = explode($replace_with, $article_body);

                if (count($content_table) > $paragraph) {
                    $html = '<p>' . $html;
                    array_splice($content_table, $paragraph, 0, $html);
                     if (ThemeService::ConfigValue("PHP_7_4")) {
                        $content = implode($replace_with, $content_table);
                    } else {
                        $content = implode($content_table, $replace_with);
                    }
                    break;
                }
                $replace_with = '<br><br>';
            case '<br><br>':
                $content_table = explode($replace_with, $article_body);
                if (count($content_table) > $paragraph) {
                    $content_table[$paragraph] .= $html;
                     if (ThemeService::ConfigValue("PHP_7_4")) {
                        $content = implode($replace_with, $content_table);
                    } else {
                        $content = implode($content_table, $replace_with);
                    }
                    break;
                }
                $replace_with = '<br>';
            case '<br>':
                $content_table = explode($replace_with, $article_body);
                if (count($content_table) > $paragraph) {
                    $content_table[$paragraph] .= $html;
                     if (ThemeService::ConfigValue("PHP_7_4")) {
                        $content = implode($replace_with, $content_table);
                    } else {
                        $content = implode($content_table, $replace_with);
                    }
                    break;
                }
            default:
                $not_found_paragraph = 1;
                $content = $article_body;
                break;
        }
        if ($not_found_paragraph && $get_found_paragraph) {
            return false;
        }
        return $content;
    }

    /**
     * sets the article->image_src
     */
    public function image_src($html = true, $thumb = false, $embed = false, $is_focal_point = true, $type = 'article_archive', $isDefaultImage = true, $image_cropping = false) {


        $enable_image_cropping = ThemeService::ConfigValue('ENABLE_IMAGE_CROPPING');
        $this->image_path = image_archive::where('np_related_article_id', $this->cms_article_id)->where('image_is_deleted', 0)->get();

        if (isset($this->image_path[0])) {
            $imgObj = $this->image_path[0];
            if (isset($imgObj->media_type)) {
                return ImageHelper::getImageSrc($imgObj, $html, $thumb, $embed, $is_focal_point, $type, $enable_image_cropping);
            } elseif ($html && $type == 'amp-article') {
                return View('theme::components.amp_no_image_src');
            } elseif ($html) {
                return View('theme::components.no_image_src');
            } else {
                return '';
            }
        } else {
            if ($isDefaultImage && $html && $type == 'amp-article') {
                return View('theme::components.amp_no_image_src');
            }
            elseif ($isDefaultImage && $html) {
                return View('theme::components.no_image_src');
            } else {
                return '';
            }
        }
    }

    /**
     * sets the article->image_caption
     */
    public function getImageCaptionAttribute($html = true, $thumb = true, $embed = false, $type = 'article') {
        if (!empty($this->image_path) && strlen($this->image_path) > 2) {
            $imgObj = json_decode(stripslashes($this->image_path));
            if (isset($imgObj->image_caption)) {
                return $imgObj->image_caption;
            } else {
                return '';
            }
        }
        return '';
    }

    public function article_body_info($textInsideParagraph = array(), $articles_array = array()) {
        $value = '';
        $article_body = $this->article_body;
        $re = "/(\*\*media\[(\d|,)*]\*\*)/";
        $matches = array();
        preg_match_all($re, $this->article_body, $matches);
        $matches = $matches[0];

        for ($k = 0; $k < count($matches); $k++) {
            $strToSearch = $matches[$k];
            $npImageIds = str_replace("**media[", "", $matches[$k]);
            $npImageIds = str_replace("]**", "", $npImageIds);

            if ($npImageIds) {
                $npImageIds = explode(",", $npImageIds);
                $images = image::whereIn('np_image_id', $npImageIds)->get();
                if ($images) {
                    $strToReplace = View('theme::components.article_body_images', ['images' => $images, 'article' => $this]); //function To Render Multiple Image
                }
                if (isset($images[0])) {
                    $value = str_replace($strToSearch, $strToReplace, $article_body);
                    $article_body = $value;
                }
            }
        }


        $value = empty($value) ? $this->article_body : $value;
        $re = "/(\*\*widget\[(\d|,)*]\*\*)/";
        $matches = array();
        preg_match_all($re, $value, $matches);
        if ($matches[0]) {
            $matches = $matches[0];
            $strToSearch = $matches[0];
            $npWidgetId = str_replace("**widget[", "", $matches[0]);
            $npWidgetId = str_replace("]**", "", $npWidgetId);
            if ($npWidgetId) {
                $article_related_ids = trim($this->related_articles_ids);
                $relatedArticlesIds = explode(',', $article_related_ids);
                $relatedArticles = article::whereIn('np_article_id', $relatedArticlesIds)->get();
                $strToReplace = View('theme::components.article_related_body', ['relatedArticles' => $relatedArticles])->render(); //function To Render Multiple Image
            }
            $value = str_replace($strToSearch, $strToReplace, $value);
        }

        $value = str_replace('******** async="" charset="utf-8" src="https://platform.twitter.com/widgets.js">********', '', $value);
        $value = str_replace('******** async=&quot;&quot; charset=&quot;utf-8&quot; src=&quot;https://platform.twitter.com/widgets.js&quot;>********', '', $value);
        $value = str_replace('******** async="" defer="defer" src="//platform.instagram.com/en_US/embeds.js">********', '', $value);
        $value = str_replace('******** async=&quot;&quot; defer=&quot;defer&quot; src=&quot;//platform.instagram.com/en_US/embeds.js&quot;>********', '', $value);

        if (!empty($textInsideParagraph)) {
            return $this->text_inside_paragraph($value, $textInsideParagraph['paragraph'], $textInsideParagraph['view_name'], false, $articles_array);
        }

        $value = str_replace("\n", "<br>", $value);

        return $value;
    }

    public function article_tags_view($view = '', $flag = 0) {
        $article_tags = explode(',', $this->article_tags);

        if ($view) {
            return View('theme::components.' . $view, array(
                'article_tags' => $article_tags,
                'flag' => $flag
            ));
        } else {
            return View('theme::components.article_tags', array(
                'article_tags' => $article_tags,
                'flag' => $flag
            ));
        }
    }

    /**
     */
    public function article_tags_view_amp($view = '', $flag = false) {
        $article_tags = explode(',', $this->article_tags);
        $get_count_images = ThemeService::ConfigValue('ARTICLE_TAGS_COUNT_IMAGE');
        $count_images = $get_count_images ? $this->image()->count() : false;

        if ($view) {
            return View('theme::components.' . $view, array(
                'article_tags' => $article_tags,
                'flag' => $flag
            ));
        } else {

            return View('theme::components.article_tags_amp', array(
                'article' => $this,
                'count_images' => $count_images,
                'article_tags' => $article_tags,
                'flag' => $flag
            ));
        }
    }

    public function article_body_clean($keepTags = '') {

        $re = '/<[^>]*>/m';
        $bodytext = preg_replace($re, '', $this->article_body);
        $re = "/(\*\*media\[(\d|,)*]\*\*)/";
        $bodytext = preg_replace($re, '', $bodytext);

        $bodytextClean = strip_tags($bodytext, $keepTags);

        return $bodytextClean;
    }

    /**
     * sets the article->simple_url
     */
    public function getSimpleUrlAttribute() {
        return ThemeService::ConfigValue('APP_URL') . 'article/' . $this->cms_article_id;
    }

    /**
     * sets the article->author_url
     */
    public function getAuthorUrlAttribute() {
        return UrlHelper::build_seo_url($this->author_id, 'author', $this->author_name);
    }

    /**
     * sets the article->seo_url
     */
    public function getSeoUrlAttribute() {
        if (ThemeService::ConfigValue('DO_NOT_USE_ARTICLE_SEO_URL')) {
            return $this->getSimpleUrlAttribute();
        }
        return UrlHelper::build_seo_url($this->cms_article_id, 'article_archive', $this->seo_meta_title, $this->section_name, $this->sub_section_name);
   }

    /**
     * sets the article->last_edited
     */
    public function getLastEditedAttribute() {
        return $this->publish_time;
    }

    /**
     * sets the article->section_name
     */
    public function getSectionNameAttribute() {
        $section = section::find_by_cms_id($this->section_id);
        return !empty($section->section_name) ? $section->section_name : '';
    }

    /**
     * sets the article->amp_url
     */
    public function getAmpUrlAttribute() {
        return ThemeService::ConfigValue('APP_URL') . 'ampArticle/' . $this->cms_article_id;
    }

    /*
     * sets the article->amp_article_body
     */

    public function getAmpArticleBodyAttribute() {
        $bodytext = str_replace("<br>", "\n", $this->article_body);
        $bodytext = preg_replace(
                array(
            '/<br style=(.*?)\/>/s', //remove style from br
            '/(<font[^>]*>)|(<\/font>)/m', //remove tag font
            '/(<FONT[^>]*>)|(<\/FONT>)/m', //remove tag font
            '/style=(.*?)>/s', // remove all style attribute
            '/\sstyle=("|\').*?("|\')/i',
            '/type=("|\').*?("|\')/', //remove type attribute type
            '/valign=("|\').*?("|\')/', //remove valign attribute type
            '/align=("|\').*?("|\')/', //remove align attribute type
            '/\s+tagline_ar\s*=\s*"\s*"/m', //remove  attribute tagline_ar empty in tags
            '/<p(.*?)>/m', //clean tag p
            '/<img[^>]+\>/i',
            '/<\s*blockquote.+?<\s*\/\s*blockquote.*?>/ms',
            '/<iframe.*?\/iframe>/i',
            '/(\*\*media\[(\d|,)*]\*\*)/',
            '#<script(.*?)>(.*?)</script>#is',
            '~<!--.+?-->~s',
            '/(<.+?)(?<=\s)on[a-z]+\s*=\s*(?:([\'"])(?!\2).+?\2|(?:\S+?\(.*?\)(?=[\s>])))(.*?>)/i', //remove all event on
            '/<meta [^>]+>/m',//remove meta tags from body
            '/(<gdiv>)|(<\/gdiv>)/m', //remove tag gdiv
            '/title=(").*?(\').*?(")/',//remove title attribute contain single quote
            '/<a(.*)(href="x-(.*)")>(.*)<\/a>/',
            '/<a(.*)(href="about:(.*)")>(.*)<\/a>/',
            '/<a(.*)href=\'file:(.*)>(.*)<\/a>/',
            '/<g(.*)>(.*)<\/g>/',
            '/<div(.*)droid="(.*)"(.*)serif="(.*)"(.*)>/',
            '/<div(.*)new="(.*)"(.*)roman="(.*)"(.*)>/',
            '/<div(.*)contenteditable="false"(.*)>/',         
            '/<div(.*)display="(.*)" playfair=(.*)>/',         
            '/<div(.*)jslog="(.*)"(.*)>/',
            '/<a(.*)fg_scanned="(.*)"(.*)>/',         
            '/<a(.*)shape="(.*)"(.*)>/',
            '/<a(.*)target=""(.*)>/',         
                ), array('/>', '', '', '>', '', '', '','', '', '<p>', '', ' ', ' ', ' ', '', ' ', '', '', '', '', ' $4 ', '$4 ',' ', ' $2 ','<div$1$3$5>', '<div$1$3$5>', '<div$1$2>', '<div$1>', '<div$1$3>','<a$1$3>','<a$1$3>', '<a$1target="_blank"$2>'), $bodytext);



        $bodytext = CommonController::cleanAmpBodyText($bodytext, 'hl2,font', 'span,br,hr');

        if (ThemeService::ConfigValue('REMOVE_ATTRIBUTES')) {
            $bodytext = CommonController::cleanAmpBodyAttributes($bodytext, ThemeService::ConfigValue('REMOVE_ATTRIBUTES'));
        }


        /************ convert youtube to amp-youtube and replace it in body ************/
        $pattern = '/<div data-oembed-url="https:\/\/youtu.be\/(.*?)">([^>]+)><iframe([^>]+)><\/iframe><\/div>/m';
        preg_match_all($pattern, $bodytext , $matches);
        foreach($matches[0] as $key => $match0){
            $replaces_str = '<div><amp-youtube data-videoid="' . $matches[1][$key] . '" layout="responsive"  width="480"  height="270"> </amp-youtube>';
            $bodytext= str_replace($matches[0][$key], $replaces_str, $bodytext);
        }
        /************ convert youtube to amp-youtube and replace it in body ************/

        /*         * ********** convert instagram to amp-instagram and replace it in body *********** */
        $pattern = '/<div data-oembed-url="https:\/\/www.instagram.com\/p\/([^>]+)\/.*?".*?\>/m';
        preg_match_all($pattern, $bodytext, $matches);
        foreach ($matches[0] as $key => $match0) {
            $replaces_str = '<div><amp-instagram class="no-bottom" data-shortcode="' . $matches[1][$key] . '" width="1" height="1" layout="responsive"></amp-instagram>';
            $bodytext = str_replace($matches[0][$key], $replaces_str, $bodytext);
        }
        /*         * ********** convert instagram to amp-instagram and replace it in body *********** */

        /*         * ********** convert twitter to amp-twitter and replace it in body *********** */
        $pattern = '/<div data-oembed-url="https:\/\/twitter.com\/([a-zA-Z0-9_]{1,15})\/status\/([^>]+)">/m';
        preg_match_all($pattern, $bodytext, $matches);
        foreach ($matches[0] as $key => $match0) {
            $replaces_str = '<div><amp-twitter  width="375" height="472" layout="responsive" data-tweetid="' . $matches[2][$key] . '" ></amp-twitter>';
            $bodytext = str_replace($matches[0][$key], $replaces_str, $bodytext);
        }
        /*         * ********** convert twitter to amp-twitter and replace it in body *********** */


        /*         * ********** convert facebook to amp-facebook and replace it in body *********** */
        $pattern = '/<div data-oembed-url="(https:\/\/www.facebook.com\/.*?)">/m';
        preg_match_all($pattern, $bodytext, $matches);
        foreach ($matches[0] as $key => $match0) {
            $replaces_str = '<div><amp-facebook  width="1" height="1" layout="responsive" data-href="' . $matches[1][$key] . '" ></amp-facebook>';
            $bodytext = str_replace($matches[0][$key], $replaces_str, $bodytext);
        }
        /*         * ********** convert facebook to amp-facebook and replace it in body *********** */

        /*         * ********** remove form forbidden attribute from body *********** */
        if(preg_match('/(.*)(<form .*)(action)(=.*)(enc)(.*)/', $bodytext)){
            $bodytext = preg_replace('/(.*)(<form .*)(action)(=.*)(enc)(.*)/', '$1$2$3-xhr$4$6', $bodytext);
        }
        /*         * ********** remove form forbidden attribute from body *********** */

        $bodytext = str_replace("\n", "<br>", $bodytext);

        return $bodytext;
    }

    public function special_amp_article_body($relatedArticles = array()) {
        $theme_controller_class = 'Themes\\' . ThemeService::Name() . '\\controllers\\' . CommonController::controller_class_name();
        if (class_exists($theme_controller_class)) {
            $themeController = new $theme_controller_class();
            if (method_exists($themeController, 'specialAmpArchiveArticleBody')) {
                $function_name = 'specialAmpArchiveArticleBody';
                $bodytext = $themeController->$function_name($this->article_body, $relatedArticles);
                return $bodytext;
            }
        }
        $bodytext = str_replace("<br>", "\n", $this->article_body);
        $bodytext = $bodytext;
        $bodytext = str_replace("&quot;", '', $bodytext);
        $bodytext = str_replace('href=""', "href=''", $bodytext);
        $bodytext = preg_replace(
               array(
            '/<table(.*?)borderColor=(.*?) (.*?)>/', //remove borderColor from table tag
            '/<div(.*?)rows=(("|\')[0-9]*("|\'))(.*?)>/', //remove rows attribute from div tag
            '/<div(.*?)cols=(("|\')[0-9]*("|\'))(.*?)>/', //remove cols attribute from div tag
            '/<div(.*?)name=(("|\')[a-zA-Z_-]*("|\'))(.*?)>/', //remove name attribute from div tag
            '/<form(.*?) action=(.*?) (.*?)>/', //replace action attribute in form tag by action-xhr
            '/<br style=(.*?)\/>/s', //remove style from br
            '/(<font[^>]*>)|(<\/font>)/m', //remove tag font
            '/(<FONT[^>]*>)|(<\/FONT>)/m', //remove tag font
            '/style=(.*?)>/s', // remove all style attribute
            '/\sstyle=("|\').*?("|\')/i',
            '/summary="(.*?)"/s', // remove all summary attribute
            '/type=("|\').*?("|\')/', //remove type attribute type
            '/valign=("|\').*?("|\')/', //remove valign attribute type
            '/align=("|\').*?("|\')/', //remove align attribute type
            '/\s+tagline_ar\s*=\s*"\s*"/m', //remove  attribute tagline_ar empty in tags
            '/<p(.*?)>/m', //clean tag p
            '/<B</',
            '/<b</',
            '/<img[^>]+\>/i',
            '/<\?xml[^>]+\>/i',
            '/<\s*blockquote.+?<\s*\/\s*blockquote.*?>/ms',
            '/<iframe.*?\/iframe>/i',
            '/<o:p.*?\/o:p>/i',
            '/(\*\*media\[(\d|,)*]\*\*)/',
            '#<script(.*?)>(.*?)</script>#is',
            '~<!--.+?-->~s',
            '/(<.+?)(?<=\s)on[a-z]+\s*=\s*(?:([\'"])(?!\2).+?\2|(?:\S+?\(.*?\)(?=[\s>])))(.*?>)/i', //remove all event on
            '/<meta [^>]+>/m',//remove meta tags from body
            '/(<gdiv>)|(<\/gdiv>)/m', //remove tag gdiv
            '/title=(").*?(\').*?(")/m'//remove title attribute contain single or double quote
                ), array('<table$1 $3>','<div $1 $5>','<div $1 $5>','<div $1 $5>','<form$1 action-xhr=$2 $3>','/>', '', '', '>', '', '', '','', '', '', '<p>','<','<', '','', ' ', ' ','', ' ', '', ' ', '', '', '', ''), $bodytext);



    /************ convert youtube to amp-youtube and replace it in body ************/
        $pattern = '/<div data-oembed-url="https:\/\/youtu.be\/(.*?)">([^>]+)><iframe([^>]+)><\/iframe><\/div>/m';
        preg_match_all($pattern, $bodytext , $matches);
        foreach($matches[0] as $key => $match0){
            $replaces_str = '<div><amp-youtube data-videoid="' . $matches[1][$key] . '" layout="responsive"  width="480"  height="270"> </amp-youtube>';
            $bodytext= str_replace($matches[0][$key], $replaces_str, $bodytext);
        }
        /************ convert youtube to amp-youtube and replace it in body ************/



        /*         * ********** convert instagram to amp-instagram and replace it in body *********** */
        $pattern = '/<div data-oembed-url="https:\/\/www.instagram.com\/p\/([^>]+)\/.*?".*?\>/m';
        preg_match_all($pattern, $bodytext, $matches);
        foreach ($matches[0] as $key => $match0) {
            $replaces_str = '<div><amp-instagram class="no-bottom" data-shortcode="' . $matches[1][$key] . '" width="1" height="1" layout="responsive"></amp-instagram>';
            $bodytext = str_replace($matches[0][$key], $replaces_str, $bodytext);
        }
        /*         * ********** convert instagram to amp-instagram and replace it in body *********** */

        /*         * ********** convert twitter to amp-twitter and replace it in body *********** */
        $pattern = '/<div data-oembed-url="https:\/\/twitter.com\/([a-zA-Z0-9_]{1,15})\/status\/([^>]+)">/m';
        preg_match_all($pattern, $bodytext, $matches);
        foreach ($matches[0] as $key => $match0) {
            $replaces_str = '<div><amp-twitter  width="375" height="472" layout="responsive" data-tweetid="' . $matches[2][$key] . '" ></amp-twitter>';
            $bodytext = str_replace($matches[0][$key], $replaces_str, $bodytext);
        }
        /*         * ********** convert twitter to amp-twitter and replace it in body *********** */

        /*         * ********** convert facebook to amp-facebook and replace it in body *********** */
        $pattern = '/<div data-oembed-url="(https:\/\/www.facebook.com\/.*?)">/m';
        preg_match_all($pattern, $bodytext, $matches);
        foreach ($matches[0] as $key => $match0) {
            $replaces_str = '<div><amp-facebook  width="1" height="1" layout="responsive" data-href="' . $matches[1][$key] . '" ></amp-facebook>';
            $bodytext = str_replace($matches[0][$key], $replaces_str, $bodytext);
        }
        /*         * ********** convert facebook to amp-facebook and replace it in body *********** */

        $bodytext = str_replace("\n", "<br>", $bodytext);

        if (!empty($relatedArticles)) {
            return $this->text_inside_paragraph_amp($bodytext, 2, false, $relatedArticles);
        }

        return $bodytext;
    }

    public static function updateArchivePermalink($cmsArticleId, $permalink) {
        article_archive::where('cms_article_id', $cmsArticleId)->update(array('permalink' => $permalink));
        return 0;
    }
    
    public function clean_article_body_from_json_editor() {  
        $articles_ceditor= articles_ceditor::find_np($this->np_article_id); 
         if(isset($articles_ceditor)){
            $article_json=json_decode(stripslashes($articles_ceditor->article_json),true);  
            $theme_controller_class = 'Themes\\' . ThemeService::Name() . '\\controllers\\' . CommonController::controller_class_name();
            if (class_exists($theme_controller_class)) {
                $themeController = new $theme_controller_class();
                if (method_exists($themeController, 'ConvertEditorToNormalArticle')) {
                    $function_name = 'ConvertEditorToNormalArticle'; 
                        $ConvertEditorToNormalArticle = $themeController->$function_name($article_json); 
                        $article_body=$ConvertEditorToNormalArticle['body'];
                        $article_body = str_replace("&rdquo;", "”", $article_body);
                        $article_body = str_replace("&ldquo;", "“", $article_body);
                        $article_body = str_replace("&#039;", "'", $article_body);
                        $article_body = stripslashes($article_body);
                        $article_body = strip_tags($article_body,"<p><br><h1><h2><h3><h4><h5><h6><table><tr><th><td>"); 
                        $re = "/(\*\*media\[(\d|,)*]\*\*)/"; 
                        $article_body = preg_replace($re, '', $article_body); 
                        $re = '/\[caption (.*)\](.*)\[\/caption\]/';
                        $article_body = preg_replace($re, '', $article_body);
                        $re = '/id[s]*\=\"(.*?)\"/';
                        $article_body = preg_replace($re, '', $article_body);
                        $re = '/\[gallery (.*)\]/';
                        $article_body = preg_replace($re, '', $article_body);
                        return $article_body;
                }
            }
        }else{
            return $this->article_body; 
        } 
    }
            
    public function amp_json_article_body() {
         $articles_ceditor= articles_ceditor::find_np($this->np_article_id); 
         if(isset($articles_ceditor)){
            $article_json=json_decode(stripslashes($articles_ceditor->article_json),true);  
            $theme_controller_class = 'Themes\\' . ThemeService::Name() . '\\controllers\\' . CommonController::controller_class_name();
            if (class_exists($theme_controller_class)) {
                $themeController = new $theme_controller_class();
                if (method_exists($themeController, 'ConvertEditorToNormalArticle')) {
                    $function_name = 'ConvertEditorToNormalArticle'; 
                        $ConvertEditorToNormalArticle = $themeController->$function_name($article_json);  
                        $bodytext=$ConvertEditorToNormalArticle['body']; 
                }
            }
        } else{
            $bodytext = $this->article_body;
        } 
        
        /* replace body image code by amp image version */
        $re = "/(\*\*media\[(\d|,)*]\*\*)/";
        $matches = array();
        preg_match_all($re, $bodytext, $matches);
        if (!empty($matches[0])) {
            $matches = $matches[0];

            for ($k = 0; $k < count($matches); $k++) {
                $strToSearch = $matches[$k];
                $npImageIds = str_replace("**media[", "", $matches[$k]);
                $npImageIds = str_replace("]**", "", $npImageIds);


                if ($npImageIds) {
                    $npImageIds = explode(",", $npImageIds);
                    $images = image::whereIn('np_image_id', $npImageIds)->where('image_is_deleted', 0)->get();
                    if ($images) {
                        $strToReplace = View('theme::components.amp_article_body_images', ['images' => $images]); //function To Render Multiple and single Image
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


        $re = "/<img(.*?)(\**NP_IMAGE_BODY\[.*?]\**)(.*?)\/>/im";
        $fullMatches = array();
        preg_match_all($re, $this->article_body, $fullMatches);
        $matches = $fullMatches[2];
        if (!empty($matches)) {
            for ($k = 0; $k < count($matches); $k++) {
                $align_dir = '';
                $width_size = '';
                $strToSearch = $matches[$k];
                $npImageIds = str_replace("**NP_IMAGE_BODY[", "", $matches[$k]);
                $npImageIds = str_replace("]**", "", $npImageIds);

                if ($npImageIds) {
                    if (isset($fullMatches[0][$k])) {
                        $strToSearch = $fullMatches[0][$k];
                    }
                    if (isset($fullMatches[3][$k])) {
                        $reWidthStyle = '/(.*?)(width:)(.*?)(")/im';
                        $matchesWidthStyle = array();
                        preg_match_all($reWidthStyle, $fullMatches[3][$k], $matchesWidthStyle);
                        if (isset($matchesWidthStyle[3][0])) {
                            $width_size = $matchesWidthStyle[3][0];
                            $width_size = str_replace('px', '', $width_size);
                        }
                    }
                    $npImageIds = explode(",", $npImageIds);
                    $images = image::whereIn('np_image_id', $npImageIds)->where('image_is_deleted', 0)->get();
                    if ($images) {
                        $strToReplace = View('theme::components.amp_article_body_images', [
                            'images' => $images,
                            'width_size' => $width_size
                        ]); //function To Render Multiple and single Image
                    }
                    if (isset($images[0])) {
                        $value = str_replace($strToSearch, $strToReplace, $bodytext);
                        $bodytext = $value;
                    } else {
                        /* if idForImage exist but image not exist in table remove **NP_IMAGE_BODY** */
                        $value = str_replace($strToSearch, '', $bodytext);
                        $bodytext = $value;
                    }
                }
            }
        }


        /* replace body image code by amp image version */

        $bodytext = str_replace('<br type="_moz" />', '<br />', $bodytext);
        $bodytext = str_replace('://://', '://', $bodytext);

        $bodytext = CommonController::cleanAmpBodyText($bodytext, 'gdiv');


        /*         * ********** convert youtube to amp-youtube and replace it in body *********** */
        $pattern = '/<iframe(.*)src="https:\/\/www.youtube.com\/embed\/([a-zA-Z0-9_-]{1,15})?(.*?)"(.*?)<\/iframe>/m';
        preg_match_all($pattern, $bodytext, $matches);
        foreach ($matches[0] as $key => $match0) {
            if(!empty($matches[2][$key])) {
                $replaces_str = '<amp-youtube data-videoid="' . $matches[2][$key] . '" layout="responsive"  width="480"  height="270"> </amp-youtube>';
            }else{
                $replaces_str="";
            }
            $bodytext = str_replace($matches[0][$key], $replaces_str, $bodytext);
        }
        /*         * ********** convert youtube to amp-youtube and replace it in body *********** */

        /*         * ********** convert youtube to amp-youtube and replace it in body *********** */
        $pattern = '/<iframe(.*)src="\/\/www.youtube.com\/embed\/([a-zA-Z0-9_-]{1,15})?(.*?)"(.*?)<\/iframe>/m';
        preg_match_all($pattern, $bodytext, $matches);
        foreach ($matches[0] as $key => $match0) {
            if(!empty($matches[2][$key])) {
                $replaces_str = '<amp-youtube data-videoid="' . $matches[2][$key] . '" layout="responsive"  width="480"  height="270"> </amp-youtube>';
            }else{
                $replaces_str = "";
            }
            $bodytext = str_replace($matches[0][$key], $replaces_str, $bodytext);
        }
        /*         * ********** convert youtube to amp-youtube and replace it in body *********** */


        /*         * ********** convert youtube to amp-youtube and replace it in body *********** */
        $bodytext = str_replace('&quot;', '"', $bodytext);
        $pattern = '/<iframe(.*?)src="https:\/\/www.youtube.com\/embed\/(.*?)"(.*?)<\/iframe>/m';
        preg_match_all($pattern, $bodytext, $matches);
        foreach ($matches[0] as $key => $match0) {
            $matches[2][$key] = str_replace(["?rel=0", "?controls=0", "&amp;showinfo=0", "&showinfo=0"], "", $matches[2][$key]);
            $replaces_str = '<amp-youtube data-videoid="' . $matches[2][$key] . '" layout="responsive"  width="480"  height="270"> </amp-youtube>';
            $bodytext = str_replace($matches[0][$key], $replaces_str, $bodytext);
        }
        /*         * ********** convert youtube to amp-youtube and replace it in body *********** */

        /*         * ********** convert facebook to amp-facebook and replace it in body *********** */
        $pattern = '/<iframe(.*?)src="https:\/\/www.facebook.com\/(.*?)href=(.*?)"(.*?)\/iframe>/m';
        preg_match_all($pattern, $bodytext, $matches);
        foreach ($matches[0] as $key => $match0) {
            $replaces_str = '<amp-facebook width="552" height="310" layout="responsive"  data-href="' . urldecode($matches[3][$key]) . '"> </amp-facebook>';
            $bodytext = str_replace($matches[0][$key], $replaces_str, $bodytext);
        }
        /*         * ********** convert facebook to amp-facebook and replace it in body *********** */
        
        
        /*         * ********** check blockquote if type instagram, tiktok, or twitter *********** */
        
        $all_matches = array();
        $bodytext = preg_replace('/(\\n|\\r\\n|\\n\\r)/','',$bodytext);
        preg_match_all('/<blockquote(.*)\/blockquote>/mU', $bodytext, $all_matches);
        if(isset($all_matches[0])){
            foreach ($all_matches[0] as $one_match){
                /*         * ********** convert twitter to amp-twitter and replace it in body *********** */
                $tw_pattern = '/<blockquote(.*?)twitter(.*?)https:\/\/twitter.com\/(.*?)\/status\/([a-zA-Z0-9_]+)(.*?)<\/blockquote>/m';
                $tw_match = array();
                preg_match_all($tw_pattern, $one_match, $tw_match);
                if(isset($tw_match[0]) && !empty($tw_match[0])){
                    $twMatches = $one_match;
                    $tw_replace_text = '<amp-twitter width="375" height="472" layout="responsive" data-tweetid="$4" ></amp-twitter>';
                    $tw_replace_by = preg_replace($tw_pattern,$tw_replace_text, $one_match);
                    $bodytext = str_replace($one_match, $tw_replace_by, $bodytext);  
                }
                /*         * ********** convert twitter to amp-twitter and replace it in body *********** */
                
                /*         * ********** convert instagram to amp-instagram and replace it in body *********** */
                $insta_pattern = '/<blockquote(.*?)instagram(.*?)https:\/\/www.instagram.com\/(p|tv|reel)\/([a-zA-Z0-9_]+)(.*?)<\/blockquote>/m';
                $insta_match = array();
                preg_match_all($insta_pattern, $one_match, $insta_match);
                if(isset($insta_match[0]) && !empty($insta_match[0])){
                    $instaMatches = $one_match;
                    $insta_replace_text = '<amp-instagram class="no-bottom" data-shortcode="$4" width="1" height="1" layout="responsive"></amp-instagram>';
                    $insta_replace_by = preg_replace($insta_pattern,$insta_replace_text, $one_match);
                    $bodytext = str_replace($one_match, $insta_replace_by, $bodytext);  
                }
                /*         * ********** convert instagram to amp-instagram and replace it in body *********** */
                
                /*         * ********** convert tiktok to amp-tiktok and replace it in body *********** */
                $tiktok_pattern = '/<blockquote(.*?)cite="https:\/\/www.tiktok.com(.*?)data-video-id="(.*?)"(.*?)<\/blockquote>/m';
                $tiktok_match = array();
                preg_match_all($tiktok_pattern, $one_match, $tiktok_match);
                if(isset($tiktok_match[0]) && !empty($tiktok_match[0])){
                    $tiktok_replace_text = '<amp-tiktok width="325" height="575" data-src="$3"></amp-tiktok>';
                    $tiktok_replace_by = preg_replace($tiktok_pattern,$tiktok_replace_text, $one_match);
                    $bodytext = str_replace($one_match, $tiktok_replace_by, $bodytext);
                }
                /*         * ********** convert tiktok to amp-tiktok and replace it in body *********** */
                
            }
        }
        if (isset($twMatches) && !empty($twMatches)) {
            $pattern = '/<div data-oembed-url="https:\/\/twitter.com\/([a-zA-Z0-9_]{1,15})\/status\/([^>]+)">/m';
            preg_match_all($pattern, $bodytext, $matches);
            foreach ($matches[0] as $key => $match0) {
                $replaces_str = '<div>';
                $bodytext = str_replace($matches[0][$key], $replaces_str, $bodytext);
            }
        }
        if (isset($instaMatches) && !empty($instaMatches)) {
            $pattern = '/<div data-oembed-url="https:\/\/www.instagram.com(.*?)web_copy_link">/m';
            preg_match_all($pattern, $bodytext, $matches);
            foreach ($matches[0] as $key => $match0) {
                $replaces_str = '<div>';
                $bodytext = str_replace($matches[0][$key], $replaces_str, $bodytext);
            }
        }
        
        /*         * ********** check blockquote if type instagram, tiktok, or twitter *********** */
        
         /*         * ********** convert video tag to amp-video and replace it in body *********** */
        $pattern = '/<video(.*?)src="(.*?)"(.*?)\/video>/m';
        preg_match_all($pattern, $bodytext, $matches);
        foreach ($matches[0] as $key => $match0) {
            $replaces_str = '<amp-video width="500" height="250" layout="responsive"  src="' . urldecode($matches[2][$key]) . '"> </amp-video>';
            $bodytext = str_replace($matches[0][$key], $replaces_str, $bodytext);
        }
        /*         * ********** convert video tag to amp-video and replace it in body *********** */
        

        $bodytext = str_replace("&quot;", '', $bodytext);
        $bodytext = preg_replace(
            array(
                '/<a(.*)href="">(.*)<\/a>/U',
                '/<a(.*)href=\'\'>(.*)<\/a>/U',
            ),
            ['$2','$2'],
            $bodytext);
//        $bodytext = str_replace(['"“','�?"',"''",'""'], ['"','"',"'",'"'], $bodytext);
        $bodytext = str_replace(['�?','“'], ['"','"'], $bodytext);
        $bodytext = str_replace(['<a href="<a-href="','<a href="<a href="'], ['<a href="','<a href="'], $bodytext);
        $bodytext = str_replace('href=""', "href=''", $bodytext);
        $bodytext = preg_replace(
                array(
            '/<table(.*?)borderColor=(.*?) (.*?)>/', //remove borderColor from table tag
            '/<table(.*)>/U', //remove attributes from table tag
            '/<div(.*?)rows=(("|\')[0-9]*("|\'))(.*?)>/', //remove rows attribute from div tag
            '/<div(.*?)cols=(("|\')[0-9]*("|\'))(.*?)>/', //remove cols attribute from div tag
            '/<div(.*?)name=(("|\')[a-zA-Z_-]*("|\'))(.*?)>/', //remove name attribute from div tag
            '/<div(.*?)fb-xfbml-state="(.*?)"(.*?)>/', //remove fb-xfbml-state attribute from div tag
            '/<div(.*?)fb-iframe-plugin-query="(.*?)"(.*?)>/', //remove fb-iframe-plugin-query attribute from div tag
            '/<form(.*?) action=(.*?) (.*?)>/', //replace action attribute in form tag by action-xhr
            '/<br style=(.*?)\/>/s', //remove style from br
            '/(<font[^>]*>)|(<\/font>)/m', //remove tag font
            '/(<FONT[^>]*>)|(<\/FONT>)/m', //remove tag font
            '/<em(.*?)>/s', // remove all style attribute
            '/style=(.*?)>/s', // remove all style attribute
            '/\sstyle=("|\').*?("|\')/i',
            '/summary="(.*?)"/s', // remove all summary attribute
            '/itemtype=("|\').*?("|\')/', //remove type attribute itemtype
            '/type=("|\').*?("|\')/', //remove type attribute type
            '/valign=("|\').*?("|\')/', //remove valign attribute type
            '/align=("|\').*?("|\')/', //remove align attribute type
            '/value=("|\').*?("|\')/', //remove value attribute type
            '/\s+tagline_ar\s*=\s*"\s*"/m', //remove  attribute tagline_ar empty in tags
            '/<p(.*?)>/m', //clean tag p
            '/<B</',
            '/<b</',
            '/<img[^>]+\>/i',
            '/<\?xml[^>]+\>/i',
            '/<\s*blockquote.+?<\s*\/\s*blockquote.*?>/ms',
            '/<iframe.*?\/iframe>/i',
            '/<o:p.*?\/o:p>/i',
            '/(\*\*media\[(\d|,)*]\*\*)/',
            '#<script(.*?)>(.*?)</script>#is',
            '~<!--.+?-->~s',
            '/(<.+?)(?<=\s)on[a-z]+\s*=\s*(?:([\'"])(?!\2).+?\2|(?:\S+?\(.*?\)(?=[\s>])))(.*?>)/i', //remove all event on
            '/<meta [^>]+>/m', //remove meta tags from body
            '/(<gdiv>)|(<\/gdiv>)/m', //remove tag gdiv
            '/<a(.*?)title=".*?(\').*?"\>/', //remove title attribute contain single quote in <a> tag
            '/<h2(.*?)title=".*?(\').*?"\>/', //remove title attribute contain single quote in <h2> tag
            '/<li(.*?)title=".*?(\').*?"\>/',//remove title attribute contain single quote in <li> tag
            '/spellcheck=("|\').*?("|\')/',//remove spellcheck attribute 
            '/dir=("|\').*?("|\')/',//remove dir attribute
            '/<a(.*)href=("|\')file:(.*)>(.*)<\/a>/',
            '/<a(.*)(href="x-(.*)")>(.*)<\/a>/',
            '/<a(.*)(href="about:(.*)")>(.*)<\/a>/',
            '/<g(.*)>(.*)<\/g>/',
            '/<div(.*)droid="(.*)"(.*)serif="(.*)"(.*)>/',
            '/<div(.*)new="(.*)"(.*)roman="(.*)"(.*)>/',
            '/<div(.*)contenteditable="false"(.*)>/',         
            '/<div(.*)display="(.*)" playfair=(.*)>/',         
            '/<div(.*)jslog="(.*)"(.*)>/',
            '/<a(.*)fg_scanned="(.*)"(.*)>/',         
            '/<a(.*)shape="(.*)"(.*)>/',
            '/<a(.*)target=""(.*)>/',
            '/<a(.*)(\')(.*)>/m',
            '/<a(.*)href=(\'|\")ttp(s)*:(.*)>/',
            '/<a(.*)helvetica=(\"|\')(.*)(\"|\')(.*)neue=(\"|\')(.*)(\"|\')(.*)>/U',        
            '/<a(.*)ms=(\"|\')(.*)(\"|\')(.*)>/U',
            '/<a(.*)href=\'(.*)\">/',
            '/<a(.*)data- href(.*)/',
//            '/<a(.*)href=\"(.*)\"(.*)\"target(.*)/',
//            '/(?<!target=(\"|\'))_blank(\"|\')/',
            '/target=(?<!("|\'))_blank("|\')/',
            '/<ul(.*)>/',
            '/<img src="(.*)"(.*)"/U',
            '/<object(.*)>/U',
            '/<embed(.*)>(.*)<\/embed>/',
            '/rel=("|\').*?("|\')/',
            '/noopener=("|\').*?("|\')/',
            '/noreferrer=("|\').*?("|\')/',
            '/<quillbot-extension-portal>(.*)<\/quillbot-extension-portal>/',
            '/<jpy=ebs(.*)>(.*)<\/jpy=ebs>/U',
            '/<org(.*)>(.*)<\/org>/U',
            '/data-label=("|\').*?("|\')/',
            '/scope=("|\').*?("|\')/',
            '/<summary(.*)>(.*)<\/summary>/U',
        ), array(
            '<table$1 $3>',
            '<table>',
            '<div$1$5>',
            '<div$1$5>',
            '<div$1$5>',
            '<div$1$3>',
            '<div$1$3>',
            '<form$1 action-xhr=$2 $3>',
            '<br/>',
            '',
            '',
            '<em>',
            '>',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '<p>',
            '<B/>',
            '<b/>',
            '',
            '',
            ' ',
            ' ',
            '',
            ' ',
            '',
            ' ',
            '',
            '',
            '',
            '<a$1>',
            '<h2 $1 >',
            '<li $1 >',
            '',
            '',
            ' ',
            ' ',
            '$4 ',
            ' $2 ',
            '<div$1$3$5>',
            '<div$1$3$5>',
            '<div$1$2>',
            '<div$1>',
            '<div$1$3>',
            '<a$1$3>',
            '<a$1$3>',
            '<a$1target="_blank"$2>',
            '<a$1"$3>',
            '<a$1href=$2http$3:$4>',
            '<a$1$5$9>',
            '<a$1$5>',
            '<a$1href="$2">',
            '<a$1data-href$2',
//            '<a$1href="$2$3"target$4',
//            'target=$2_blank$2',
            'target=$2_blank$2',
            '<ul>',
            '<img src="$2"',
            '',
            '',
            '',
            '',
            '',
            '',
            '$2',
            '$2',
            '',
            '',
            '$2',
        ),$bodytext);
        $bodytext = preg_replace('/\s\s\s*/', ' ', $bodytext); // replace multiple spaces by single space
        //DO_NOT_CLEAN_AMP_SPAN: this define is added if we do not want to remove classes from tags
        if (!ThemeService::ConfigValue('DO_NOT_CLEAN_AMP_TAGS')) {
            $bodytext = CommonController::cleanAmpBodyText($bodytext,'hl2,font', 'span,br');
        }
        if (ThemeService::ConfigValue('REMOVE_ATTRIBUTES')) {
            $bodytext = CommonController::cleanAmpBodyAttributes($bodytext, ThemeService::ConfigValue('REMOVE_ATTRIBUTES'));
        }



        /*         * ********** convert youtube to amp-youtube and replace it in body *********** */
        $pattern = '/<div data-oembed-url="https:\/\/youtu.be\/(.*?)">([^>]+)><iframe([^>]+)><\/iframe><\/div>/m';
        preg_match_all($pattern, $bodytext, $matches);
        foreach ($matches[0] as $key => $match0) {
            $replaces_str = '<div><amp-youtube data-videoid="' . $matches[1][$key] . '" layout="responsive"  width="480"  height="270"> </amp-youtube>';
            $bodytext = str_replace($matches[0][$key], $replaces_str, $bodytext);
        }
        /*         * ********** convert youtube to amp-youtube and replace it in body *********** */



        /*         * ********** convert instagram to amp-instagram and replace it in body *********** */
        $pattern = '/<div data-oembed-url="https:\/\/www.instagram.com\/p\/([^>]+)\/.*?".*?\>/m';
        preg_match_all($pattern, $bodytext, $matches);
        foreach ($matches[0] as $key => $match0) {
            $replaces_str = '<div><amp-instagram class="no-bottom" data-shortcode="' . $matches[1][$key] . '" width="1" height="1" layout="responsive"></amp-instagram>';
            $bodytext = str_replace($matches[0][$key], $replaces_str, $bodytext);
        }
        /*         * ********** convert instagram to amp-instagram and replace it in body *********** */


        /*         * ********** convert twitter to amp-twitter and replace it in body *********** */
        $pattern = '/<div data-oembed-url="https:\/\/twitter.com\/([a-zA-Z0-9_]{1,15})\/status\/([^>]+)">/m';
        preg_match_all($pattern, $bodytext, $matches);
        foreach ($matches[0] as $key => $match0) {
            $replaces_str = '<div><amp-twitter width="375" height="472" layout="responsive" data-tweetid="' . $matches[2][$key] . '" ></amp-twitter>';
            $bodytext = str_replace($matches[0][$key], $replaces_str, $bodytext);
        }
        /*         * ********** convert twitter to amp-twitter and replace it in body *********** */

        /*         * ********** convert facebook to amp-facebook and replace it in body *********** */
        $pattern = '/<div data-oembed-url="(https:\/\/www.facebook.com\/.*?)">/m';
        preg_match_all($pattern, $bodytext, $matches);
        foreach ($matches[0] as $key => $match0) {
            $replaces_str = '<div><amp-facebook  width="1" height="1" layout="responsive" data-href="' . $matches[1][$key] . '" ></amp-facebook>';
            $bodytext = str_replace($matches[0][$key], $replaces_str, $bodytext);
        }
        /*         * ********** convert facebook to amp-facebook and replace it in body *********** */

         /************ convert dailymotion to amp-dailymotion and replace it in body ************/
        /*ex <div class="dailymotion-cpe" height="360" video-id="x82nssx"> </div> */
        $pattern = '/<div class="dailymotion-cpe" (.*?) video-id="([a-zA-Z0-9_-]{1,15})?(.*?)">/m';
        preg_match_all($pattern, $bodytext , $matches);
        foreach($matches[0] as $key => $match0) {
            $replaces_str = '<div><amp-dailymotion data-videoid="'.$matches[2][$key].'" layout="responsive" data-ui-highlight="FF4081" width="480" height="270"> </amp-dailymotion>';
            $bodytext= str_replace($matches[0][$key], $replaces_str, $bodytext);
        }
        /************ convert dailymotion to amp-dailymotion and replace it in body ************/

        /**  replace spaces with dashes in href */
        $re = '/href=\"([^"]*)(\s)(.*)\"/U';
        preg_match_all($re, $bodytext, $matches);
        if(!empty($matches)){
            foreach($matches[0] as $key=>$match0){
                $bodytext= str_replace($matches[0][$key], str_replace([" ",":"],["-",""],$matches[0][$key]), $bodytext);
            }
        }

        /*we put here because before we remove attribute type from article body*/
        $theme_controller_class = 'Themes\\' . ThemeService::Name() . '\\controllers\\' . CommonController::controller_class_name();
        if (class_exists($theme_controller_class)) {
            $themeController = new $theme_controller_class();
            if (method_exists($themeController, 'workWithAmpArticleBody')) {
                $function_name = 'workWithAmpArticleBody';
                $bodytext = $themeController->$function_name($bodytext);
            }
        }
        
        //we can add here any static code we want to remove
        $bodytext = str_replace(array('fulltext=""','style="line-height:107%"','<u5:p></u5:p>'),array('','',''),$bodytext);
//        
        //remove style attribute from body
        $re = '/<style(.*?)>(.*?)<\/style>/smi';
        preg_match_all($re, $bodytext, $matches, PREG_SET_ORDER, 0);
        foreach ($matches as $i => $match) {
            if (isset($match[0])) {
                $bodytext = str_replace($match[0], "", $bodytext);
            }
        }

        if (!empty($relatedArticles)) {
            return $this->text_inside_paragraph_amp($bodytext, 5, false, $relatedArticles);
        }

        return $bodytext;
    }

}
