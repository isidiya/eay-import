<?php

namespace App\Http\Controllers;

use App\Models\article;
use App\Models\article_archive;
use App\Models\section;
use App\Models\image;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Layout\Website\Services\ThemeService;
use App\Mail\SendEmail;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\CommonController;
use Themes\fratmat\controllers\FratmatController;
use Illuminate\Support\Facades\DB;
use App\Models\article_multi_section;
use App\Models\page;
use App\Models\bootstrap_rows;
use Illuminate\Support\Facades\View;
use Layout\Website\Services\WidgetService;
use Layout\Website\Services\PageService;

class AjaxController extends Controller {

    public function __construct() {
        
    }

    public static function getIP() {
        // Reference: https://www.chriswiegman.com/2014/05/getting-correct-ip-address-php/
        //Just get the headers if we can or else use the SERVER global.
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
        } else {
            $headers = $_SERVER;
        }

        //Get the forwarded IP if it exists.
        if (array_key_exists('X-Forwarded-For', $headers) && filter_var($headers['X-Forwarded-For'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $the_ip = $headers['X-Forwarded-For'];
        } elseif (array_key_exists('HTTP_X_FORWARDED_FOR', $headers) && filter_var($headers['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $the_ip = $headers['HTTP_X_FORWARDED_FOR'];
        } else {
            $the_ip = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
        }

        return $the_ip;
    }

    public function getAllAuthorArticles() {
        $np_author_id = Request()->input("npAuthorId");
        if ($np_author_id > 0) {
            $articles = article::where('author_id', $np_author_id)->paginate(6, ['*'], 'pgno');
        } else {
            $articles = article::where('sub_section_id', ThemeService::ConfigValue('AUTHOR_ARTICLE_SUBSECTION_ID'))->paginate(6, ['*'], 'pgno');
        }
        $withPath = Request()->input("withPath");
        $articles->withPath($withPath);
        return view('theme::ajax.get_all_author_articles', ['articles' => $articles, "np_author_id" => $np_author_id]);
    }

    public function loadMoreTodayInpic() {
        $sectionId = Request()->input("sectionId");
        $limit = Request()->input("limit");
        $offset = Request()->input("offset");
        $articlesPhotos = article::where('section_id', $sectionId)->where('media_gallery_flag', '0')->orderBy('publish_time', 'desc')->offset($offset)->limit($limit)->get();

        $html = view('theme::ajax.load_more_today_inpic', ['articlesPhotos' => $articlesPhotos]);
        return $html;
    }

    public function loadMoreAlbums() {
        $sectionId = Request()->input("sectionId");
        $limit = Request()->input("limit");
        $offset = Request()->input("offset");
        $articlesAlbums = article::where('section_id', $sectionId)->where('media_gallery_flag', '1')->orderBy('publish_time', 'desc')->offset($offset)->limit($limit)->get();

        $html = view('theme::ajax.load_more_albums', ['articlesAlbums' => $articlesAlbums]);
        return $html;
    }

    public function loadTodayPicture() {
        $sectionId = Request()->input("sectionId");
        $event = Request()->input("event");
        $publish = Request()->input("publish");

        if ($event == 'next') {
            $article = article::where('section_id', $sectionId)
                            ->where('media_gallery_flag', '0')
                            ->where('publish_time', '<', date($publish))
                            ->orderBy('publish_time', 'desc')->first();
            if (empty($article)) {
                $article = article::where('section_id', $sectionId)
                                ->where('media_gallery_flag', '0')
                                ->where('publish_time', '>', date($publish))
                                ->orderBy('publish_time', 'desc')->first();
            }
        } else {
            $article = article::where('section_id', $sectionId)
                            ->where('media_gallery_flag', '0')
                            ->where('publish_time', '>', date($publish))
                            ->orderBy('publish_time', 'asc')->first();
            if (empty($article)) {
                $article = article::where('section_id', $sectionId)
                                ->where('media_gallery_flag', '0')
                                ->where('publish_time', '<', date($publish))
                                ->orderBy('publish_time', 'asc')->first();
            }
        }

        $html = view('theme::ajax.load_today_picture', ['article' => $article,
            'link' => ThemeService::ConfigValue("APP_URL") . $article->permalink,
            'pageTitle' => $article->article_title . " - Photo | " . ThemeService::ConfigValue("NEWSPAPER_PAGE_TITLE"),
            'publish' => $article->publish_time
                ])->render();
        $data_array = array(
            "html" => $html,
            "link" => ThemeService::ConfigValue("APP_URL") . $article->permalink,
            "pageTitle" => $article->article_title . " - Photo | " . ThemeService::ConfigValue("NEWSPAPER_PAGE_TITLE"),
            "newpublish" => $article->publish_time,
            "eventButton" => $event
        );

        return $data_array;
    }

    public function loadMoreAlbumPic() {
        $articleNpId = Request()->input("articleNpId");
        $limit = Request()->input("limit");
        $offset = Request()->input("offset");

        $article = article::find_np($articleNpId);

        $allImages = image::where('np_related_article_id', $articleNpId)->orderBy('media_order', 'asc')->offset($offset)->limit($limit)->get();
        $html = view('theme::ajax.load_more_album_pic', ['allImages' => $allImages, 'article' => $article]);
        return $html;
    }

    public function contactUsAction() {

        $captcha = Request()->input("captcha_response");

        if (!CommonController::verifyRecaptcha($captcha)) {
            $return_array = array(
                'is_error' => '1',
                'message' => 'Invalid re-captcha. Please try again.',
                'message_ar' => 'رمزالمرور غير صالح. حاول مرة اخرى.'
            );

            return $return_array;
        }

        $objMail = new \stdClass();

        $objMail->view = 'theme::mails.contact-us';
        $objMail->subject = Request()->input("txtSubject");
        $objMail->senderUserName = Request()->input("txtName");
        $objMail->userEmail = Request()->input("txtEmail");
        $objMail->reason = Request()->input("txtReason");
        $objMail->message = Request()->input("txtMessage");
        $objMail->phone = Request()->input("txtPhone");
        $objMail->country = Request()->input("txtCountry");

        $objMail->senderEmail = ThemeService::ConfigValue('EMAIL_FROM');

        $objMail->senderIP = self::getIP();
        $objMail->currentDay = date("d/m/Y");

        try {
            Mail::to(ThemeService::ConfigValue('CONTACT_US_EMAIL'))->send(new SendEmail($objMail));
            $return_array = array(
                'is_error' => 0,
                'message' => 'Thank you for your enquiry. Your email has been redirected, and will be processed accordingly.',
                'message_ar' => 'شكرا لاستفسارك. تمت إعادة توجيه بريدك الإلكتروني ، وسيتم معالجته وفقًا لذلك.'
            );
        } catch (\Exception $e) {
            $return_array = array(
                'is_error' => 1,
                'message' => "Email not sent. Please try again. " . $e->getMessage(),
                'message_ar' => "البريد لم يرسل. حاول مرة اخرى. " . $e->getMessage()
            );
        }
        return $return_array;
    }

    public function sendCvAction() {

        $captcha = Request()->input("captcha_response");

        if (!CommonController::verifyRecaptcha($captcha)) {
            $return_array = array(
                'is_error' => '1',
                'message' => 'Invalid re-captcha. Please try again.',
                'message_ar' => 'رمزالمرور غير صالح. حاول مرة اخرى.'
            );

            return $return_array;
        }


        $objMail = new \stdClass();

        $objMail->view = 'theme::mails.career';
        $objMail->subject = Request()->input("txtSubject");
        $objMail->senderUserName = Request()->input("txtName");
        $objMail->senderEmail = Request()->input("txtEmail");
        $objMail->attach = Request()->file("cv");

        $objMail->senderIP = self::getIP();
        $objMail->currentDay = date("d/m/Y");

        try {
            Mail::to(ThemeService::ConfigValue('CAREER_EMAIL'))->send(new SendEmail($objMail));
            $return_array = array(
                'is_error' => 0,
                'message' => 'Thanks for sending your CV. Your email has been redirected, and will be processed accordingly.',
                'message_ar' => 'شكرا على ارسال سيرتك الذاتية. تمت إعادة توجيه بريدك الإلكتروني ، وسيتم معالجته وفقًا لذلك.'
            );
        } catch (\Exception $e) {
            $return_array = array(
                'is_error' => 1,
                'message' => "Email not sent. Please try again. " . $e->getMessage(),
                'message_ar' => "البريد لم يرسل. حاول مرة اخرى. " . $e->getMessage()
            );
        }
        return $return_array;
    }

    public function htmlToImage() {
        $imageToSave = Request()->input("imageToSave");
        $imageName = Request()->input("imageName");

        $imagedata = base64_decode($imageToSave);

        $file = base_path() . ThemeService::ConfigValue('HTML_TO_CONVAS_URL') . $imageName . '.png';
        file_put_contents($file, $imagedata);

        $imageurl = ThemeService::ConfigValue('APP_URL') . 'uploads/images/sharing/' . $imageName . '.png';

        $return_array = array(
            'is_error' => 0,
            'image_url' => $imageurl,
        );
        return $return_array;
    }

    public function loadMoreArticles() {
        $sectionId = Request()->input("sectionId");
        $subSectionId = Request()->input("subSectionId");
        $limit = Request()->input("limit");
        $offset = Request()->input("offset");
        if (!empty($subSectionId[0])) {
            $articles = article::whereIn('section_id', $sectionId)
                    ->whereIn('sub_section_id', $subSectionId)
                    ->where('publish_time', '<', DB::raw(ThemeService::ConfigValue('GIVEN_TIME')))
                    ->orderBy('publish_time', 'DESC')
                    ->offset($offset)
                    ->limit($limit)
                    ->get();
        } else {
            $articles = article::whereIn('section_id', $sectionId)
                    ->where('publish_time', '<', DB::raw(ThemeService::ConfigValue('GIVEN_TIME')))
                    ->orderBy('publish_time', 'DESC')
                    ->offset($offset)
                    ->limit($limit)
                    ->get();
        }
        $html = view('theme::ajax.load_more_articles', ['articles' => $articles]);
        return $html;
    }

    public function loadMoreArticlesLiveAndArchive() {
        $sectionId = Request()->input("sectionId");
        $subSectionId = Request()->input("subSectionId");
        $cmsSectionId = Request()->input("cmsSectionId");
        $cmsSubSectionId = Request()->input("cmsSubSectionId");
        $limit = Request()->input("limit");
        $offset = Request()->input("offset");
        $liveArticlesCount = Request()->input("liveTotal");
        $archiveArticlesCount = Request()->input("archiveTotal");

        if (!empty($subSectionId[0])) {
            if (ThemeService::ConfigValue('MULTI_COUNTRIES') == 1) {
                $articles_query = article_multi_section::distinct('ams_article_id')->select('ams_article_id')->whereIn("ams_subsection_id", $subSectionId);
                $articles_query = article::whereIn("cms_article_id", $articles_query);
            } else {
                $articles_query = article::whereIn('section_id', $sectionId)->whereIn('sub_section_id', $subSectionId);
            }
            $archive_articles_query = article_archive::whereIn('section_id', $cmsSectionId)->whereIn('sub_section_id', $cmsSubSectionId);
        } else {
            if (ThemeService::ConfigValue('MULTI_COUNTRIES') == 1) {
                $articles_query = article_multi_section::distinct('ams_article_id')->select('ams_article_id')->whereIn("ams_section_id", $sectionId);
                $articles_query = article::whereIn("cms_article_id", $articles_query);
            } else {
                $articles_query = article::whereIn('section_id', $sectionId);
            }
            $archive_articles_query = article_archive::whereIn('section_id', $cmsSectionId);
        }

        $limit_top = $offset + $limit;
        if ($limit_top <= $liveArticlesCount) {
            $articles = $articles_query->where('max_publish_time', '<', DB::raw(ThemeService::ConfigValue('GIVEN_TIME')))
                            ->orderBy('max_publish_time', 'desc')
                            ->offset($offset)
                            ->limit($limit)->get();
        } else {
            $limit_articles_archive = $limit_top - $liveArticlesCount;
            $archive_pageno = 0;
            if ($limit_articles_archive > $limit) {
                $archive_pageno = ceil($limit_articles_archive / $limit);
                $offset_archive = $limit_articles_archive - (($archive_pageno - 1) * $limit);
                $articles = [];
                $offset_archive = (($archive_pageno - 2) * $limit) + $offset_archive;
                $limit_articles_archive = $limit;
            } else {
                $articles = $articles_query->where('max_publish_time', '<', DB::raw(ThemeService::ConfigValue('GIVEN_TIME')))
                                ->orderBy('max_publish_time', 'desc')
                                ->offset($offset)
                                ->limit($limit)->get();
                $offset_archive = 0;
            }
            $articles_archive = $archive_articles_query->where('publish_time', '<', DB::raw(ThemeService::ConfigValue('GIVEN_TIME')))
                    ->orderBy('publish_time', 'desc')
                    ->offset($offset_archive)
                    ->limit($limit_articles_archive)
                    ->get();
            if (count($articles_archive) > 0) {
                $theme_controller_class = 'Themes\\' . ThemeService::Name() . '\\controllers\\' . CommonController::controller_class_name();
                if (class_exists($theme_controller_class)) {
                    $themeController = new $theme_controller_class();
                    if (method_exists($themeController, 'getArchiveArticle')) {
                        $function_name = 'getArchiveArticle';
                        foreach ($articles_archive as $article_archive_list) {
                            $articles[] = $themeController->$function_name($article_archive_list);
                        }
                    }
                }
            }
        }
        $html = view('theme::ajax.load_more_articles', ['articles' => $articles]);
        return $html;
    }

    public function getMoreAuthorLiveAndArchiveArticles() {
        $author_id = Request()->input("authorId");
        $author_cms_id = Request()->input("authorCMSId");
        $limit = Request()->input("limit");
        $offset = Request()->input("offset");
        $liveArticlesCount = Request()->input("liveArticlesCount");
        $archiveArticlesCount = Request()->input("archiveArticlesCount");

        $limit_top = $offset + $limit;
        if ($limit_top <= $liveArticlesCount) {
            $articles = article::where('max_publish_time', '<', DB::raw(ThemeService::ConfigValue('GIVEN_TIME')))
                            ->where('author_id', $author_id)
                            ->orderBy('max_publish_time', 'desc')
                            ->offset($offset)
                            ->limit($limit)->get();
        } else {
            $limit_articles_archive = $limit_top - $liveArticlesCount;
            $archive_pageno = 0;
            if ($limit_articles_archive > $limit) {
                $archive_pageno = ceil($limit_articles_archive / $limit);
                $offset_archive = $limit_articles_archive - (($archive_pageno - 1) * $limit);
                $articles = [];
                $offset_archive = (($archive_pageno - 2) * $limit) + $offset_archive;
                $limit_articles_archive = $limit;
            } else {
                $articles = article::where('max_publish_time', '<', DB::raw(ThemeService::ConfigValue('GIVEN_TIME')))
                                ->where('author_id', $author_id)
                                ->orderBy('max_publish_time', 'desc')
                                ->offset($offset)
                                ->limit($limit)->get();
                $offset_archive = 0;
            }
            $articles_archive = article_archive::where('publish_time', '<', DB::raw(ThemeService::ConfigValue('GIVEN_TIME')))
                    ->where('author_id', $author_cms_id)
                    ->orderBy('publish_time', 'desc')
                    ->offset($offset_archive)
                    ->limit($limit_articles_archive)
                    ->get();

            if (count($articles_archive) > 0) {
                $theme_controller_class = 'Themes\\' . ThemeService::Name() . '\\controllers\\' . CommonController::controller_class_name();
                if (class_exists($theme_controller_class)) {
                    $themeController = new $theme_controller_class();
                    if (method_exists($themeController, 'getArchiveArticle')) {
                        $function_name = 'getArchiveArticle';
                        foreach ($articles_archive as $article_archive_list) {
                            $articles[] = $themeController->$function_name($article_archive_list);
                        }
                    }
                }
            }
        }


        $section = section::find_np(ThemeService::ConfigValue('AUTHOR_ARTICLE_SECTION_ID'));
        $html = view('theme::ajax.load_more_author_articles', ['articles' => $articles, 'section' => $section]);
        return $html;
    }

    public function renderWidgets() {
        $row_number = 0;
        if (null !== Request()->input("row_number")) {
            $row_number = Request()->input("row_number");
            $np_page_id = Request()->input("np_page_id");
        }
        $page_bootstrap = bootstrap_rows::where('page_id', $np_page_id)->first();
        if ($page_bootstrap) {
            if (!empty($page_bootstrap->bootstrap_tags)) {
                $rows = json_decode($page_bootstrap->bootstrap_tags, true);
            }
        }
        $widgets = page::find_widgets_np($np_page_id);

        $bootstrap_rows = [];
        //Get Static articles id to temove from Articles Filter
        if (ThemeService::ConfigValue("SKIP_STATIC_ARTICLES") == 1) {
            static $static_articles_id = [];
            foreach ($rows as $row) {//
                foreach ($row as $column) {
                    $bootstrap_column = new \stdClass();
                    foreach ($column as $key => $widget_id) {
                        if (is_integer($key)) {
                            $widget = $widgets[$widget_id];
                            $widgetClasses = json_decode($widget->widget_data);
                            if (isset($widgetClasses->articles_ids)) {
                                foreach ($widgetClasses->articles_ids as $article_id) {
                                    $static_articles_id[] = $article_id;
                                }
                            }
                        }
                    }
                }
            }
            $static_articles_id = array_unique($static_articles_id);
            PageService::SetStaticArticlesPage($static_articles_id);
        }
        foreach ($rows as $key_row => $row) {
            if ($key_row >= $row_number) {
                $bootstap_row = new \stdClass();
                $first_widget_id = (!empty($row[0]) && !empty($row[0][0])) ? $row[0][0] : 0;
                $bootstap_row->is_full_container = \Layout\Website\Models\WebsiteWidget::is_full_container($first_widget_id);
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
                                    } else if (is_numeric(strpos(strtolower($widgetClass), "fluid["))) {
                                        $bootstap_row->fluid_classes = str_replace(array("fluid[", "]"), array("", ""), $widgetClass);
                                    }
                                }
                            }
                        }
                    }

                    $bootstrap_column->widgets = [];
                    foreach ($column as $key => $widget_id) {
                        if (is_integer($key)) {
                            $bootstrap_column->widgets[] = WidgetService::widget_by_widget_data($widgets[$widget_id]);
                        }
                    }
                    //                $bootstrap_column->widgets = WidgetService::widgets($column);
                    $bootstap_row->columns[] = $bootstrap_column;
                }
                $bootstrap_rows[] = $bootstap_row;
            }
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
            return View::make('theme::bootstrap.with_container', ['rows' => $bootstrap_rows, 'special_classes' => $special_classes, 'page' => $page])->render();
        }

        return View::make('theme::bootstrap.without_container', ['rows' => $bootstrap_rows, 'page' => $page])->render();
    }

    public function ajaxWidgets() {
        $arr_widget = [];
        if (null !== Request()->input("arr_widget")) {
            $arr_widget = Request()->input("arr_widget");
            $np_page_id = Request()->input("np_page_id");
            $article = Request()->input("article");
        }
        if (!empty($article)) {
            $article = new article($article);
            PageService::SetArticle($article);
        }
        $widgets_db = \App\Models\widget::whereIn("np_widget_id", $arr_widget)->get();
        $widgets = [];
        foreach ($widgets_db as $widget) {
            $widgets[$widget->np_widget_id] = WidgetService::widget_by_widget_data($widget)->render();
        }
        return $widgets;
    }

    public static function sendyNewsletter(){

        $email = Request()->input("email");
        if(empty($email)){
            return 'Please enter a valid email address';
        }

        $country = CommonController::getCountryDataThroughIP();
        $country_name = !empty($country['country_code']) ?  $country['country_code'] : '';

        $url = ThemeService::ConfigValue('SENDY_URL');
        $arraydata = http_build_query(
            array(
                "country"=>$country_name,
                "email"=>$email,
                "api_key" => ThemeService::ConfigValue('SENDY_API_KEY'),
                "list" => ThemeService::ConfigValue('SENDY_LIST'),
                "boolean" =>'true',
            )
        );

        $opts = array('http' => array('method'  => 'POST', 'header'  => 'Content-type: application/x-www-form-urlencoded', 'content' => $arraydata));
        $context  = stream_context_create($opts);
        $message = file_get_contents($url, false, $context);

        if(empty($message)){
            $message = 'Try again later';
        }elseif($message==1 || $message=='Already subscribed.'){
            $message = 'Thank you for subscribing.';
        }elseif($message=='Email is Wrong'){
            $message = 'Please enter a valid email address.';
        }
        return $message;
    }

}
