<?php

/**
 * Created by PhpStorm.
 * User: timur
 * Date: 22.11.2018
 * Time: 19:10
 */

namespace Layout\Website\Helpers;

use App\Http\Controllers\CommonController;
use Layout\Website\Helpers\HijriDate;
use Layout\Website\Helpers\Calendar;
use Layout\Website\Helpers\datetime;
use Layout\Website\Services\ThemeService;

class DateTimeHelper {

    public static function getDisplayDateWithHijri($timestamp = '', $display_weekday = true, $is_english = false, $display_time = false) {
        global $dateFormat;

        $timestamp_today = !$timestamp ? date('Y-m-d', time()) : date('Y-m-d', $timestamp);
        $timestamp = !$timestamp ? time() : $timestamp;

        $c = new Calendar();
        if(ThemeService::ConfigValue('DATE_HIJRI_OFFSET')){
            $timestamp_today =  $timestamp_today . '+'. ThemeService::ConfigValue('DATE_HIJRI_OFFSET') . ' day';
        }
        $timestamp_today = date('Y-m-d', strtotime($timestamp_today));
        $d = new datetime($timestamp_today, null, 'ar', $c);
        list($cday, $cmonth, $cyear) = explode('-', $d->format('_j-_n-_Y'));

        $hijri = array($cmonth, $cday, $cyear);

        $dayofweek = date('D', $timestamp);
        $days = array("Mon" => "الاثنين", "Tue" => "الثلاثاء", "Wed" => "الأربعاء", "Thu" => "الخميس", "Fri" => "الجمعة", "Sat" => "السبت", "Sun" => "الاحد",);
        foreach ($days as $en => $ar) {
            if ($en == $dayofweek) {
                $dayofweek_ar = $ar;
            }
        }
        if ($is_english) {
            $weekday = $dayofweek;
        } else {
            $weekday = $dayofweek_ar;
        }


        if ($is_english) {
            $hijriMonth = HijriDate::monthNameEnglish($hijri[0]);
            $hijriDate = '';
            $seperator = ', ';
        } else {
            $hijriMonth = HijriDate::monthName($hijri[0]);
            $hijriDate = ' هـ';
            $seperator = ' / ';
        }

        if ($display_time) {
            $time = " - " . date('H:i', $timestamp);
        } else {
            $time = '';
        }

        if ($is_english) {
            return ($display_weekday ? $weekday . $seperator : '') . $hijriMonth . ' ' . ($hijri[1] ) . $seperator . $hijri[2] . $hijriDate . $time;
        } else {
            return ($display_weekday ? $weekday . $seperator : '') . ($hijri[1] ) . $seperator . $hijriMonth . $seperator . $hijri[2] . $hijriDate . $time;
        }
    }

