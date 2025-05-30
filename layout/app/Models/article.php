<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Layout\Website\Helpers\ImageHelper;
use Layout\Website\Helpers\UrlHelper;
use Layout\Website\Services\PageService;
use Layout\Website\Services\ThemeService;
use App\Http\Controllers\CommonController;
use Illuminate\Support\Facades\DB;
use \Illuminate\Support\Arr;
use Layout\Website\Services\WidgetService;
use Illuminate\Support\Facades\Cache;

class article extends Model {

    protected $table = "article";
    protected $primaryKey = 'cms_article_id';
    public $timestamps = false;
    protected $fillable = ['cms_article_id', 'np_article_id', 'article_name', 'article_title', 'article_headline', 'article_subtitle', 'article_body', 'article_custom_fields', 'cms_type', 'author_id', 'section_id', 'seo_meta_keywords', 'seo_meta_description', 'seo_meta_title', 'publish_time', 'related_articles_ids', 'article_tags', 'sub_section_id', 'visit_count', 'sponsored_flag', 'offer_flag', 'featured_article_flag', 'media_gallery_flag', 'video_gallery_flag', 'highlight_flag', 'top_story_flag', 'is_updated', 'is_old_article', 'old_article_id', 'article_byline', 'ts', 'last_edited', 'alt_publish_time', 'image_path', 'author_name', 'section_name', 'sub_section_name', 'slide_show', 'breaking_news', 'visit_count_update_date', 'old_cms_article_id', 'permalink', 'show_image_in_thumb', 'api_status', 'a_custom_data', 'publication_id', 'max_publish_time', 'page_number', 'homepage_article_flag', 'article_shortlink', 'cropped_image'];

    const cached_minutes = 10;

    public static function find_np($np_id) {
        return self::where('np_article_id', $np_id)->first();
    }

    public static function find($cms_id) {
        if (is_numeric($cms_id)) {
            return self::where('cms_article_id', $cms_id)->first();
        }
    }

    public function getSectionInfoAttribute() {
        if ($this->section_id) {
            if ($this->is_old_article) {
                $section = section::find_by_cms_id($this->section_id);
            } else {
                $section = section::find_np($this->section_id);
            }
            if (!empty($section)) {
                return $section->section_info ? $section->section_info : $section->section_name;
            }
            return '';
        } else {
            return '';
        }
    }

    public function getSubSectionInfoAttribute() {
        if ($this->sub_section_id) {
            if ($this->is_old_article) {
                $sub_section = sub_section::find_by_cms_id($this->sub_section_id);
            } else {
                $sub_section = sub_section::find_np($this->sub_section_id);
            }
            if (!empty($sub_section)) {
                return $sub_section->sub_section_info ? $sub_section->sub_section_info : $sub_section->sub_section_name;
            }
            return '';
        } else {
            return '';
        }
    }

    public function getSectionAttribute() {
        if ($this->is_old_article) {
            return section::find_by_cms_id($this->section_id);
        } else {
            return section::find_np($this->section_id);
        }
    }

    public function getSubSectionAttribute() {
        return sub_section::find_np($this->sub_section_id);
    }

    public function author() {
        //return $this->hasMany('App\Models\image','np_related_article_id','np_article_id');
        $relation = $this->hasOne('App\Models\author', 'np_author_id', 'author_id');
        //$relation->getQuery()->where('is_old_image',$this->is_old_article)->where('image_is_deleted', 0);
        return $relation;
    }

    public function section() {
        //return $this->hasMany('App\Models\image','np_related_article_id','np_article_id');
        $relation = $this->hasMany('App\Models\section', 'np_section_id', 'section_id');
        //$relation->getQuery()->where('is_old_image',$this->is_old_article)->where('image_is_deleted', 0);
        return $relation;
    }

    public function related_articles_table() {
        $relation = $this->hasMany('App\Models\related_articles', 'article_id', 'np_article_id');
        return $relation;
    }

    public function article_multi_section() {
        $relation = $this->hasMany('App\Models\article_multi_section', 'ams_article_id', 'cms_article_id');
        return $relation;
    }

    public function image() {
        $relation = $this->hasMany('App\Models\image', 'np_related_article_id', 'np_article_id')->where("image_is_deleted","0");
        return $relation;
    }

    public function tag() {
        return $this->hasMany('App\Models\article_tags', 'np_article_id', 'np_article_id');
    }

    public function web_data_values() {
        return $this->hasMany('App\Models\web_data_values', 'cms_article_id', 'cms_article_id');
    }

    public function getSeoMetaTitleAttribute($value) {
        return (!empty($value)) ? $value : (!empty($this->article_title) ? $this->article_title : "noTitle" );
    }

    public static function rssArticlesCache($section, $sub_section, $cached_minutes = self::cached_minutes,$get_all_sections=false) {
        //caches the value for the lifetime of the request so once we set the page_id in the controller we would be able to access it with this service elsewhere

        $section_id  = !empty($section->cms_section_id) ? $section->cms_section_id : 0;
        $sub_section_id  = !empty($sub_section->cms_sub_section_id) ? $sub_section->cms_sub_section_id : 0;
        $articles = Cache::remember("rssArticlesCache_" . $section_id . "_" . $sub_section_id, $cached_minutes, function ()use ($section, $sub_section,$get_all_sections) {

                    if ($get_all_sections) {
                        $ams_ids = article_multi_section::select("ams_article_id")->where('ams_article_date', '<', DB::raw(ThemeService::ConfigValue('GIVEN_TIME')))->orderBy('ams_article_date', 'desc')->limit(50)->get();
                    }else if (isset($sub_section->np_sub_section_id)) {
                        $ams_ids = article_multi_section::select("ams_article_id")->where('ams_article_date', '<', DB::raw(ThemeService::ConfigValue('GIVEN_TIME')))->where('ams_subsection_id', $sub_section->np_sub_section_id)->orderBy('ams_article_date', 'desc')->limit(50)->get();
                    } elseif (isset($section->np_section_id)) {
                        $ams_ids = article_multi_section::select("ams_article_id")->where('ams_article_date', '<', DB::raw(ThemeService::ConfigValue('GIVEN_TIME')))->where('ams_section_id', $section->np_section_id)->orderBy('ams_article_date', 'desc')->limit(50)->get();
                    } else {
                        $ams_ids = article_multi_section::select("ams_article_id")->where('ams_article_date', '<', DB::raw(ThemeService::ConfigValue('GIVEN_TIME')))->limit(50)->orderBy('ams_article_date', 'desc')->get();
                    }

                    if (ThemeService::ConfigValue('ENABLE_MAX_PUBLISH_TIME')) {
                        $articles_tmp = self::whereIn("cms_article_id", $ams_ids)->orderBy('max_publish_time', 'DESC')->limit(50)->get();
                    } else {
                        $articles_tmp = self::whereIn("cms_article_id", $ams_ids)->orderBy('publish_time', 'DESC')->limit(50)->get();
                    }

                    if (count($articles_tmp) < 50) {
                        $count_archive = 50 - count($articles_tmp);
                        if (isset($sub_section->cms_sub_section_id)) {
                            $ams_ids = article_multi_section_archive::distinct('ams_article_id')
                                            ->select('ams_article_id')
                                            ->where('ams_article_date', '<', DB::raw(ThemeService::ConfigValue('GIVEN_TIME')))
                                            ->where('ams_subsection_id', $sub_section->cms_sub_section_id)
                                            ->orderBy('ams_article_date', 'desc')
                                            ->limit($count_archive)->get();
                        } elseif (!empty($section->cms_section_id)) {
                            $ams_ids = article_multi_section_archive::distinct('ams_article_id')
                                            ->select('ams_article_id')
                                            ->where('ams_article_date', '<', DB::raw(ThemeService::ConfigValue('GIVEN_TIME')))
                                            ->where('ams_section_id', $section->cms_section_id)
                                            ->orderBy('ams_article_date', 'desc')
                                            ->limit($count_archive)->get();
                        } else {
                            $ams_ids = article_multi_section_archive::distinct('ams_article_id')
                                            ->select('ams_article_id')
                                            ->where('ams_article_date', '<', DB::raw(ThemeService::ConfigValue('GIVEN_TIME')))
                                            ->orderBy('ams_article_date', 'desc')
                                            ->limit($count_archive)->get();
                        }
                        $articles_archive = article_archive::whereIn("cms_article_id", $ams_ids)->orderBy('publish_time', 'desc')->limit($count_archive)->get();
                        $theme_controller_class = 'Themes\\' . ThemeService::Name() . '\\controllers\\' . CommonController::controller_class_name();
                        if (class_exists($theme_controller_class)) {
                            $themeController = new $theme_controller_class();
                            if (method_exists($themeController, 'getArchiveArticle')) {
                                $function_name = 'getArchiveArticle';
                                foreach ($articles_archive as $archive_articles_list) {
                                    $articles_tmp[] = $themeController->$function_name($archive_articles_list);
                                }
                            }
                        }
                    }
                    return $articles_tmp;
                });
        return isset($articles) ? $articles : [];
    }

