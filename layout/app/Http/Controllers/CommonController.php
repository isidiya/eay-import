<?php

namespace App\Http\Controllers;

use GeoIp2\Exception\AddressNotFoundException;
use Layout\Website\Services\ThemeService;
use Illuminate\Support\Facades\Cookie;

class CommonController extends Controller {

    public static function verifyRecaptcha($captcha) {
        if (!$captcha) {
            return 0; //'Please check the the captcha form.';
        }
        $secretKey = ThemeService::ConfigValue('RECAPTCHA_SECRET_KEY');
        $ip = $_SERVER['REMOTE_ADDR'];
        $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=" . $secretKey . "&response=" . $captcha . "&remoteip=" . $ip);
        $responseKeys = json_decode($response, true);
        if (intval($responseKeys["success"]) !== 1) {
            return 0; //'You are spammer ! Get the @$%K out';
        } else {
            return 1; //'Thanks for posting comment.';
        }
    }

    public static function WriteToTestFile($data = 'in', $mode = 'ab+', $file = "C:/test.txt") {
        if (is_object($data)) {
            $data = json_decode(json_encode($data), true);
        }

        if (is_array($data)) {
            $data = print_r($data, true);
        }

        $data .= "\n\n";
        $data .= " ------------------------------------------------------ ";
        $data .= "\n\n";

        $fp = fopen($file, $mode);
        fwrite($fp, $data);
        fclose($fp);
    }

    public static function checkExternalLink($link) {
        $link = urldecode($link);
        $poshttp = strpos($link, 'http://');
        $poshttps = strpos($link, 'https://');
        if (is_numeric($poshttp) || is_numeric($poshttps)) {
            return '_blank';
        } else {
            return '';
        }
    }

    public static function createPdfPreview($file_path) {

        $file_path = pathinfo($file_path, PATHINFO_DIRNAME) . '/' . pathinfo($file_path, PATHINFO_FILENAME);
        $pdf_path = public_path() . "/" . $file_path;

        $gs = 'gs -dNOPAUSE -sDEVICE=jpeg -r72  -dLastPage=1 -sOutputFile="' . $pdf_path . '.jpeg" "' . $pdf_path . '.pdf"';
        exec($gs, $output, $return_var);
        return $file_path . '.jpeg';
    }

    public static function replaceUrl($str_text) {
        $toChar = "-";
        if (ThemeService::ConfigValue('TO_CHARACHTERS')) {
            $toChar = explode(',', ThemeService::ConfigValue('TO_CHARACHTERS'));
        }
        if (ThemeService::ConfigValue('REPLACE_CHARACHTERS')) {
            $replaceArray = explode(',', ThemeService::ConfigValue('REPLACE_CHARACHTERS'));
            $str_text = str_replace($replaceArray, $toChar, $str_text);
        }
        return $str_text;
    }

    public static function revertReplaceUrl($str_text) {
        $toChar = "-";
        if (ThemeService::ConfigValue('TO_CHARACHTERS')) {
            $toChar = explode(',', ThemeService::ConfigValue('TO_CHARACHTERS'));
        }
        if (ThemeService::ConfigValue('REPLACE_CHARACHTERS')) {
            $replaceArray = explode(',', ThemeService::ConfigValue('REPLACE_CHARACHTERS'));
            $str_text = str_replace($toChar, $replaceArray, $str_text);
        }
        return $str_text;
    }

    public static function getTodayDate($format = "Y-m-d H:i:s") {
        return date($format);
    }

    public static function limitTextByChar($string, $start = 0, $length = 35, $text_language = 'en', $lan = '', $ellipsis = true, $replaceBr = 0) {
        $string = self::convertToPlainText($string, $replaceBr); // Remove all tags before stripping

        if ($length >= strlen($string)) {
            return $string;
        }

        $length = ( $text_language == "ar" ? $length * 2 : $length );
        if ($length >= strlen($string)) {
            return $string;
        }
        $restofstring = substr($string, $start, strlen($string) - $start);
        $wrappedwords = wordwrap($restofstring, $length, '$$', 1);
        $result = substr($restofstring, 0, strpos($wrappedwords, '$$'));
        if ($ellipsis) {
            $result = ( $text_language == 'ar' ? $result . '...' : $result . '...' );
        }

        return $result;
    }

