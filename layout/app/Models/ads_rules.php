<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ads_rules extends Model {

    protected $table = "ads_rules";
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = ['amp_article', 'url', 'url_segments_count', 'url_formula', 'ads_zone_id', 'script_web', 'header_script_web', 'active_script_web', 'web_iframe', 'script_mobile', 'header_script_mobile', 'active_script_mobile', 'mobile_iframe', 'iframe_width', 'iframe_height'];

    public function zone() {
        $relation = $this->hasOne('App\Models\ads_zones', 'id', 'ads_zone_id');
        return $relation;
    }

    public static function get_zone_rules($zone_id, $amp_article) {
        $all_rules = array(
            'is_equal_to' => self::where('url_formula', 'is_equal_to')->where('amp_article', $amp_article)->where('ads_zone_id', $zone_id)->orderBy('url_segments_count', 'desc')->get(),
            'starting_with' => self::where('url_formula', 'starting_with')->where('amp_article', $amp_article)->where('ads_zone_id', $zone_id)->orderBy('url_segments_count', 'desc')->get(),
            'including' => self::where('url_formula', 'including')->where('amp_article', $amp_article)->where('ads_zone_id', $zone_id)->orderBy('url_segments_count', 'desc')->get(),
            'general_rule' => self::where('url_formula', 'general_rule')->where('amp_article', $amp_article)->where('ads_zone_id', $zone_id)->first()
        );
        return isset($all_rules) ? $all_rules : new ads_rules();
    }

    public static function get_ad($url, $zone_id, $is_web = 1, $amp_article) {
        $all_rules = self::get_zone_rules($zone_id, $amp_article);
        $device_active_field = ($is_web == 1) ? 'active_script_web' : 'active_script_mobile';
        $device_script_field = ($is_web == 1) ? 'script_web' : 'script_mobile';
        $device_header_script_field = ($is_web == 1) ? 'header_script_web' : 'header_script_mobile';

        foreach ($all_rules['is_equal_to'] as $equal_to_rule) {
            if (($url == $equal_to_rule->url) && ($equal_to_rule->$device_active_field == 1)) {
                $ad_tmp = array(
                    'body_script' => stripslashes($equal_to_rule->$device_script_field),
                    'header_script' => $equal_to_rule->$device_header_script_field
                );
                return $ad_tmp;
            }
        }
        foreach ($all_rules['starting_with'] as $starting_with_rule) {
            if ((strpos($url, $starting_with_rule->url) === 0) && ($starting_with_rule->$device_active_field == 1)) {
                $ad_tmp = array(
                    'body_script' => stripslashes($starting_with_rule->$device_script_field),
                    'header_script' => $starting_with_rule->$device_header_script_field
                );
                return $ad_tmp;
            }
        }
        foreach ($all_rules['including'] as $including_rule) {
            if ((strpos($url, $including_rule->url) !== false) && ($including_rule->$device_active_field == 1)) {
                $ad_tmp = array(
                    'body_script' => stripslashes($including_rule->$device_script_field),
                    'header_script' => $including_rule->$device_header_script_field
                );
                return $ad_tmp;
            }
        }
        if ($all_rules['general_rule'] && ($all_rules['general_rule']->$device_active_field == 1)) {
            $general_rule = $all_rules['general_rule'];
            $ad_tmp = array(
                'body_script' => stripslashes($general_rule->$device_script_field),
                'header_script' => $general_rule->$device_header_script_field
            );
            return $ad_tmp;
        } else {
            $ad_tmp = array(
                'body_script' => '',
                'header_script' => ''
            );
            return $ad_tmp;
        }
    }

}