    public function article_body_rssIa($length = 0) {



        $re = '/<p[^>]*>/m';
        $article_body = preg_replace($re, '[##PTAG##]', $this->article_body);
        $re = '/<\/p[^>]*>/m';
        $article_body = preg_replace($re, '[##EPTAG##]', $article_body);
        $re = '/<br[^>]*><br[^>]*>/m';
        $article_body = preg_replace($re, '[##BRTAG##]', $article_body);
        $re = '/<br[^>]*>/m';
        $article_body = preg_replace($re, '[##BRTAG##]', $article_body);

        $re = '/<b[^>]*>/m';
        $article_body = preg_replace($re, '[##BTAG##]', $article_body);
        $re = '/<\/b[^>]*>/m';
        $article_body = preg_replace($re, '[##EBTAG##]', $article_body);


        /* replace body image code by amp image version */
        $re = "/(\*\*media\[(\d|,)*]\*\*)/";
        $matches = array();
        preg_match_all($re, $article_body, $matches);
        if (!empty($matches[0])) {
            $matches = $matches[0];

            for ($k = 0; $k < count($matches); $k++) {
                $strToSearch = $matches[$k];
                $npImageIds = str_replace("**media[", "", $matches[$k]);
                $npImageIds = str_replace("]**", "", $npImageIds);


                if ($npImageIds) {
                    $npImageIds = explode(",", $npImageIds);
                    $images = image::whereIn('np_image_id', $npImageIds)->get();
                    if ($images) {
                        $strToReplace = View('theme::components.rssia_article_body_images', ['images' => $images]); //function To Render Multiple and single Image
                    }
                    if (isset($images[0])) {
                        $value = str_replace($strToSearch, $strToReplace, $article_body);
                        $article_body = $value;
                    } else {
                        /* if idForImage exist but image not exist in table remove **media[idForImage]** */
                        $value = str_replace($strToSearch, '', $article_body);
                        $article_body = $value;
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
                    $images = image::whereIn('np_image_id', $npImageIds)->get();
                    if ($images) {
                        $strToReplace = View('theme::components.rssia_article_body_images', [
                            'images' => $images,
                            'width_size' => $width_size
                        ]); //function To Render Multiple and single Image
                    }
                    if (isset($images[0])) {
                        $value = str_replace($strToSearch, $strToReplace, $article_body);
                        $article_body = $value;
                    } else {
                        /* if idForImage exist but image not exist in table remove **NP_IMAGE_BODY** */
                        $value = str_replace($strToSearch, '', $article_body);
                        $article_body = $value;
                    }
                }
            }
        }


        $pattern = '/<iframe(.*)src="https:\/\/www.youtube.com\/embed\/([a-zA-Z0-9_-]{1,15})?(.*?)"(.*?)<\/iframe>/m';
        preg_match_all($pattern, $article_body, $matches);
        foreach ($matches[0] as $key => $match0) {
            $replaces_str = '</p><figure class="op-interactive"><iframe width="560" height="315" src="https://www.youtube.com/embed/' . $matches[2][$key] . '?rel=0"></iframe></figure><p>';
            $article_body = str_replace($matches[0][$key], $replaces_str, $article_body);
        }



        //
        //		$re = '/<[^>]*>/m';
        //		$re = '#<(?!/?(img|figure|figcaption)\b)[^>]+>#';
        //      $article_body = preg_replace($re, '', $article_body);



        $re = "/(\*\*media\[(\d|,)*]\*\*)/";
        $article_body = preg_replace($re, '', $article_body);

        $re = '/\[caption (.*)\](.*)\[\/caption\]/';
        $article_body = preg_replace($re, '', $article_body);

        $re = '/id[s]*\=\"(.*?)\"/';
        $article_body = preg_replace($re, '', $article_body);

        $re = '/\[gallery (.*)\]/';
        $article_body = preg_replace($re, '', $article_body);

        $article_body = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $article_body);

        $article_body = str_replace('[##PTAG##][##BRTAG##]', '<p>', $article_body);
        $article_body = str_replace('[##PTAG##]', '<p>', $article_body);
        $article_body = str_replace('[##EPTAG##]', '</p>', $article_body);


        $article_body = str_replace('[##BTAG##]', '<b>', $article_body);
        $article_body = str_replace('[##EBTAG##]', '</b>', $article_body);

        $content_article = explode('[##BRTAG##]', $article_body);

        if (count($content_article) > 1) {
            $article_body = '';
            foreach ($content_article as $content_article_list) {
                if (empty($content_article_list)) {
                    continue;
                }
                if (strpos($content_article_list, '<p>') !== false || strpos($content_article_list, '</p>') !== false) {
                    $article_body .= $content_article_list;
                } else {
                    $article_body .= '<p>' . $content_article_list . '</p>';
                }
            }
        }


        $content_article_ptag = explode('<p>', $article_body);

        if (count($content_article_ptag) > 1) {
            $article_body = '';
            foreach ($content_article_ptag as $content_article_ptag_list) {
                if (empty($content_article_ptag_list)) {
                    continue;
                }

                if (strpos($content_article_ptag_list, '<figure>') !== false && strpos($content_article_ptag_list, '</p>') !== false) {
                    $content_article_ptag_list = str_replace('[##IMGPTAG##]', '<p>', $content_article_ptag_list);
                    $content_article_ptag_list = str_replace('[##IMGEPTAG##]', '</p>', $content_article_ptag_list);
                }
                if (strpos($content_article_ptag_list, '</p>') !== false) {
                    $article_body .= '<p>' . $content_article_ptag_list;
                } else {
                    $article_body .= $content_article_ptag_list;
                }
            }
        }

        $article_body = str_replace('[##IMGEPTAG##]', '', $article_body);
        $article_body = str_replace('[##IMGPTAG##]', '', $article_body);

        $article_body = str_replace('<p></p>', '', $article_body);
        $article_body = str_replace('<p> </p>', '', $article_body);
        $article_body = str_replace('</p></p>', '</p>', $article_body);
        $article_body = str_replace('<p><p>', '<p>', $article_body);

        return $article_body;
    }

    public function article_body_text($length = 0) {
        $re = '/<[^>]*>/m';
        $article_body = preg_replace($re, '', $this->article_body);
        $re = "/(\*\*media\[(\d|,)*]\*\*)/";
        $article_body = preg_replace($re, '', $article_body);
        $re = '/\[caption (.*)\](.*)\[\/caption\]/';
        $article_body = preg_replace($re, '', $article_body);
        $re = '/id[s]*\=\"(.*?)\"/';
        $article_body = preg_replace($re, '', $article_body);
        $re = '/\[gallery (.*)\]/';
        $article_body = preg_replace($re, '', $article_body);
        if ($length > 0) {
            return substr($article_body, 0, $length) . ' ...';
        } else {
            return $article_body;
        }
    }