    public static function getDateDifference($dateTime1, $dateTime2 = '', $english = true, $ago_time = false) {
        //Here we check if we have a function getPostArticlePreview article in Theme controller
        $theme_controller_class = 'Themes\\' . ThemeService::Name() . '\\controllers\\' . ThemeService::ThemeController();
        if (class_exists($theme_controller_class)) {
            $themeController = new $theme_controller_class();
            if (method_exists($themeController, 'getDateDifference')) {
                return $themeController->getDateDifference($dateTime1, $dateTime2, $english, $ago_time);
            }
        }


        $publichTime = $dateTime1;

        if (!$dateTime2)
            $dateTime2 = new \DateTime();
        else
            $dateTime2 = new \DateTime($dateTime2);

        $dateTime1 = new \DateTime($dateTime1);
        $interval = $dateTime1->diff($dateTime2);

        $numberOfDays = $interval->format('%a'); //%y years %m months %a days %h hours %i minutes
        //ms: malay language
        if ($english === 'ms') {
            $zh_month = '';
            if ($numberOfDays > 2) {
                $dateToTime = strtotime($publichTime);
                $months = array('Jan' => 'Jan', 'Feb' => 'Feb', 'Mar' => 'Mac', 'Apr' => 'Apr', 'May' => 'Mei', 'June' => 'Jun', 'July' => 'Jul', 'Aug' => 'Ogos', 'Sep' => 'Sep', 'Oct' => 'Okt', 'Nov' => 'Nov', 'Dec' => 'Dis');
                $en_month = date("M", $dateToTime);

                foreach ($months as $en => $zh) {
                    if ($en == $en_month) {
                        $zh_month = $zh;
                    }
                }
                $display_month = $zh_month;

                $day_en = date('j', $dateToTime);
                $year_en = date('Y', $dateToTime);

                $result = $day_en . ' ' . $display_month . ' ' . $year_en; //echo 2018 八月 13
            }

            if ($numberOfDays <= 2 && $numberOfDays >= 1) {
                $result = $numberOfDays . ' hari yang lalu'; //echo 1d ago
            }


            if (empty($numberOfDays) || $numberOfDays == 0) {
                $numberOfHours = $interval->format('%h');

                if ($numberOfHours > 0) {
                    $result = $numberOfHours . ' jam yang lalu'; //echo 14h ago
                } else {
                    $numberOfMinutes = $interval->format('%i');
                    $result = $numberOfMinutes . ' minit yang lalu'; //echo 10m ago
                }
            }
        } elseif ($english === 'zh') {
            //zh: chinese
            $zh_month = '';
            if ($numberOfDays > 2) {
                $dateToTime = strtotime($publichTime);
                $months = array('Jan' => '一月', 'Feb' => '二月', 'Mar' => '三月', 'Apr' => '四月', 'May' => '五月', 'June' => '六月', 'July' => '七月', 'Aug' => '八月', 'Sep' => '九月', 'Oct' => '十月', 'Nov' => '十一月', 'Dec' => '十二月');
                $en_month = date("M", $dateToTime);

                foreach ($months as $en => $zh) {
                    if ($en == $en_month) {
                        $zh_month = $zh;
                    }
                }
                $display_month = $zh_month;

                $day_en = date('j', $dateToTime);
                $year_en = date('Y', $dateToTime);
                $month_en = date('m', $dateToTime);

                $result = $year_en . '年 ' . $month_en . '月 ' . $day_en . '日'; //echo 2018 八月 13
            }

            if ($numberOfDays <= 2 && $numberOfDays >= 1) {
                $result = $numberOfDays . '天前'; //echo 1d ago
            }


            if (empty($numberOfDays) || $numberOfDays == 0) {
                $numberOfHours = $interval->format('%h');

                if ($numberOfHours > 0) {
                    $result = $numberOfHours . ' 小时前'; //echo 14h ago
                } else {
                    $numberOfMinutes = $interval->format('%i');
                    $result = $numberOfMinutes . ' 分钟前'; //echo 10m ago
                }
            }
        } elseif ($english) {

            if ($numberOfDays > 2) {
                $result = date('d M Y ', strtotime($publichTime)); //echo 13 Sep 2018
            }

            if ($numberOfDays <= 2 && $numberOfDays >= 1) {
                $result = $numberOfDays . 'd ago'; //echo 1d ago
            }


            if (empty($numberOfDays) || $numberOfDays == 0) {
                $numberOfHours = $interval->format('%h');

                if ($numberOfHours > 0) {
                    $result = $numberOfHours . ' h ago'; //echo 14h ago
                } else {
                    $numberOfMinutes = $interval->format('%i');
                    $result = $numberOfMinutes . ' m ago'; //echo 10m ago
                }
            }
        } else {

            if ($numberOfDays > 2 || $ago_time) {

                $dateToTime = strtotime($publichTime);

                $dayofweek = date('D', $dateToTime);
                $days = array("Mon" => "الاثنين", "Tue" => "الثلاثاء", "Wed" => "الأربعاء", "Thu" => "الخميس", "Fri" => "الجمعة", "Sat" => "السبت", "Sun" => "الاحد",);
                foreach ($days as $en => $ar) {
                    if ($en == $dayofweek) {
                        $ar_weekday = $ar;
                    }
                }
                $display_days = $ar_weekday;

                $months = array("Jan" => "يناير", "Feb" => "فبراير", "Mar" => "مارس", "Apr" => "أبريل", "May" => "مايو", "Jun" => "يونيو", "Jul" => "يوليو", "Aug" => "أغسطس", "Sep" => "سبتمبر", "Oct" => "أكتوبر", "Nov" => "نوفمبر", "Dec" => "ديسمبر");
                $en_month = date("M", $dateToTime);

                foreach ($months as $en => $ar) {
                    if ($en == $en_month) {
                        $ar_month = $ar;
                    }
                }
                $display_month = $ar_month;


                $day_en = date('j', $dateToTime);
                $year_en = date('Y', $dateToTime);
                //to use different format, fill the variable in theme.php and add another case
                if (ThemeService::ConfigValue('DIFFERENCE_FORMAT') && ThemeService::ConfigValue('DIFFERENCE_FORMAT')) {
                    switch (ThemeService::ConfigValue('DIFFERENCE_FORMAT')) {
                        case 'l dd-mm-YYYY':
                            $result = $display_days . ' ' . $day_en . '-' . date("m", $dateToTime) . '-' . $year_en;
                            break;
                    }
                } else {
                    $result = $day_en . ' ' . $display_month . ' ' . $year_en; //echo 13سبتمبر 2018 
                }
            } else if ($numberOfDays <= 2 && $numberOfDays >= 1) {


                $result = 'منذ ';

                if ($numberOfDays == 2) {
                    $result .= ' يومين';

                } else {
                    $result .= ' يوم';

                }
            } else if (empty($numberOfDays) || $numberOfDays == 0) {
                
                $numberOfHours = $interval->format('%h');

                $result = 'منذ ';

                if ($numberOfHours > 2 && $numberOfHours < 11 ) {
                    $result .= $numberOfHours . ' ساعات';

                }elseif($numberOfHours > 10){
                    $result .= $numberOfHours . ' ساعة ';

                }elseif ($numberOfHours == 2) {
                    $result .= 'ساعتين ';

                } elseif ($numberOfHours == 1) {
                    $result .= 'ساعة ';

                } else {
                    $numberOfMinutes = $interval->format('%i');

                    if ($numberOfMinutes > 2 && $numberOfMinutes < 11) {
                        $result .= $numberOfMinutes . ' دقائق';

                    } elseif ($numberOfMinutes < 2) {
                        $result .= 'دقيقة ';

                    }elseif( $numberOfMinutes > 10){
                        $result .= $numberOfMinutes . ' دقيقة ';

                    }elseif ($numberOfMinutes == 2) {
                        $result .= 'دقيقتين ';
                    }
                }
            }
        }

        return $result;
    }