    public static function get_words($init_body, $count = 10) {

        $init_body = strip_tags($init_body);
        $init_body = str_replace("<br>", "\n", $init_body);
        $init_body = preg_replace(
                array(
            // Remove invisible content
            '/<br>|\n/',
            '#<script(.*?)>(.*?)</script>#is',
            '/<img[^>]+\>/i',
            '/<iframe.*?\/iframe>/i',
            '~<blockquote(.*?)>(.*)</blockquote>~si',
            '/(\*\*media\[(\d|,)*]\*\*)/',
            '~<!--.+?-->~s',
            '/<([a-z][a-z0-9]*)[^>]*?(\/?)>/i' //remove all attributes
                ), array(
            ' ', ' ', ' ', ' ', ' '
                ), $init_body);
        $init_body = str_replace("\n", "<br>", $init_body);


        $explode_body = explode(' ', $init_body);
        if (isset($explode_body[$count + 1])) {
            return implode(' ', array_slice(explode(' ', $init_body), 0, $count)) . '...';
        }
        return implode(' ', array_slice(explode(' ', $init_body), 0, $count));
    }

    public static function convertToPlainText($text, $replaceBr = 0) {
        if ($replaceBr) {
            $text = strip_tags($text, '<br>');
            $text = str_replace('<br>', ' ', $text);
        } else {
            $text = strip_tags($text);
        } {// Remove **media[IDs]**
            $pattern = "/(\*\*media\[(\d|,)*]\*\*)/";
            $matches = array();
            preg_match_all($pattern, $text, $matches);

            $matches = $matches[0];

            $text = str_replace($matches, '', $text);
        } {// Remove **pullquote**
            $text = str_replace('**pullquote**', '', $text);
        } {// Remove ---
            $text = str_replace('---', '', $text);
        }

        return $text;
    }

    public static function strip($string, $length, $addPoints, $removeTags = true, $remove_double_qoutes = 0, $replaceBrWithSpace = false) {

        if ($replaceBrWithSpace) { // replacing br with space used for authors when are in text .
            $string = str_replace('<br>', ' ', $string);
        }


        $string = self::convertToPlainText($string); // Remove all tags before stripping

        if ($addPoints) {
            $append = '...';
        } else {
            $append = '';
        }
        $string = trim($string);

        if ($removeTags) {
            $string = strip_tags($string);
        }

        $newStringArray = explode(" ", $string);
        $i = 0;
        while ($i < $length) {
            if ($remove_double_qoutes == 1) {
                $text = html_entity_decode($newStringArray [$i]);
                $text = str_replace('"', "'", $text);
                echo $text;
            } else {
                echo html_entity_decode($newStringArray [$i]);
            }

            echo " ";
            $i ++;
        }
        if (count($newStringArray) > $length) {
            echo $append;
        }
    }

    public static function controller_class_name() {
        $funcName = ucwords(str_replace('_', '', ThemeService::Name())) . 'Controller';
        return $funcName;
    }

    public static function createCookie($name, $value, $main_url, $domain = "", $expire = "30", $path = "/", $httponly = true) {//30 number of days
        if (is_numeric(strpos($main_url, "https://"))) {
            $secure = true;
        } else {
            $secure = false;
        }
        setcookie($name, $value, time() + (86400 * $expire), $path, $domain, $secure, $httponly);
    }

