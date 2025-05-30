<?php

/**
 * Created by PhpStorm.
 * User: timur
 * Date: 22.11.2018
 * Time: 19:07
 */

namespace Layout\Website\Helpers;

class TextHelper {

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
        //$result			= ( $text_language == 'ar' ? '...' . $result : $result . '...' );
        if ($ellipsis) {
            $result = ( $text_language == 'ar' ? $result . '...' : $result . '...' );
        }

        return $result;
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

    public static function hex2rgb($color, $opacity = 1) {
        $hex = str_replace('#', '', $color);
        $r = 'FF';
        $g = 'FF';
        $b = 'FF';
        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        return 'rgba(' . $r . ',' . $g . ',' . $b . ',' . $opacity . ')';
    }

    public static function cleanSpecialCharacters($text) {
        $text = mb_convert_encoding($text, 'Windows-1252', 'UTF-8');
        return $text;
    }

    public static function cleanFrenchCharacters($text) {
        $text = str_replace('&#039;', "'", $text);
        $text = str_replace("&Egrave;", "È", $text);
        $text = str_replace("&Eacute;", "É", $text);
        $text = str_replace("&Ecirc;", "Ê", $text);
        $text = str_replace("&Euml;", "Ë", $text);
        $text = str_replace("&egrave;", "è", $text);
        $text = str_replace("&eacute;", "é", $text);
        $text = str_replace("&ecirc;", "ê", $text);
        $text = str_replace("&agrave;", "à", $text);
        $text = str_replace("&ugrave;", "ù", $text);
        $text = str_replace("&uacute;", "ú", $text);
        $text = str_replace("&amp;", "&", $text);
        $text = str_replace('"', '', $text);
        return $text;
    }

//    this function returns $nbOfParagraphs paragraphs from the text
    public static function getTextParagraphs($text, $nbOfParagraphs = 1) {
        $matches = array();
        $result = '';
//        remove empty paragraphs
        $text = preg_replace('/<p[^>]*>([\s]?)*<\\/p[^>]*>/', '', $text);
        $text = str_replace(array('<picture>','</picture>'),array('#start#picture#start#','#end#picutre#end#'),$text);
        if(strpos(mb_substr($text, 0, 5),'<p') === false){
            $text = '#first#p#first#' . $text;
        }

        if(strpos($text,'<p') === false){
            $text =  $text . '</p>';
        }else{
            $pos = strpos($text,'<p>');
            if ($pos !== false) {
                $text = substr_replace($text, '</p><p>', $pos, strlen('<p>'));
            }
        }

        $text = str_replace('#first#p#first#','<p>',$text);
        preg_match_all("'<(p)(.*?)>(.*?)</(p)>'si", $text, $matches);
        if (isset($matches[0]) && !empty($matches[0])) {
            for ($i = 0; $i < $nbOfParagraphs; $i++) {
             $result .= isset($matches[0][$i]) ? $matches[0][$i] : '';   
            }
        } else {
            preg_match_all("'<(div)(.*?)>(.*?)</(div)>'si", $text, $matches);
            if (isset($matches[0]) && !empty($matches[0])) {
                for ($i = 0; $i < $nbOfParagraphs; $i++) {
                 $result .= isset($matches[0][$i]) ? $matches[0][$i] : '';   
                }
            }else{
                $result = $text;
            }
        }
        $result = str_replace(array('#start#picture#start#','#end#picutre#end#'),array('<picture>','</picture>'),$result);
        return $result;
    }

    public static function removeBasicTags($text) {
        $text = preg_replace(
                array(
            '/<\\/?html(\\s+.*?>|>)/', //remove html tag
            '/<\\/?head(\\s+.*?>|>)/', //remove head tag
            '/<\\/?body(\\s+.*?>|>)/', //remove body tag
            '/<meta [^>]+>/m'//remove meta tags 
                ), array('', '', '', ''), $text);

        return $text;
    }

}