    public static function getDisplayDate($timestamp = '', $display_weekday = false, $display_time = false, $is_english = false, $extra_option = false, $time_detect = false) {
        //Here we check if we have a function getDisplayDate article in Theme controller Added for lematin theme
        $theme_controller_class = 'Themes\\' . ThemeService::Name() . '\\controllers\\' . ThemeService::ThemeController();
        if (class_exists($theme_controller_class)) {
            $themeController = new $theme_controller_class();
            if (method_exists($themeController, 'getDisplayDate')) {
                return $themeController->getDisplayDate($timestamp, $display_weekday, $display_time, $is_english, $extra_option, $time_detect);
            }
        }
        $timestamp = !$timestamp ? time() : $timestamp;
        $month_en = date('m', $timestamp);
        $day_en = date('j', $timestamp);
        $year_en = date('Y', $timestamp);
        $hour_en = date('H', $timestamp);
        if (ThemeService::ConfigValue("DATE_12HOURS")) {
            $hour_en = date('h', $timestamp);
        }
        $minutes_en = date('i', $timestamp);
        $time_option = date('A', $timestamp);

        if ($extra_option) {
            if (isset($extra_option['date_format'])) {
                $date_form = $extra_option['date_format'];
                if ($is_english === 'fr') {
                    $fr_date = date($date_form, $timestamp);

                    $days = array("Mon" => "Lun", "Tue" => "Mar", "Wed" => "Mer", "Thu" => "Jeu", "Fri" => "Ven", "Sat" => "Sam", "Sun" => "Dim");
                    foreach ($days as $en_day => $fr_day) {
                        if (is_numeric(strpos($fr_date, $en_day))) {
                            $fr_date = str_replace($en_day, $fr_day, $fr_date);
                        }
                    }

                    $months = array("Jan" => "Jan", "Feb" => "Fév", "Mar" => "Mar", "Apr" => "Avr", "May" => "Mai", "Jun" => "Juin", "Jul" => "Juil", "Aug" => "Août", "Sep" => "Sep", "Oct" => "Oct", "Nov" => "Nov", "Dec" => "Déc");
                    foreach ($months as $en_month => $fr_month) {
                        if (is_numeric(strpos($fr_date, $en_month))) {
                            $fr_date = str_replace($en_month, $fr_month, $fr_date);
                        }
                    }

                    $time = array("AM" => "AM", "PM" => "PM");
                    if ($display_time && $time_detect) {
                        foreach ($time as $en_time => $fr_time) {
                            $fr_date = str_replace(strtolower($en_time), $fr_time, strtolower($fr_date));
                        }
                    }

                    return $fr_date;
                }
                elseif ($is_english === 'ma') {
                     //malay language
                    $ma_date = date($date_form, $timestamp);

                    $days = array("Mon" => "Isnin", "Tue" => "Selasa", "Wed" => "Rabu", "Thu" => "Khamis", "Fri" => "Jumaat", "Sat" => "Sabtu", "Sun" => "Ahad");
                    foreach ($days as $en_day => $ma_day) {
                        if (is_numeric(strpos($ma_date, $en_day))) {
                            $ma_date = str_replace($en_day, $ma_day, $ma_date);
                        }
                    }

                    $months = array("Jan" => "Januari", "Feb" => "Februari", "Mar" => "Mac", "Apr" => "April", "May" => "Mei", "Jun" => "Jun", "Jul" => "Julai", "Aug" => "Ogos", "Sep" => "September", "Oct" => "Oktober", "Nov" => "November", "Dec" => "Disember");
                    foreach ($months as $en_month => $ma_month) {
                        if (is_numeric(strpos($ma_date, $en_month))) {
                            $ma_date = str_replace($en_month, $ma_month, $ma_date);
                        }
                    }

                    $time = array("AM" => "AM", "PM" => "PM");
                    if ($display_time && $time_detect) {
                        foreach ($time as $en_time => $ma_time) {
                            $ma_date = str_replace(strtolower($en_time), $ma_time, strtolower($ma_date));
                        }
                    }

                    return $ma_date;
                }
                elseif ($is_english === 'es') {
                     //spanish language
                    $es_date = date($date_form, $timestamp);

                    $days = array("Monday" => "Lunes", "Tuesday" => "Martes", "Wednesday" => "Miércoles", "Thursday" => "Jueves", "Friday" => "Viernes", "Saturday" => "Sábado", "Sunday" => "Domingo");
                    foreach ($days as $en_day => $ma_day) {
                        if (is_numeric(strpos($es_date, $en_day))) {
                            $es_date = str_replace($en_day, $ma_day, $es_date);
                        }
                    }

                    $months = array("January" => "Enero", "February" => "Febrero", "March" => "Marzo", "April" => "Abril", "May" => "Mayo", "June" => "Junio", "July" => "Julio", "August" => "Agosto", "September" => "Septiembre", "October" => "Octubre", "November" => "Noviembre", "December" => "Diciembre");
                    foreach ($months as $en_month => $ma_month) {
                        if (is_numeric(strpos($es_date, $en_month))) {
                            $es_date = str_replace($en_month, $ma_month, $es_date);
                        }
                    }

                    $time = array("AM" => "AM", "PM" => "PM");
                    if ($display_time && $time_detect) {
                        foreach ($time as $en_time => $ma_time) {
                            $es_date = str_replace(strtolower($en_time), $ma_time, strtolower($es_date));
                        }
                    }

                    return $es_date;
                }
                elseif ($is_english) {
                    return date($date_form, $timestamp);
                } else {
                    $ar_date = date($date_form, $timestamp);

                    $days = array("Mon" => "الاثنين", "Tue" => "الثلاثاء", "Wed" => "الأربعاء", "Thu" => "الخميس", "Fri" => "الجمعة", "Sat" => "السبت", "Sun" => "الاحد");
                    foreach ($days as $en_day => $ar_day) {
                        if (is_numeric(strpos($ar_date, $en_day))) {
                            $ar_date = str_replace($en_day, $ar_day, $ar_date);
                        }
                    }

                    $months = array("Jan" => "يناير", "Feb" => "فبراير", "Mar" => "مارس", "Apr" => "أبريل", "May" => "مايو", "Jun" => "يونيو", "Jul" => "يوليو", "Aug" => "أغسطس", "Sep" => "سبتمبر", "Oct" => "أكتوبر", "Nov" => "نوفمبر", "Dec" => "ديسمبر");
                    foreach ($months as $en_month => $ar_month) {
                        if (is_numeric(strpos($ar_date, $en_month))) {
                            $ar_date = str_replace($en_month, $ar_month, $ar_date);
                        }
                    }

                    $time = array("AM" => "ص", "PM" => "م");
                    if ($display_time && $time_detect) {
                        foreach ($time as $en_time => $ar_time) {
                            $ar_date = str_replace(strtolower($en_time), $ar_time, strtolower($ar_date));
                        }
                    }

                    return $ar_date;
                }
            }
        } else {
            if ($is_english === 'fr') {
                $dayofweek = date('D', $timestamp);
                $days = array("Mon" => "Lun", "Tue" => "Mar", "Wed" => "Mer", "Thu" => "Jeu", "Fri" => "Ven", "Sat" => "Sam", "Sun" => "Dim");
                foreach ($days as $en => $fr) {
                    if ($en == $dayofweek) {
                        $weekday = $fr;
                    }
                }
                if ($display_weekday) {
                    $months = array("Jan" => "Jan", "Feb" => "Fév", "Mar" => "Mar", "Apr" => "Avr", "May" => "Mai", "Jun" => "Juin", "Jul" => "Juil", "Aug" => "Août", "Sep" => "Sep", "Oct" => "Oct", "Nov" => "Nov", "Dec" => "Déc");
                    $en_month = date("M", $timestamp);

                    foreach ($months as $en => $fr) {
                        if ($en == $en_month) {
                            $fr_month = $fr;
                        }
                    }
                    $display_month = $fr_month;
                }
            } elseif ($is_english) {
                if ($display_weekday) {
                    $weekday = date('l', $timestamp);
                    $display_month = date("F", $timestamp) . ' ' . $day_en . ', ';
                    $day_en = '';
                }
            } else {
                $dayofweek = date('D', $timestamp);
                $days = array("Mon" => "الاثنين", "Tue" => "الثلاثاء", "Wed" => "الأربعاء", "Thu" => "الخميس", "Fri" => "الجمعة", "Sat" => "السبت", "Sun" => "الاحد");
                foreach ($days as $en => $ar) {
                    if ($en == $dayofweek) {
                        $weekday = $ar;
                    }
                }
                if ($display_weekday) {
                    $months = array("Jan" => "يناير", "Feb" => "فبراير", "Mar" => "مارس", "Apr" => "أبريل", "May" => "مايو", "Jun" => "يونيو", "Jul" => "يوليو", "Aug" => "أغسطس", "Sep" => "سبتمبر", "Oct" => "أكتوبر", "Nov" => "نوفمبر", "Dec" => "ديسمبر");
                    $en_month = date("M", $timestamp);

                    foreach ($months as $en => $ar) {
                        if ($en == $en_month) {
                            $ar_month = $ar;
                        }
                    }
                    $display_month = $ar_month;
                }
            }


            if ($display_time && $time_detect) {
                if ($is_english || ($is_english == 'fr')) {
                    $time_option = $time_option == 'PM' ? 'PM' : 'AM';
                } else {
                    $time_option = $time_option == 'PM' ? 'م' : 'ص';
                }
            }
            $display_time == true ? ( $time = '  ' . $hour_en . ':' . $minutes_en . ($time_detect ? ' ' . $time_option : '') ) : $time = '';

            if ($display_weekday) {
                return ($display_weekday ? $weekday : ' ') . ' ' . $day_en . ' ' . $display_month . ' ' . $year_en . $time;
            } else {
                return $time;
            }
        }
    }

    public static function sortArrayByCustomField($programs) {
        usort($programs, function($a, $b) {
            $a_time = $a['program_time'];
            $b_time = $b['program_time'];
            return strcmp($a_time, $b_time);
        });
        return $programs;
    }

}