    public static function getCountryDataThroughIP() {
        if (1) {
            $reader = new \GeoIp2\Database\Reader(ThemeService::MainMmdbPath() . '/GeoLite2-Country.mmdb');
            try {

				if (isset($_SERVER['HTTP_X_SUCURI_COUNTRY'])) {
                    $CC = $_SERVER['HTTP_X_SUCURI_COUNTRY'];
                    $countryData = array("country_code" => $CC , "country_name" => "");
					setcookie('USER_COUNTRY_CODE', json_encode($countryData), time() + 60 * 60 * 24 * 30, '/');
					return $countryData;
                }


                if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                    $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                    $country = $reader->country(trim($ip[0]));
                }
                if (empty($country) && isset($_SERVER['HTTP_X_SUCURI_CLIENTIP'])) {
                    $_SERVER["REMOTE_ADDR"] = $_SERVER['HTTP_X_SUCURI_CLIENTIP'];
                }
                //$country = $reader->country('2.17.139.12'); // Turkey
                // $country = $reader->country('121.58.212.162'); // PHILIPINO
                //$country = $reader->country('213.175.168.67'); // LEBANON
                //$country = $reader->country('212.138.92.10'); // SA
                if (empty($country)) {
//                    if ($_SERVER['REMOTE_ADDR'] == "127.0.0.1") {
//                        return array("country_code" => ThemeService::ConfigValue("COUNTRY_DATA")['country_code'], "country_name" => ThemeService::ConfigValue("COUNTRY_DATA")['country_name']);
//                    }
                    $country = $reader->country($_SERVER['REMOTE_ADDR']);
                }

                $countryCode = $country->country->isoCode;
                $countryName = $country->country->names['en'];
            } catch (AddressNotFoundException $ex) {
                $countryCode = "";
                $countryName = "";
            }
            $countryData = array("country_code" => $countryCode, "country_name" => $countryName);
            setcookie('USER_COUNTRY_CODE', json_encode($countryData), time() + 60 * 60 * 24 * 30, '/');
        } else {
            $countryData = json_decode($_COOKIE["USER_COUNTRY_CODE"], true);
            $countryCode = $countryData['country_code'];
            $countryName = $countryData['country_name'];
        }
        return array("country_code" => $countryCode, "country_name" => $countryName);
    }

    public static function cleanAmpBodyText($bodytext, $remove_tag = '', $clean_tag = '') {
        // remove tag is used only to remove the tag and keep its content for example if we use 'h2,h1'
        //  <p><h1>php</h1><h2>Laravel</h2></p>    =>   <p>php Laravel </laravel>

        $remove_tag_array = explode(',', $remove_tag);
        foreach ($remove_tag_array as $remove_tag_list) {
            $bodytext = preg_replace("/<\\/?" . $remove_tag_list . "(.|\\s)*?>/", '', $bodytext);
        }

        // clear tag is used only to claen the tag only and  it keeps the content for example if we use 'br,span'
        //  <p><br type="moz" /><span type="o-">Laravel</span></p>    =>   <p><br/><span>Laravel </span></laravel>
        $clean_tag_array = explode(',', $clean_tag);
        foreach ($clean_tag_array as $clean_tag_list) {
            $bodytext = preg_replace('/<' . $clean_tag_list . ' (.*?)>/m', '', $bodytext);
        }
        return $bodytext;
    }

    public static function cleanAmpBodyAttributes($bodytext, $remove_attributes = '') {
        $remove_attributes_array = explode(',', $remove_attributes);
        foreach ($remove_attributes_array as $remove_attributes_list) {
            $bodytext = preg_replace('/' . $remove_attributes_list . '="(.*?)"/', '', $bodytext);
        }
        return $bodytext;
    }

    public static function sanitize_output($buffer) {
        $search = array(
            '/\>[^\S ]+/s', // strip whitespaces after tags, except space
            '/[^\S ]+\</s', // strip whitespaces before tags, except space
            '/(\s)+/s', // shorten multiple whitespace sequences
            '/<!--(.|\s)*?-->/', // Remove HTML comments
            '/\>\s+\</', // Remove Extra space between Tags
        );

        $replace = array(
            '>',
            '<',
            '\\1',
            '',
            '><',
        );

        $buffer = preg_replace($search, $replace, $buffer);

        return $buffer;
    }

}