    public function article_body_clean($keepTags = '') {


        $re = "#<script(.*?)>(.*?)</script>#is";
        $bodytext = preg_replace($re, '', $this->article_body);
        $re = '/<[^>]*>/m';
        $bodytext = preg_replace($re, '', $bodytext);
        $re = "/(\*\*media\[(\d|,)*]\*\*)/";
        $bodytext = preg_replace($re, '', $bodytext);

        $re = "/(\*\*carousel\[(.*)]\*\*)/U";
        $bodytext = preg_replace($re, '', $bodytext);

        $bodytextClean = strip_tags($bodytext, $keepTags);
        $bodytextClean = str_replace("\n", "", $bodytextClean);
        $bodytextClean = str_replace("\\", " ", $bodytext);
        // dd($bodytextClean);
        if (ThemeService::ConfigValue('BODY_CLEAN_TABS')) {
            $bodytextClean = str_replace(["\r\n", "\t"], "", $bodytextClean);

            $re = '/(\s)(\s)+/';
            $bodytextClean = preg_replace($re, ' ', $bodytextClean);
        }
        return $bodytextClean;
    }

    /*
     * sets the article->amp_article_body
     */

    public function getAmpArticleBodyAttribute() {
        //$bodytext = str_replace("<br>", "\n", $this->article_body);
        $bodytext = $this->article_body;

        /*         * ********** convert youtube to amp-youtube and replace it in body *********** */
        $pattern = '/<iframe(.*)src="https:\/\/www.youtube.com\/embed\/([a-zA-Z0-9_-]{1,15})?(.*?)"(.*?)<\/iframe>/m';
        preg_match_all($pattern, $bodytext, $matches);
        foreach ($matches[0] as $key => $match0) {
            $replaces_str = '<amp-youtube data-videoid="' . $matches[2][$key] . '" layout="responsive"  width="480"  height="270"> </amp-youtube>';
            $bodytext = str_replace($matches[0][$key], $replaces_str, $bodytext);
        }
        /*         * ********** convert youtube to amp-youtube and replace it in body *********** */

        /*         * ********** convert youtube to amp-youtube and replace it in body *********** */
        $pattern = '/<iframe(.*)src="\/\/www.youtube.com\/embed\/([a-zA-Z0-9_-]{1,15})?(.*?)"(.*?)<\/iframe>/m';
        preg_match_all($pattern, $bodytext, $matches);
        foreach ($matches[0] as $key => $match0) {
            $replaces_str = '<amp-youtube data-videoid="' . $matches[2][$key] . '" layout="responsive"  width="480"  height="270"> </amp-youtube>';
            $bodytext = str_replace($matches[0][$key], $replaces_str, $bodytext);
        }
        /*         * ********** convert youtube to amp-youtube and replace it in body *********** */

        $bodytext = str_replace('<br type="_moz" />', '<br />', $bodytext);
        /*         * ********** convert youtube to amp-youtube and replace it in body *********** */
        $pattern = '/<iframe(.*)src=&quot;https:\/\/www.youtube.com\/embed\/(.*?)&quot;(.*?)<\/iframe>/m';
        preg_match_all($pattern, $bodytext, $matches);
        foreach ($matches[0] as $key => $match0) {
            $matches[2][$key] = str_replace(["?rel=0", "?controls=0", "&amp;showinfo=0", "&showinfo=0"], "", $matches[2][$key]);
            $replaces_str = '<amp-youtube data-videoid="' . $matches[2][$key] . '" layout="responsive"  width="480"  height="270"> </amp-youtube>';
            $bodytext = str_replace($matches[0][$key], $replaces_str, $bodytext);
        }
        /*         * ********** convert youtube to amp-youtube and replace it in body *********** */

        /*         * ********** convert facebook to amp-facebook and replace it in body *********** */
        $pattern = '/<iframe src=&quot;https:\/\/www.facebook.com\/(.*?)href=(.*?)&quot;(.*?)\/iframe>/m';
        preg_match_all($pattern, $bodytext, $matches);
        foreach ($matches[0] as $key => $match0) {
            $replaces_str = '<amp-facebook width="552" height="310" layout="responsive"  data-href="' . urldecode($matches[2][$key]) . '"> </amp-facebook>';
            $bodytext = str_replace($matches[0][$key], $replaces_str, $bodytext);
        }
        /*         * ********** convert facebook to amp-facebook and replace it in body *********** */

        /*         * ********** convert twitter to amp-twitter and replace it in body *********** */
        $pattern = '/<blockquote(.*?)twitter(.*?)https:\/\/twitter.com\/(.*?)\/status\/([a-zA-Z0-9_]+)(.*?)<\/blockquote>/m';
        preg_match_all($pattern, $bodytext, $matches);
        foreach ($matches[0] as $key => $match0) {
            $replaces_str = '<amp-twitter width="375" height="472" layout="responsive" data-tweetid="' . $matches[4][$key] . '" ></amp-twitter>';
            $bodytext = str_replace($matches[0][$key], $replaces_str, $bodytext);
        }
        /*         * ********** convert twitter to amp-twitter and replace it in body *********** */

        /*         * ********** convert instagram to amp-instagram and replace it in body *********** */
        $pattern = '/<blockquote(.*?)instagram(.*?)https:\/\/www.instagram.com\/p\/([a-zA-Z0-9_]+)(.*?)<\/blockquote>/m';
        preg_match_all($pattern, $bodytext, $matches);
        foreach ($matches[0] as $key => $match0) {
            $replaces_str = '<amp-instagram class="no-bottom" data-shortcode="' . $matches[3][$key] . '" width="1" height="1" layout="responsive"></amp-instagram>';
            $bodytext = str_replace($matches[0][$key], $replaces_str, $bodytext);
        }
        /*         * ********** convert instagram to amp-instagram and replace it in body *********** */

        //$bodytext = str_replace("&quot;", '', $bodytext);
        
        $bodytext = preg_replace('/<a(.*)(href="x-(.*)")>(.*)<\/a>/', '$4',$bodytext);
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
            '/<meta [^>]+>/m', //remove meta tags from body
            '/(<gdiv>)|(<\/gdiv>)/m', //remove tag gdiv
            '/<a(.*?)title=".*?(\').*?"\>/', //remove title attribute contain single quote in <a> tag
            '/<h2(.*?)title=".*?(\').*?"\>/', //remove title attribute contain single quote in <h2> tag
            '/<li(.*?)title=".*?(\').*?"\>/',//remove title attribute contain single quote in <li> tag
            '/<a (.*?) spellcheck="(.*?)" (.*?)>/',//remove spellcheck attribute in <a> tag
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
            '/<B</',
            '/<b</',
            '/<\?xml[^>]+\>/i',
            '/<o:p.*?\/o:p>/i',     
            '/<end.*?\/end>/i'     
                ), array('<table$1 $3>', '<div $1 $5>', '<div $1 $5>', '<div $1 $5>', '<form$1 action-xhr=$2 $3>','/>', '', '', '>', '', '', '', '', '', '<p>', '', ' ', ' ', ' ', '', ' ', '', '', '', '<a $1 >', '<h2 $1 >', '<li $1 >', '<a $1 $3>', ' ', ' ', '$4 ', ' $2 ','<div$1$3$5>', '<div$1$3$5>', '<div$1$2>', '<div$1>', '<div$1$3>','<a$1$3>','<a$1$3>', '<a$1target="_blank"$2>','<', '<','','',''), $bodytext);


        $bodytext = CommonController::cleanAmpBodyText($bodytext, 'hl2,font', 'span,br,hr');

        if (ThemeService::ConfigValue('REMOVE_ATTRIBUTES')) {
            $bodytext = CommonController::cleanAmpBodyAttributes($bodytext, ThemeService::ConfigValue('REMOVE_ATTRIBUTES'));
        }

        /**  replace spaces with dashes in href */
        $re = '/href=\"([^"]*)(\s)(.*)\"/U';
        preg_match_all($re, $bodytext, $matches);
        if(!empty($matches)){
            foreach($matches[0] as $key=>$match0){
                $bodytext= str_replace($matches[0][$key], str_replace([" ",":"],["-",""],$matches[0][$key]), $bodytext);
            }
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

        /*           *      *********** convert a href iframe to correct iframe form *********** */
        $pattern = '/<a href="&lt;iframe(.*?)>Facebook<\/a>/';
        preg_match_all($pattern, $bodytext, $matches);
        foreach($matches[1] as $key => $match0){
            $replaces_str = '<div><amp-iframe'. $match0.'></amp-iframe></div>';
            $bodytext = str_replace($matches[0][$key], $replaces_str, $bodytext);
            $bodytext = str_replace('&quot;', '"',$bodytext);
        }
        /*           *      *********** convert a href iframe to correct iframe form *********** */

        /*           *      *********** remove malito invalid *********** */

        $pattern = '/<a (.*)m\$&amp;\*n&pound;-k\*-n@&pound;k\^!(.*)<\/a>/';
        $bodytext = preg_replace($pattern, ' ', $bodytext);

        /*           *      *********** remove malito invalid *********** */

        $bodytext = str_replace('"', "'", $bodytext);
        //$bodytext = str_replace("\n", "<br>", $bodytext);
        //$bodytext =preg_replace("/<br><br>/m", "<br>", $bodytext);//one <br> in cms added 2<br>in db this code to convert it to one <br>

        return $bodytext;
    }

    public function special_amp_article_body($relatedArticles = array()) {
        //$bodytext = str_replace("<br>", "\n", $this->article_body);

        $bodytext = $this->article_body;

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
        $bodytext = str_replace(['�?','“','”','\"'], ['"','"','"','"'], $bodytext);
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
            '/<a(.*)href=("|\')file:(.*)>(.*)<\/a>/',
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

    public function getRelatedArticlesAttribute() {
        $article_related_ids = trim($this->related_articles_ids);
        if (strlen($article_related_ids) > 0) {
            $article_related_ids = explode(",", $article_related_ids);
            return article::wherein("np_article_id", $article_related_ids)->get();
        }
        return false;
    }

    public static function get_articles_menu($section_id, $limit = 2) {
        $time_field = (ThemeService::ConfigValue('ENABLE_MAX_PUBLISH_TIME') && \Illuminate\Support\Facades\Schema::hasColumn("article", "max_publish_time")) ? 'max_publish_time' : 'publish_time';
        if ($section_id > 0) {
            return self::where('section_id', $section_id)->where($time_field, '<', DB::raw('now()'))->orderBy($time_field, "desc")->limit($limit)->get();
        } else {
            return self::where($time_field, '<', DB::raw('now()'))->orderBy($time_field, "desc")->limit($limit)->get();
        }
    }

    public function getSphinxRelatedArticlesAttribute() {

        if (empty($this->related_articles_ids)) {
            return false;
        }
        $sphinx = related_articles::where('article_id', $this->np_article_id)->first();
        $sphinx_articles_ids = $sphinx->related_ids;
        if (strlen($sphinx_articles_ids) > 0) {
            $sphinx_articles_ids = explode(",", $sphinx_articles_ids);
            $relatedArticles = article::wherein("np_article_id", $sphinx_articles_ids)->get();
            $articleRelatedIds = explode(',', $this->related_articles_ids);
            foreach ($relatedArticles as $key => $relatedArticle) {
                if (in_array($relatedArticle->np_article_id, $articleRelatedIds)) {
                    $relatedArticles = Arr::except($relatedArticles, $key);
                }
            }
            return $relatedArticles;
        }

        return false;
    }

    public function time_ago($with_time = false, $diff = false, $language = 'ar', $time_per_day = false) {

        $originalDate = $this->publish_time;
        if ($diff) {
            $now = new \DateTime();
            $date = new \DateTime($originalDate);
            $interval = $date->diff($now);

            $newDate = $interval->format('%a');

            if ($language == 'ar') {
                if ($newDate < 1) {
                    return '';
                }
                if ($newDate >= 1 && $newDate < 2) {
                    $newDate = '1d ago';
                } elseif ($newDate >= 2) {
                    $newDate = date("d M Y", strtotime($originalDate));
                } elseif (empty($newDate) || $newDate === 0) {
                    $newDate = $interval->format('%h');
                    if ($newDate >= 1) {
                        $newDate .= 'h ago';
                    }
                    if (empty($newDate) || $newDate === 0) {
                        $newDate = $interval->format('%i');
                        if ($newDate > 0) {
                            $newDate .= 'm ago';
                        }

                        if (empty($newDate) || $newDate === 0) {
                            $newDate = '';
                        }
                    }
                }
            } else {
                if ($newDate >= 1 && $newDate < 2) {
                    $newDate = '1d ago';
                } elseif ($newDate >= 2) {
                    $newDate = date("d M Y", strtotime($originalDate));
                } elseif (empty($newDate) || $newDate === 0) {
                    $newDate = $interval->format('%h');
                    if ($newDate >= 1) {
                        $newDate .= 'h ago';
                    }
                    if (empty($newDate) || $newDate === 0) {
                        $newDate = $interval->format('%i');
                        if ($newDate > 0) {
                            $newDate .= 'm ago';
                        }

                        if (empty($newDate) || $newDate === 0) {
                            $newDate = '';
                        }
                    }
                }
            }
        } else {
            $newDate = date("d M Y", strtotime($originalDate));
        }

        return $newDate;
    }

    public function getDateDifferenceAttribute() {
        return \Layout\Website\Helpers\DateTimeHelper::getDateDifference($this->publish_time, '', false);
    }

    public function article_body_info($textInsideParagraph = array(), $articles_array = array(), $parameter_array = array()) {
        $value = '';
        $align_dir = '';
        $width_size = '';
        $theme_controller_class = 'Themes\\' . ThemeService::Name() . '\\controllers\\' . CommonController::controller_class_name();
        if (class_exists($theme_controller_class)) {
            $themeController = new $theme_controller_class();
            if (method_exists($themeController, 'workWithArticleBody')) {
                $function_name = 'workWithArticleBody';
                $this->article_body = $themeController->$function_name($this->article_body);
            }
            if (method_exists($themeController, 'replaceHttpByHttps')) {
                $function_name = 'replaceHttpByHttps';
                $this->article_body = $themeController->$function_name($this->article_body);
            }
        }



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
                } else {
                    /* if idForImage exist but image not exist in table remove **media[idForImage]** */
                    $value = str_replace($strToSearch, '', $article_body);
                    $article_body = $value;
                }
            }
        }



        $re = '/<img(.*?)(\**NP_IMAGE_BODY\[.*?]\**)(.*?)\/>/im';
        $fullMatches = array();
        preg_match_all($re, $this->article_body, $fullMatches);
        $matches = $fullMatches[2];
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
                    $reStyle = '/(.*?)(float:)(.*?)(;)/im';
                    $matchesStyle = array();
                    preg_match_all($reStyle, $fullMatches[3][$k], $matchesStyle);
                    if (isset($matchesStyle[3][0])) {
                        $align_dir = $matchesStyle[3][0];
                    }
                    
                    if($align_dir == ""){
                        $reStyle = '/(.*?)(style=(\'|")float:)(.*?)(\'|")/im';
                        $matchesStyle = array();
                        preg_match_all($reStyle, $fullMatches[3][$k], $matchesStyle);
                        if (isset($matchesStyle[4][0])) {
                            $align_dir = $matchesStyle[4][0];
                        }
                    }

                    $reWidthStyle = '/(.*?)(width:)(.*?)(")/im';
                    $matchesWidthStyle = array();
                    preg_match_all($reWidthStyle, $fullMatches[3][$k], $matchesWidthStyle);
                    if (isset($matchesWidthStyle[3][0])) {
                        $width_size = $matchesWidthStyle[3][0];
                    }
                    
                    if($width_size == ""){
                        $reWidthStyle = '/(.*?)(width=(\'|"))(.*?)(\'|")/im';
                        $matchesWidthStyle = array();
                        preg_match_all($reWidthStyle, $fullMatches[3][$k], $matchesWidthStyle);
                        if (isset($matchesWidthStyle[4][0])) {
                            $width_size = $matchesWidthStyle[4][0];
                        }
                    }
                }
                $npImageIds = explode(",", $npImageIds);
                $images = image::whereIn('np_image_id', $npImageIds)->get();
                if ($images) {
                    if (empty($parameter_array['article_body_images_view'])) {
                        $article_body_images_view = 'article_body_images';
                    } else {
                        $article_body_images_view = $parameter_array['article_body_images_view'];
                    }
                    $strToReplace = View('theme::components.' . $article_body_images_view, [
                        'images' => $images,
                        'article' => $this,
                        'align_dir' => $align_dir,
                        'width_size' => $width_size,
                    ]); //function To Render Multiple Image
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

        $value = empty($value) ? $this->article_body : $value;
        $re = "/(\*\*related_articles\[(.*?)\]\*\*)/";
        $matches = array();
        preg_match_all($re, $value, $matches);
        if (isset($matches[0])) {
            foreach ($matches[0] as $matche) {
                $strToSearch = $matche;

                $npWidgetId = str_replace("**related_articles[", "", $matche);
                $npWidgetId = str_replace("]**", "", $npWidgetId);
                if ($npWidgetId) {
                    $article_related_ids = trim($npWidgetId);
                    $relatedArticlesIds = explode(',', $article_related_ids);

                    foreach ($relatedArticlesIds as $key => $related_id) {
                        if (strpos($related_id, "-")) {
                            $numeric_id = explode('-', $related_id);
                            $numeric_id = $numeric_id[0];
                            $relatedArticlesIds[$key] = $numeric_id;
                        }
                    }
                    $relatedArticles = article::whereIn('np_article_id', $relatedArticlesIds)->get();
                    $strToReplace = View('theme::components.article_related_body', ['relatedArticles' => $relatedArticles])->render(); //function To Render Multiple Image
                }
                $value = str_replace($strToSearch, $strToReplace, $value);
            }
        }

        $value = str_replace('******** async="" charset="utf-8" src="https://platform.twitter.com/widgets.js">********', '', $value);
        $value = str_replace('******** async=&quot;&quot; charset=&quot;utf-8&quot; src=&quot;https://platform.twitter.com/widgets.js&quot;>********', '', $value);
        $value = str_replace('******** async="" defer="defer" src="//platform.instagram.com/en_US/embeds.js">********', '', $value);
        $value = str_replace('******** async=&quot;&quot; defer=&quot;defer&quot; src=&quot;//platform.instagram.com/en_US/embeds.js&quot;>********', '', $value);
        //this code commented for daily sabah to be checked later on 18022020
        //$value =preg_replace("/<br><br>/m", "<br>", $value);//one <br> in cms added 2<br>in db this code to convert it to one <br>
        if (!empty($textInsideParagraph)) {
            if (class_exists($theme_controller_class)) {
                $themeController = new $theme_controller_class();
                if (method_exists($themeController, 'textInsideParagraph')) {
                    $function_name = 'textInsideParagraph';
                    return $themeController->$function_name($value, $textInsideParagraph, $articles_array,$this);
                } else {
                    return $this->text_inside_paragraph($value, $textInsideParagraph['paragraph'], $textInsideParagraph['view_name'], false, $articles_array);
                }
            }
        }

        return $value;
    }

    public function getSeoMetaKeywordsAttribute($value) {
        //this is laravel logic of modifying an existing table field value
        if (empty($value)) {
            $remove = ['Â«', '&raquo;', '&laquo;', 'Â»', ' Ø¨ ', ' Ø§ ', ' Ø£ ', ' ØŒ ', ' Ø¹Ù† ', ' Ø¹Ù†Ø¯ ', ' Ø¹Ù†Ø¯Ù…Ø§ ', ' Ø¹Ù„Ù‰ ', ' Ø¹Ù„ÙŠÙ‡ ', ' Ø¹Ù„ÙŠÙ‡Ø§ ', ' ØªÙ… ', ' Ø¶Ø¯ ', ' Ø¨Ø¹Ø¯ ', ' Ø¨Ø¹Ø¶ ', ' Ø­ØªÙ‰ ', ' Ø§Ø°Ø§ ', ' Ø§Ø­Ø¯ ', ' Ø¨Ø§Ù† ', ' Ø§Ø¬Ù„ ', ' ØºÙŠØ± ', ' Ø¨Ù† ', ' Ø¨Ù‡ ', ' Ø«Ù… ', ' Ø§ï¿½? ', ' Ø§Ù† ', ' Ø§Ùˆ ', ' Ø§ÙŠ ', ' Ø¨Ù‡Ø§ ', ' Ø­ÙŠØ« ', ' Ø§Ø§Ù„Ø§ ', ' Ø§Ù…Ø§ ', ' Ø§Ø§Ù„ØªÙ‰ ', ' Ø§Ù„ØªÙŠ ', ' Ø§ÙƒØ«Ø± ', ' Ø§ÙŠØ¶Ø§ ', ' Ø§Ù„Ø°Ù‰ ', ' Ø§Ù„Ø°ÙŠ ', ' Ø§Ù„Ø§Ù† ', ' Ø§Ù„Ø°ÙŠÙ† ', ' Ø§Ø¨ÙŠÙ† ', ' Ø°Ù„Ùƒ ', ' Ø¯ÙˆÙ† ', ' Ø­ÙˆÙ„ ', ' Ø­ÙŠÙ† ', ' Ø§Ù„Ù‰ ', ' Ø§Ù†Ù‡ ', ' Ø§ÙˆÙ„ ', ' Ø§Ù†Ù‡Ø§ ', ' ï¿½? ', ' Ùˆ ', ' Ùˆ6 ', ' Ù‚Ø¯ ', ' Ù„Ø§ ', ' Ù…Ø§ ', ' Ù…Ø¹ ', ' Ù‡Ø°Ø§ ', ' ÙˆØ§Ø­Ø¯ ', ' ÙˆØ§Ø¶Ø§ï¿½? ', ' ÙˆØ§Ø¶Ø§ï¿½?Øª ', ' ï¿½?Ø§Ù† ', ' Ù‚Ø¨Ù„ ', ' Ù‚Ø§Ù„ ', ' ÙƒØ§Ù† ', ' Ù„Ø¯Ù‰ ', ' Ù†Ø­Ùˆ ', ' Ù‡Ø°Ù‡ ', ' ÙˆØ§Ù† ', ' ÙˆØ§ÙƒØ¯ ', ' ÙƒØ§Ù†Øª ', ' ÙˆØ§ÙˆØ¶Ø­ ', ' ï¿½?Ù‰ ', ' ï¿½?ÙŠ ', ' ÙƒÙ„ ', ' Ù„Ù… ', ' Ù„Ù† ', ' Ù„Ù‡ ', ' Ù…Ù† ', ' Ù‡Ùˆ ', ' Ù‡ÙŠ ', ' Ù‚ÙˆØ© ', ' ÙƒÙ…Ø§ ', ' Ù„Ù‡Ø§ ', ' Ù…Ù†Ø° ', ' ÙˆÙ‚Ø¯ ', ' ÙˆÙ„Ø§ ', ' Ù„Ù‚Ø§Ø¡ ', ' Ù…Ù‚Ø§Ø¨Ù„ ', ' Ù‡Ù†Ø§Ùƒ ', ' ÙˆÙ‚Ø§Ù„ ', ' ÙˆÙƒØ§Ù† ', ' ÙˆÙ‚Ø§Ù„Øª ', ' ÙˆÙƒØ§Ù†Øª ', ' ï¿½?ÙŠÙ‡ ', ' Ù„ÙƒÙ† ', ' Ùˆï¿½?ÙŠ ', ' ÙˆÙ„Ù… ', ' ÙˆÙ…Ù† ', ' ÙˆÙ‡Ùˆ ', ' ÙˆÙ‡ÙŠ ', ' ÙŠÙˆÙ… ', ' ï¿½?ÙŠÙ‡Ø§ ', ' Ù…Ù†Ù‡Ø§ ', ' ÙŠÙƒÙˆÙ† ', ' ÙŠÙ…ÙƒÙ† '];
            $keywords = $this->article_title;
            foreach ($remove as $word) {
                if (strpos($keywords, $word)) {
                    $keywords = str_replace($word, ' ', $keywords);
                }
            }
            $keywords = str_replace('  ', ' ', trim($keywords));
            $value = str_replace(' ', ',', trim($keywords));

            $to_replace = array('&#039;','"');
            $replace_by  = array("'", "'");

            $value = str_replace($to_replace, $replace_by,$value);
        }

        return $value;
    }

    public function getSeoMetaDescriptionAttribute($value) {
        //this is laravel logic of modifying an existing table field value        
        return !empty($value) && !ctype_space($value) ? $value : (ThemeService::ConfigValue('DEFAULT_SEO_META_DESC') ? $this->attributes['seo_meta_description'] : $this->article_title);
    }

    /**
     * sets the article->simple_url
     */
    public function getSimpleUrlAttribute() {
        return ThemeService::ConfigValue('APP_URL') . 'article/' . $this->cms_article_id;
    }

    /**
     * sets the article->amp_url
     */
    public function getAmpUrlAttribute() {
        return ThemeService::ConfigValue('APP_URL') . 'ampArticle/' . $this->cms_article_id;
    }

    /**
     * sets the article->seo_url
     */
    public function getSeoUrlAttribute() {
        if (ThemeService::ConfigValue('DO_NOT_USE_ARTICLE_SEO_URL')) {
            return $this->getSimpleUrlAttribute();
        }
        $external_link = json_decode($this->article_custom_fields);
        if (isset($external_link->external_link) && strlen(trim($external_link->external_link[0])) > 0) {
            return $external_link->external_link[0] . '" target="_blank';
        }
        if (isset($this->permalink) && (is_numeric(strpos($this->permalink, "http://")) || is_numeric(strpos($this->permalink, "https://")))) {
            return $this->permalink;
        }
        if ($this->is_old_article) {
            if(ThemeService::ConfigValue('USE_ARCHIVE_PERMALINK')){
                return UrlHelper::main_url() . $this->permalink;
            }elseif (ThemeService::ConfigValue('PERMALINK_AS_OLD_ID')) {
                return UrlHelper::main_url().$this->old_article_id;
            }else{
            return UrlHelper::build_seo_url($this->cms_article_id, 'article_archive', $this->seo_meta_title, $this->section_name, $this->sub_section_name);
            }
        } else {
            return UrlHelper::build_seo_url($this->cms_article_id, 'article', $this->seo_meta_title, $this->section_id, $this->sub_section_id, '', $this);
        }
    }

    /**
     * sets the article->encode_url
     */
    public function getEncodeUrlAttribute() {
        $url_parse = parse_url($this->seo_url);
        $entities = array('%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%2B', '%24', '%2C', '%2F', '%3F', '%25', '%23', '%5B', '%5D');
        $replacements = array('!', '*', "'", "(", ")", ";", ":", "@", "&", "=", "+", "$", ",", "/", "?", "%", "#", "[", "]");
        $url_cleaned = str_replace($entities, $replacements, rawurlencode($url_parse['path']));
        return $url_parse['scheme'] . '://' . $url_parse['host'] . str_replace('_', '%81', $url_cleaned);
    }

    /**
     * sets the article->author_info
     */
    public function getAuthorInfoAttribute() {
        if ($this->author_id) {
            return author::find_np($this->author_id);
        } else {
            return array();
        }
    }

    /**
     * sets the article->author_url
     */
    public function getAuthorUrlAttribute() {
        return UrlHelper::build_seo_url($this->author_id, 'author', $this->author_name);
    }

    /**
     * sets the article->author_image_src
     */
    public function getAuthorImageSrcAttribute() {
        if ($this->author_id <> 0) {
            $author = author::find_np($this->author_id);
        } else {
            return '';
        }
        if ($author && !empty($author->author_image)) {
            $img = (strpos($author->author_image, 'http') !== FALSE) ? $author->author_image : ThemeService::ConfigValue('CDN_URL') . $author->author_image;
			if(isset($author->last_modified)){
				$img .= "?ts=".strtotime($author->last_modified);
			}
			return $img;
        }

        return '';
    }

    /**
     */
    public function article_tags_view($view = '', $flag = false) {
        $article_tags = explode(',', str_replace(' ', '-', $this->article_tags));
        $get_count_images = ThemeService::ConfigValue('ARTICLE_TAGS_COUNT_IMAGE');
        $count_images = $get_count_images ? $this->image()->count() : false;

        if ($view) {
            return View('theme::components.' . $view, array(
                'article_tags' => $article_tags,
                'flag' => $flag
            ));
        } else {

            return View('theme::components.article_tags', array(
                'article' => $this,
                'count_images' => $count_images,
                'article_tags' => $article_tags,
                'flag' => $flag
            ));
        }
    }

    /**
     */
    public function article_tags_view_amp($view = '', $flag = false) {
        $article_tags = explode(',', str_replace(' ', '-', $this->article_tags));
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

    //display the article tags; if there is multi subsections then display the first subsection, otherwise display the section name.
    public function article_tags_section_subsection_view($view = '', $flag = 0, $is_article_bottom = false) {
        if ($this->is_old_article == 1) {
            $article_tags[] = array('section' => section::find_np($this->section_id));
        } else {
            //display all tags at the bottom of the article
            if ($is_article_bottom) {
                $ams_article = article_multi_section::where('ams_article_id', $this->cms_article_id)->get();
                $article_tags = [];
                if (isset($ams_article)) {
                    if (count($ams_article) > 0) {
                        foreach ($ams_article as $amsId) {
                            if (isset($amsId->ams_subsection_id) && $amsId->ams_subsection_id > 0) {
                                $article_tags[] = array('sub_section' => sub_section::find_np($amsId->ams_subsection_id));
                            } else if (isset($amsId->ams_section_id) && $amsId->ams_section_id > 0) {
                                $article_tags[] = array('section' => section::find_np($amsId->ams_section_id));
                            }
                        }
                    }
                }
            } else {
                $article_tags = [];
                $ams_article = $this;
                if (isset($ams_article->sub_section_id) && $ams_article->sub_section_id > 0) {
                    $article_tags[] = array('sub_section' => sub_section::find_np($ams_article->sub_section_id));
                } else if (isset($ams_article->section_id) && $ams_article->section_id > 0) {
                    $article_tags[] = array('section' => section::find_np($ams_article->section_id));
                }
            }
        }
        if ($view) {
            return View('theme::components.' . $view, array(
                'article_tags' => $article_tags,
                'flag' => $flag,
                'is_article_bottom' => $is_article_bottom
            ));
        } else {
            return View('theme::components.article_tags', array(
                'article_tags' => $article_tags,
                'flag' => $flag,
                'is_article_bottom' => $is_article_bottom
            ));
        }
    }

    /**
     * sets the article->author_image_src ()
     */
    public function author_image_src($html = false, $view = 'article', $enable_default_image_author = false) {

        if ($this->author_id > 0) {
            $author = author::find_np($this->author_id);
        } else {
            return '';
        }

        if ($html) {
            if (($author && !empty($author->author_image)) || ($enable_default_image_author && $author)) {
                return View('theme::components.author_image_src', array(
                    'author' => $author,
                    'article' => $this,
                    'view' => $view
                ));
            }
        } else {
            if ($author && !empty($author->author_image)) {
                return ThemeService::ConfigValue('CDN_URL') . $author->author_image;
            }
        }

        return '';
    }

    /**
     * sets the article->section_url
     */
    public function getSectionUrlAttribute() {
        if ($this->is_old_article && isset($this->np_section_id) && $this->np_section_id > 0) {
            return UrlHelper::build_seo_url(1, 'section', '', $this->np_section_id);
        } else {
            return UrlHelper::build_seo_url(1, 'section', '', $this->section_id);
        }
    }

    /**
     * sets the article->sub_section_url
     */
    public function getSubSectionUrlAttribute() {
        if ($this->is_old_article && isset($this->np_sub_section_id) && $this->np_sub_section_id > 0) {
            return UrlHelper::build_seo_url($this->np_sub_section_id, 'sub_section');
        } else {
            return UrlHelper::build_seo_url($this->sub_section_id, 'sub_section');
        }
    }

    /**
     * sets the article->page_url_by_section()
     */
    public function page_url_by_section($get_sub_section = true) {

        $url = '';
        if ($this->sub_section_id && $get_sub_section) {
            if (!$this->is_old_article) {
                $url = UrlHelper::build_seo_url($this->sub_section_id, 'sub_section');
            }
            if (empty($url)) {
                $url = '/' . UrlHelper::clean_url($this->section_name) . '/' . UrlHelper::clean_url($this->sub_section_name);
            }
        } else {
            if (!$this->is_old_article) {
                $url = UrlHelper::build_seo_url(1, 'section', '', $this->section_id);
            }
            if (empty($url)) {
                if(ThemeService::ConfigValue('LANGUAGE')=='ar'){
                    $section_name = $this->section_name;
                }else{
                    $section_name = strtolower($this->section_name);
                }
                $url = '/' . str_replace(' ', '-', $section_name);
            }
        }
        return $url;
    } 
    /**
     * sets the article->short_title
     */
    public function getShortTitleAttribute() {
        $short_title = $this->article_title;
        if (ThemeService::ConfigValue('SHORT_TITLE_LOGIC')) {
            $custom_fields = $this->getCustomFieldsAttribute();
            if (!empty($custom_fields->short_title) && !empty($custom_fields->short_title[0])) {
                $short_title = $custom_fields->short_title[0];
            }
        }

        return $short_title;
    }

    /**
     * sets the article->redirect_url
     */
    public function getRedirectUrlAttribute() {
		$custom_fields = $this->getCustomFieldsAttribute();
		if (!empty($custom_fields->redirect_url) && !empty($custom_fields->redirect_url[0])) {
			$redirect_url = $custom_fields->redirect_url[0];
			return $redirect_url ;
		}
		return "";
    }

    /**
     * sets the article->homepage_title
     */
    public function getHomepageTitleAttribute() {
        $article_custom_fields = json_decode($this->article_custom_fields);
        return isset($article_custom_fields->homepage_title[0]) ? $article_custom_fields->homepage_title[0] : 0;
    }

    /**
     * sets the article->last_modified_user
     */
    public function getLastModifiedUserAttribute() {
        $article_custom_fields = json_decode($this->article_custom_fields);
        return isset($article_custom_fields->last_modified_user) ? $article_custom_fields->last_modified_user : '';
    }

    /**
     * sets the article->article_shortlink_api()
     */
    public function article_shortlink_api($cms_article_id, $article_url) {
        if (ThemeService::ConfigValue('ENABLE_SHORTLINK_URL')) {
            if ($this->article_shortlink) {
                return $this->article_shortlink;
            } else {
                return $this->permalink;
            //    $theme_controller_class = 'Themes\\'.ThemeService::Name().'\\controllers\\'.ThemeService::ThemeController();
            //    if(class_exists($theme_controller_class)){
            //        $themeController = new $theme_controller_class();
            //        if(method_exists($themeController, 'getShortLinkAction')) {
            //            return $themeController->getShortlinkAction($article_url,$cms_article_id);
            //        }
            //    }else{
            //        return false;
            //    }
            }
        }
    }
    
    /**
    * sets the article->article_shortlink_bitly_api()
    */
    public function article_shortlink_bitly_api($cms_article_id, $article_url) {
        if (ThemeService::ConfigValue('ENABLE_SHORTLINK_URL')) {
            if ($this->article_shortlink) { 
                return $this->article_shortlink;
            } else {
                $theme_controller_class = 'Themes\\' . ThemeService::Name() . '\\controllers\\' . ThemeService::ThemeController();
                if (class_exists($theme_controller_class)) {
                    $themeController = new $theme_controller_class();
                    if (method_exists($themeController, 'getShortLinkAction')) {
                        return $themeController->getShortlinkAction($article_url, $cms_article_id);
                    }
                } else {
                    return false;
                }
            }
        }
    }

    /**
     * sets the article->article_shortlink_brandly_api()
     */
    public function article_shortlink_brandly_api($cms_article_id, $article_url) {
        if (ThemeService::ConfigValue('ENABLE_SHORTLINK_URL')) {
            if ($this->article_shortlink) {
                return $this->article_shortlink;
            } else {
                $theme_controller_class = 'Themes\\' . ThemeService::Name() . '\\controllers\\' . ThemeService::ThemeController();
                if (class_exists($theme_controller_class)) {
                    $themeController = new $theme_controller_class();
                    if (method_exists($themeController, 'getShortLinkAction')) {
                        return $themeController->getShortlinkAction($article_url, $cms_article_id);
                    }
                } else {
                    return false;
                }
            }
        }
    }

    /**
     * sets the article->multi_section_id
     */
    public function getMultiSectionIdAttribute() {
        $multi_secction_ids = array();
        foreach ($this->article_multi_section()->get() as $multi_section_array) {
            if (isset($multi_section_array->ams_section_id)) {
                $multi_secction_ids[] = $multi_section_array->ams_section_id;
            }
        }
        return $multi_secction_ids;
    }


    /**
     * sets the article->custom_fields
     */
    public function getCustomFieldsAttribute() {
        return json_decode($this->article_custom_fields);
    }

    /**
     * sets the article->article_tags_array
     */
    public function getArticleTagsArrayAttribute() {
        return explode(',', $this->article_tags);
    }

    /**
     * gets the article->source
     */
    public function getSourceAttribute() {
        // return $this->custom_fields->source;
        if (isset($this->custom_fields->source)) {
            $source = $this->custom_fields->source[0];
            if ($source == "" || $source == "none") {
                return null;
            }
            return $source;
        }
        return null;
    }

    /**
     * gets the article->publish_date from custom fields
     */
    public function getPublishDateAttribute() {
        $publish_date = $this->publish_time;
        if (isset($this->custom_fields->Publish_Date) && !empty($this->custom_fields->Publish_Date)) {
            if(!empty($this->custom_fields->Publish_Date[0])){
                $publish_date = $this->custom_fields->Publish_Date[0];
            }
        }
        return $publish_date;
    }

    /**
     * sets the article->image_alt_text
     */
    public function getImageAltTextAttribute() {
        $first_image = $this->image->first();

        return $first_image ? $first_image->image_alt_text : '';
    }

    /**
     * sets the article->image_info
     */
    public function getimageInfoAttribute() {
        return json_decode(stripslashes($this->image_path));
    }

    /**
     * sets the article->image_info
     */
    public function image_info_cdn($thumb = 800) {
        $image_obj = json_decode(stripslashes($this->image_path));
        $image_src = ThemeService::ConfigValue("CDN_URL") . $image_obj->image_path;
        $init_src = explode("/", $image_src);
        $path = "w" . $thumb . "/" . end($init_src);
        $key = key($init_src);
        $init_src[$key] = $path;
        $init_src = 'https://cdn.premiumread.com/web30/storage/' . str_replace(['https://www.', 'http://www.', 'https://', 'http://'], ['', '', '', ''], implode("/", $init_src)) . '.jpg';
        return $init_src;
    }

    /**

     * sets the article->images_count
     */
    public function getImagesCountAttribute() {
        $image = $this->image->count();

        return $image;
    }

    /**
     * sets the article->image_src
     */
    public function image_src($html = true, $thumb = false, $embed = false, $is_focal_point = true, $type = 'article', $isDefaultImage = true, $view_image_caption = false) {
        $enable_image_cropping = ThemeService::ConfigValue('ENABLE_IMAGE_CROPPING');
        if ($this->is_old_article) {
            if(!ThemeService::ConfigValue('ENABLE_ARCHIVE_IMAGES_THUMBS')){
                $thumb = false;
            }
            $enable_image_cropping = false;
        }

        if (isset($this->image_path) && strlen($this->image_path) > 2) {

            $theme_controller_class = 'Themes\\' . ThemeService::Name() . '\\controllers\\' . CommonController::controller_class_name();

            if(ThemeService::ConfigValue('IMAGE_THUMBNAIL_LOGIC')){
                if (class_exists($theme_controller_class)) {
                    $themeController = new $theme_controller_class();
                    if (method_exists($themeController, 'getThumbnailImage')) {
                        $function_name = 'getThumbnailImage';
                        $imgObj = $themeController->$function_name($this);
                    }else{
                        $imgObj = json_decode(stripslashes($this->image_path));
                    }
                }else{
                    $imgObj = json_decode(stripslashes($this->image_path));
                }
            }else {
                $imgObj = json_decode(stripslashes($this->image_path));
            }
            if (!$imgObj) {
                $imgObj = json_decode($this->image_path);
            }
            if ($imgObj) {
                $imgObj->article = $this;
                if (isset($this->article_shortlink) && !empty($this->article_shortlink)) {
                    $imgObj->article_shortlink = $this->article_shortlink;
                }
            }

            if (isset($imgObj->media_type)) {
                //NP_DEFAULT_IMAGE_SIZES this config to check if image hass croping or no
                //if no we set $thumbs = 0 ;
                if (ThemeService::ConfigValue('NP_DEFAULT_IMAGE_SIZES') && $this->is_old_article == 0) {
                    $flag = 1;
                    $cropping_data = json_decode($imgObj->image_cropping, true);
                    foreach ($cropping_data as $key => $cropping) {
                        if ($key == $thumb && in_array($key, ThemeService::ConfigValue('NP_DEFAULT_IMAGE_SIZES'))) {
                            $flag = 0;
                        }
                    }
                    if ($flag) {
                        $thumb = false;
                    }
                }
                $imgObj->time_str = strtotime($this->last_edited);
                $article = PageService::Article();
                return ImageHelper::getImageSrc($imgObj, $html, $thumb, $embed, $is_focal_point, $type, $enable_image_cropping, $view_image_caption, $isDefaultImage,$article);
            } elseif (isset($imgObj->image_path) && strpos($imgObj->image_path, "/images/")) {
                /* code below for articles old contain image path but missing media_type,image_caption,image_alt_text,image_count values */
                $imgObj->media_type = 0;
                $imgObj->image_caption = '';
                $imgObj->image_alt_text = '';
                $imgObj->image_count = '1';
                $imgObj->time_str = strtotime($this->last_edited);
                return ImageHelper::getImageSrc($imgObj, $html, $thumb, $embed, $is_focal_point, $type, $enable_image_cropping, $view_image_caption, $isDefaultImage);
            } elseif ($html && $type == 'amp-article') {
                return View('theme::components.amp_no_image_src');
            } elseif ($html) {
                return View('theme::components.no_image_src');
            } else {
                return '';
            }
        } else {

            if ($isDefaultImage && $html) {
                return View('theme::components.no_image_src', [
                    'thumb' => $thumb]
                );
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

    /**
     * sets the article->content_keywords
     */
    public function getContentKeywordsAttribute() {
        $article_custom_fields = json_decode($this->article_custom_fields);
        $content_keywords = trim($article_custom_fields->content_keywords[0]);
        if (strlen($content_keywords) > 0) {
            $content_keywords = explode(",", $content_keywords);
            return $content_keywords;
        } else {
            return [];
        }
    }

    /**
     * sets the article->related_images // excluding the body embedded media
     */
    public function getRelatedImagesAttribute() {
        $related_images = [];
        $pattern = "/(\*\*media\[(\d|,)*]\*\*)/";
        $matches = array();
        preg_match_all($pattern, $this->article_body, $matches);
        $matches = $matches[0];

        $body_image_ids = [];
        for ($k = 0; $k < count($matches); $k++) {
            $body_image_id = str_replace("**media[", "", $matches[$k]);
            $body_image_id = str_replace("]**", "", $body_image_id);

            if ($body_image_id) {
                $body_image_ids[$body_image_id] = true;
            }
        }

        $images = image::where('np_related_article_id', $this->np_article_id)->where('is_old_image', $this->is_old_article)->where('image_is_deleted', 0)->get();
        if ($images) {
            foreach ($images as $image) {
                if (!isset($body_image_ids[$image->np_image_id])) {
                    $related_images[] = $image;
                }
            }
        }

        return collect($related_images);
    }

    public static function updatePermalink($cmsArticleId, $permalink) {
        $article = article::where('cms_article_id', $cmsArticleId)->update(array('permalink' => $permalink));
        return 0;
    }

    public static function updateArticleShortlink($cmsArticleId, $articleShortlink) {
        $article = article::where('cms_article_id', $cmsArticleId)->update(array('article_shortlink' => $articleShortlink));
        return 0;
    }

    public function getIsAuthorSectionAttribute() {
        if ($this->author_id > 0) {
            $authorArticleSubSectionId = ThemeService::ConfigValue('AUTHOR_ARTICLE_SUBSECTION_ID') ? explode(",", ThemeService::ConfigValue('AUTHOR_ARTICLE_SUBSECTION_ID')) : 0;

            $authorArticleSectionId = ThemeService::ConfigValue('AUTHOR_ARTICLE_SECTION_ID') ? explode(",", ThemeService::ConfigValue('AUTHOR_ARTICLE_SECTION_ID')) : 0;

            if (in_array($this->section_id, $authorArticleSectionId) && in_array($this->sub_section_id, $authorArticleSubSectionId)) {
                return true;
            }
        }

        return false;
    }

    public static function text_inside_paragraph($article_body = '', $paragraph = 2, $view_name = '', $get_found_paragraph = false, $articles_array = array(), $static_data = array(), $static_data_mobile = array(), $print_on_not_found = true) {
        if (empty($article_body)) {
            return false;
        }
        if (!$get_found_paragraph) {
            $view_name = WidgetService::widget_view_name($view_name);
            $view_style = 'theme::widgets.' . $view_name;
            $html = View(
                    $view_style, array(
                'articles' => $articles_array,
                'static_data' => $static_data,
                'static_data_mobile' => $static_data_mobile,
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
                if ($print_on_not_found) {
                    $not_found_paragraph = 1;
                    $content = $article_body . $html;
                } else {
                    $content = $article_body;
                }
                break;
        }
        if ($not_found_paragraph && $get_found_paragraph) {
            return false;
        }
        return $content;
    }

    public function text_inside_paragraph_amp($article_body = '', $paragraph = 2, $get_found_paragraph = false, $relatedArticles = array()) {

        if (empty($article_body)) {
            return false;
        }
        if (!$get_found_paragraph) {
            $view_name = WidgetService::widget_view_name('related_article_inside_body_amp');
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
                $content.=$html;
                break;
        }
        if ($not_found_paragraph && $get_found_paragraph) {
            return false;
        }
        return $content;
    }

    public function seo_url_by_section($menu_id = 0, $followSubsection = 0) {
        $menu_id = $menu_id == 0 ? ThemeService::ConfigValue('WEB_MENU_ID') : $menu_id;
        /* $followSubsection to follow section if subsection exist but not page created for it and not link assign to it in menu */
        if ($followSubsection) {
            $article_menu_info = menu_item::find_menu_by_section_id($this->section_id, $this->sub_section_id, $menu_id);
        } else {
            $article_menu_info = menu_item::find_menu_by_section_id($this->section_id, 0, $menu_id);
        }

        if (!empty($article_menu_info) && $article_menu_info['page_id']) {
            return UrlHelper::build_seo_url($article_menu_info['page_id'], 'page');
        } else {
            return '';
        }
    }

    public function articles_by_breaking_news($np_id) {

        return self::where('np_article_id', $np_id)->where('breaking_news', 1)->first();
    }

    public static function breaking_news($limit, $time) {
        return self::where('breaking_news', 1)->where("publish_time", '>', DB::raw($time))->orderBy("publish_time", "desc")->limit($limit)->get();
    }

    public function getSectionColorAttribute(){
        if(!empty($this->section_id)){
            $section = section::find_np($this->section_id);
            if(!empty($section)){
                $section_color =  $section->section_color;
            }
        }
        return !empty($section_color) ? $section_color : '';
    }
    
    public static function get_sub_articles($np_id) {  
        $all_sub_articles=self::where('article_parent_id', $np_id)->where("publish_time", '<', DB::raw('now()'))->orderBy("publish_time", "asc")->get();
        return !empty($all_sub_articles) ? $all_sub_articles : '';
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
    
    public function getArticleTitleAttribute() { 
        $theme_controller_class = 'Themes\\' . ThemeService::Name() . '\\controllers\\' . CommonController::controller_class_name();
        if (class_exists($theme_controller_class)) {
            $themeController = new $theme_controller_class();
            if (method_exists($themeController, 'workWithArticleTitle')) {
                $function_name = 'workWithArticleTitle';
                return $themeController->$function_name($this->attributes['article_title']);
            }else{
                return $this->attributes['article_title'];
            }
        }
    }

}
