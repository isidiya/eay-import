<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Layout\Website\Helpers\ImageHelper;
use Layout\Website\Helpers\UrlHelper;
use Layout\Website\Services\ThemeService;
use App\Http\Controllers\CommonController;
use \Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class article_archive_lastdays extends Model
{
    protected  $table="article_archive_lastdays";
    protected $primaryKey = 'cms_article_id';
    public    $timestamps = false;
    const cached_minutes = 1440;

    public static function find_np($np_id){
        return self::where('cms_article_id', $np_id)->first();
    }

    public function getSectionAttribute(){
        return section::find_np($this->section_id);
        //return $this->hasOne('App\Models\section', 'np_section_id', 'section_id');
    }

    public function getSubSectionAttribute(){
        return sub_section::find_np($this->sub_section_id);
        //return $this->hasOne('App\Models\sub_section','np_sub_section_id', 'sub_section_id');
    }
	public function author(){
        //return $this->hasMany('App\Models\image','np_related_article_id','cms_article_id');
        $relation = $this->hasOne('App\Models\author','cms_author_id','author_id');
        //$relation->getQuery()->where('is_old_image',$this->is_old_article)->where('image_is_deleted', 0);
        return $relation;
    }

	public function section(){
        //return $this->hasMany('App\Models\image','np_related_article_id','cms_article_id');
        $relation = $this->hasMany('App\Models\section','np_section_id','section_id');
        //$relation->getQuery()->where('is_old_image',$this->is_old_article)->where('image_is_deleted', 0);
        return $relation;
    }

     public function image(){
        //return $this->hasMany('App\Models\image_archive','np_related_article_id','cms_article_id');
        $relation = $this->hasMany('App\Models\image_archive','np_related_article_id','cms_article_id');
        //$relation->getQuery()->where('is_old_image',$this->is_old_article)->where('image_is_deleted', 0);
        return $relation;
    }

    public function text_inside_paragraph($article_body='',$paragraph=2,$view_name='',$get_found_paragraph=false,$articles_array=array()){

        if(empty($article_body)){
            return false;
        }
        if(!$get_found_paragraph){
            $view_name = WidgetService::widget_view_name($view_name);
            $view_style = 'theme::widgets.' . $view_name;
            $html =  View($view_style,array(
                'articles' => $articles_array
            ))->render();
        }else{
            $html = '';
        }
        $replace_with = '</p>';
        $not_found_paragraph = 0;
        switch ($replace_with) {
            case '</p>':
                $content_table = explode($replace_with, $article_body);

                if(count($content_table) > $paragraph){
                    $html = '<p>' . $html;
                    array_splice( $content_table, $paragraph, 0,$html);
                    $content = implode($content_table, $replace_with);
                    break;
                }
                $replace_with = '<br><br>';
            case '<br><br>':
                $content_table = explode($replace_with, $article_body);
                if(count($content_table) > $paragraph){
                    $content_table[$paragraph] .= $html;
                    $content = implode($content_table, $replace_with);
                    break;
                }
                $replace_with = '<br>';
            case '<br>':
                $content_table = explode($replace_with, $article_body);
                if(count($content_table) > $paragraph){
                    $content_table[$paragraph] .= $html;
                    $content = implode($content_table, $replace_with);
                    break;
                }
            default:
                $not_found_paragraph = 1;
                $content = $article_body;
                break;
        }
        if($not_found_paragraph && $get_found_paragraph){
            return false;
        }
        $content = str_replace("\n", "<br>", $content);
        return $content;

    }


     public function text_inside_paragraph_amp($article_body='',$paragraph=2,$get_found_paragraph=false,$relatedArticles=array()){

        if(empty($article_body)){
            return false;
        }
        if(!$get_found_paragraph){
            $view_name = \Layout\Website\Services\WidgetService::widget_view_name('related_article_inside_body_amp');
            $view_style = 'theme::widgets.' . $view_name;
            $html =  View($view_style,array(
                'articles' => $relatedArticles
            ))->render();
        }else{
            $html = '';
        }
        $replace_with = '</p>';
        $not_found_paragraph = 0;
        switch ($replace_with) {
            case '</p>':
                $content_table = explode($replace_with, $article_body);

                if(count($content_table) > $paragraph){
                    $html = '<p>' . $html;
                    array_splice( $content_table, $paragraph, 0,$html);
                    $content = implode($content_table, $replace_with);
                    break;
                }
                $replace_with = '<br><br>';
            case '<br><br>':
                $content_table = explode($replace_with, $article_body);
                if(count($content_table) > $paragraph){
                    $content_table[$paragraph] .= $html;
                    $content = implode($content_table, $replace_with);
                    break;
                }
                $replace_with = '<br>';
            case '<br>':
                $content_table = explode($replace_with, $article_body);
                if(count($content_table) > $paragraph){
                    $content_table[$paragraph] .= $html;
                    $content = implode($content_table, $replace_with);
                    break;
                }
            default:
                $not_found_paragraph = 1;
                $content = $article_body;
                break;
        }
        if($not_found_paragraph && $get_found_paragraph){
            return false;
        }
        return $content;

    }

     /**
     * sets the article->image_src
     */
    public function image_src($html =true, $thumb=false ,$embed=false,$is_focal_point = true,$type='article_archive',$isDefaultImage = true,$image_cropping=false){


        $enable_image_cropping = ThemeService::ConfigValue('ENABLE_IMAGE_CROPPING');
        $this->image_path = image_archive::where('np_related_article_id', $this->cms_article_id)->where('image_is_deleted', 0)->get();

        if(isset($this->image_path[0])){
            $imgObj = $this->image_path[0];
            if(isset($imgObj->media_type)){
                return ImageHelper::getImageSrc($imgObj,$html,$thumb,$embed,$is_focal_point,$type,$enable_image_cropping);
            }elseif($html && $type=='amp-article'){
                return View('theme::components.amp_no_image_src');
            }elseif($html){
                return View('theme::components.no_image_src');
            }else{
                return '';
            }

        }
        else {
            if($isDefaultImage && $html){
                return View('theme::components.no_image_src');
            } else {
                return '';
            }
        }
    }

    /**
     * sets the article->image_caption
     */
    public function getImageCaptionAttribute($html =true,$thumb=true,$embed=false,$type='article'){
        if(!empty($this->image_path) && strlen($this->image_path) > 2 ){
            $imgObj = json_decode(stripslashes($this->image_path));
            if(isset($imgObj->image_caption)){
                return $imgObj->image_caption;
            } else {
                return '';
            }
        }
        return '';
    }


    public function  article_body_info($textInsideParagraph = array(),$articles_array=array()){
        $value = '';
        $article_body = $this->article_body;
        $re = "/(\*\*media\[(\d|,)*]\*\*)/";
        $matches = array();
        preg_match_all($re, $this->article_body, $matches);
        $matches = $matches[0];

        for ($k = 0; $k < count($matches); $k++)
        {
            $strToSearch =$matches[$k];
            $npImageIds = str_replace("**media[", "", $matches[$k]);
            $npImageIds = str_replace("]**", "", $npImageIds);

            if($npImageIds){
                $npImageIds =explode(",",$npImageIds);
                $images = image::whereIn('np_image_id',$npImageIds)->get();
                if($images){
                    $strToReplace = View('theme::components.article_body_images',['images'=>$images,'article'=>$this]);//function To Render Multiple Image
                }
                if(isset($images[0])){
                    $value= str_replace($strToSearch, $strToReplace,$article_body );
                    $article_body = $value;
                }
            }
        }


        $value = empty($value) ? $this->article_body : $value;
        $re = "/(\*\*widget\[(\d|,)*]\*\*)/";
        $matches = array();
        preg_match_all($re, $value, $matches);
        if($matches[0]){
            $matches = $matches[0];
            $strToSearch	= $matches[0];
            $npWidgetId		= str_replace("**widget[", "", $matches[0]);
            $npWidgetId		= str_replace("]**", "", $npWidgetId);
            if($npWidgetId){
                $article_related_ids =  trim($this->related_articles_ids);
                $relatedArticlesIds = explode(',',$article_related_ids);
                $relatedArticles	= article::whereIn('np_article_id',$relatedArticlesIds)->get();
                $strToReplace		= View('theme::components.article_related_body',['relatedArticles'=>$relatedArticles])->render();//function To Render Multiple Image
            }
            $value= str_replace($strToSearch, $strToReplace,$value );
        }

        $value = str_replace('******** async="" charset="utf-8" src="https://platform.twitter.com/widgets.js">********', '', $value);
        $value = str_replace('******** async=&quot;&quot; charset=&quot;utf-8&quot; src=&quot;https://platform.twitter.com/widgets.js&quot;>********', '', $value);
        $value = str_replace('******** async="" defer="defer" src="//platform.instagram.com/en_US/embeds.js">********', '', $value);
        $value = str_replace('******** async=&quot;&quot; defer=&quot;defer&quot; src=&quot;//platform.instagram.com/en_US/embeds.js&quot;>********', '', $value);

        if(!empty($textInsideParagraph)){
            return $this->text_inside_paragraph($value,$textInsideParagraph['paragraph'],$textInsideParagraph['view_name'],false,$articles_array);
        }

        $value = str_replace("\n", "<br>", $value);

        return $value;
    }

    public function article_tags_view($view='',$flag=0){
        $article_tags = explode(',',$this->article_tags);

        if($view){
            return View('theme::components.' . $view,array(
                'article_tags' => $article_tags,
                'flag' => $flag
            ));
        }else{
            return View('theme::components.article_tags',array(
                'article_tags' => $article_tags,
                'flag' => $flag
            ));
        }
    }

    /**
     */
    public function article_tags_view_amp($view='',$flag=false){
        $article_tags = explode(',',$this->article_tags);
        $get_count_images = ThemeService::ConfigValue('ARTICLE_TAGS_COUNT_IMAGE');
        $count_images = $get_count_images ? $this->image()->count() : false;

        if($view){
            return View('theme::components.' . $view,array(
                'article_tags' => $article_tags,
                'flag' => $flag
            ));
        }else{

            return View('theme::components.article_tags_amp',array(
                'article' => $this,
                'count_images' => $count_images,
                'article_tags' => $article_tags,
                'flag' => $flag
            ));
        }
    }

    public function  article_body_clean($keepTags=''){

        $re = '/<[^>]*>/m';
        $bodytext = preg_replace($re, '', $this->article_body);
        $re = "/(\*\*media\[(\d|,)*]\*\*)/";
        $bodytext = preg_replace($re, '', $bodytext);

        $bodytextClean= strip_tags($bodytext,$keepTags);

        return $bodytextClean;
    }

    /**
     * sets the article->simple_url
     */
    public function getSimpleUrlAttribute(){
        return ThemeService::ConfigValue('APP_URL') . 'article/' . $this->cms_article_id;
    }

    /**
     * sets the article->author_name
     */
    public function getAuthorNameAttribute(){
        $author= author::find_cms($this->author_id);
        return isset($author->author_name) ? $author->author_name : '';
    }

    /**
     * sets the article->author_url
     */
    public function getAuthorUrlAttribute(){
        return UrlHelper::build_seo_url($this->author_id, 'author', $this->author_name);
    }

    /**
     * sets the article->seo_url
     */
    public function getSeoUrlAttribute(){
        if(ThemeService::ConfigValue('DO_NOT_USE_ARTICLE_SEO_URL')){
            return $this->getSimpleUrlAttribute();
        }
        return UrlHelper::build_seo_url($this->cms_article_id, 'article_archive', $this->seo_meta_title, $this->section_id, $this->sub_section_id);
    }

    /**
     * sets the article->last_edited
     */
    public function getLastEditedAttribute(){
        return $this->publish_time;
    }

     /**
     * sets the article->section_name
     */
    public function getSectionNameAttribute(){
        $section =  section::find_np($this->section_id);
        return $section->section_name;
    }

    /**
     * sets the article->amp_url
     */
    public function getAmpUrlAttribute(){
        return ThemeService::ConfigValue('APP_URL') . 'ampArticle/' . $this->cms_article_id;
    }

    /*
     * sets the article->amp_article_body
     */
    public function getAmpArticleBodyAttribute() {
        $bodytext = str_replace("<br>", "\n", $this->article_body);
        $bodytext = preg_replace(
            array(
                // Remove invisible content
                '#<script(.*?)>(.*?)</script>#is',
                '/<img[^>]+\>/i',
                '/<iframe.*?\/iframe>/i',
                '/<\s*blockquote.+?<\s*\/\s*blockquote.*?>/ms',
                '/(\*\*media\[(\d|,)*]\*\*)/',
                '~<!--.+?-->~s',
                '/<([a-z][a-z0-9]*)[^>]*?(\/?)>/i' //remove all attributes
            ), array(
            ' ', ' ', ' ', ' ', ' '
        ), $bodytext);

        /************ convert instagram to amp-instagram and replace it in body ************/
        $pattern = '/<div data-oembed-url="https:\/\/www.instagram.com\/p\/([^>]+)\/.*?".*?\>/m';
        preg_match_all($pattern, $bodytext , $matches);
        foreach($matches[0] as $key => $match0) {
            $replaces_str = '<div><amp-instagram class="no-bottom" data-shortcode="' . $matches[1][$key] . '" width="1" height="1" layout="responsive"></amp-instagram>';
            $bodytext= str_replace($matches[0][$key], $replaces_str, $bodytext);
        }
        /************ convert instagram to amp-instagram and replace it in body ************/

        /************ convert twitter to amp-twitter and replace it in body ************/
        $pattern = '/<div data-oembed-url="https:\/\/twitter.com\/([a-zA-Z0-9_]{1,15})\/status\/([^>]+)">/m';
        preg_match_all($pattern, $bodytext , $matches);
        foreach($matches[0] as $key => $match0) {
            $replaces_str = '<div><amp-twitter  width="375" height="472" layout="responsive" data-tweetid="' . $matches[2][$key] . '" ></amp-twitter>';
            $bodytext= str_replace($matches[0][$key], $replaces_str, $bodytext);
        }
        /************ convert twitter to amp-twitter and replace it in body ************/


        /************ convert facebook to amp-facebook and replace it in body ************/
        $pattern = '/<div data-oembed-url="(https:\/\/www.facebook.com\/.*?)">/m';
        preg_match_all($pattern, $bodytext , $matches);
        foreach($matches[0] as $key => $match0) {
            $replaces_str = '<div><amp-facebook  width="1" height="1" layout="responsive" data-href="' . $matches[1][$key] . '" ></amp-facebook>';
            $bodytext= str_replace($matches[0][$key], $replaces_str, $bodytext);
        }
        /************ convert facebook to amp-facebook and replace it in body ************/


        $bodytext = str_replace("\n", "<br>", $bodytext);

        return $bodytext;
    }

    public function special_amp_article_body($relatedArticles=array()) {
        $bodytext = str_replace("<br>", "\n", $this->article_body);
        $bodytext = str_replace("&quot;", '', $bodytext);
        $bodytext = str_replace('href=""', "href=''", $bodytext);
        $bodytext = preg_replace(
            array(
                '/<\s*style.+?<\s*\/\s*style.*?>/si',
                '/\s+tagline_ar\s*=\s*"\s*"/m',//remove  attribute tagline_ar empty in tags
                '/<p(.*?)>/m',//clean tag p
                '/<img[^>]+\>/i',
                '/<\s*blockquote.+?<\s*\/\s*blockquote.*?>/ms',
                '/<iframe.*?\/iframe>/i',
                '/(\*\*media\[(\d|,)*]\*\*)/',
                '#<script(.*?)>(.*?)</script>#is',
                '~<!--.+?-->~s',
            ),
            array('','','<p>', '', ' ', ' ', ' ','' ),$bodytext);


        /************ convert instagram to amp-instagram and replace it in body ************/
        $pattern = '/<div data-oembed-url="https:\/\/www.instagram.com\/p\/([^>]+)\/.*?".*?\>/m';
        preg_match_all($pattern, $bodytext , $matches);
        foreach($matches[0] as $key => $match0) {
            $replaces_str = '<div><amp-instagram class="no-bottom" data-shortcode="' . $matches[1][$key] . '" width="1" height="1" layout="responsive"></amp-instagram>';
            $bodytext= str_replace($matches[0][$key], $replaces_str, $bodytext);
        }
        /************ convert instagram to amp-instagram and replace it in body ************/

        /************ convert twitter to amp-twitter and replace it in body ************/
        $pattern = '/<div data-oembed-url="https:\/\/twitter.com\/([a-zA-Z0-9_]{1,15})\/status\/([^>]+)">/m';
        preg_match_all($pattern, $bodytext , $matches);
        foreach($matches[0] as $key => $match0) {
            $replaces_str = '<div><amp-twitter  width="375" height="472" layout="responsive" data-tweetid="' . $matches[2][$key] . '" ></amp-twitter>';
            $bodytext= str_replace($matches[0][$key], $replaces_str, $bodytext);
        }
        /************ convert twitter to amp-twitter and replace it in body ************/

        /************ convert facebook to amp-facebook and replace it in body ************/
        $pattern = '/<div data-oembed-url="(https:\/\/www.facebook.com\/.*?)">/m';
        preg_match_all($pattern, $bodytext , $matches);
        foreach($matches[0] as $key => $match0) {
            $replaces_str = '<div><amp-facebook  width="1" height="1" layout="responsive" data-href="' . $matches[1][$key] . '" ></amp-facebook>';
            $bodytext= str_replace($matches[0][$key], $replaces_str, $bodytext);
        }
        /************ convert facebook to amp-facebook and replace it in body ************/

        $bodytext = str_replace("\n", "<br>", $bodytext);

		if(!empty($relatedArticles)){
            return $this->text_inside_paragraph_amp($bodytext,2,false,$relatedArticles);
		}

        return $bodytext;
    }


    public static function updateArchivePermalink($cmsArticleId,$permalink){
        article_archive::where('cms_article_id', $cmsArticleId)->update(array('permalink'=>$permalink));
        return 0;
    }



	public static function cache_home_widget($count,$sections,$subsections){
		//caches the value for the lifetime of the request so once we set the page_id in the controller we would be able to access it with this service elsewhere
       $articles_x =[];
		$articles_x = Cache::remember("cache_articlesFilter_".implode("_", $sections)."_".implode("_", $subsections)."_".$count, self::cached_minutes, function ()use ($count,$sections,$subsections) {
			$articles =[];
			$article_archive = \App\Models\article_archive_lastdays::where("publish_time",'<',DB::raw(ThemeService::ConfigValue('GIVEN_TIME')));
					if (!empty($sections)) {
						$archive_sections =[];
						foreach($sections as $section){
							$archive_sections[] = section::find_np($section)->cms_section_id;
						}
						$article_archive = $article_archive->whereIn('section_id', $archive_sections);
					}
					if (!empty($subsections)) {
						$archive_subsections =[];
						foreach($subsections as $sub_section){
							$archive_subsections[] = sub_section::find_np($sub_section)->cms_sub_section_id;
						}

						$article_archive = $article_archive->whereIn('sub_section_id', $archive_subsections);
					}


					//$article_archive = $article_archive->has('image');

					$article_archive = $article_archive->orderBy('publish_time', 'desc')->limit($count);



					$article_archive = $article_archive->get();
					foreach ($article_archive as $article_arch){
                        $theme_controller_class = 'Themes\\'.ThemeService::Name().'\\controllers\\'.  CommonController::controller_class_name();
                        if(class_exists($theme_controller_class)){
                            $themeController = new $theme_controller_class();
                            if(method_exists($themeController, 'getArchiveArticle')) {
                                $function_name ='getArchiveArticle';
                                $articles[]= $themeController->$function_name($article_arch,'getFirstImage');
                            }
                        }
					}

                return $articles;
			});
		return $articles_x;
    }


}
