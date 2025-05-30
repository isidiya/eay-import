<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ads_display extends Model
{
	protected  $table="ads_display";
    protected $primaryKey = 'id';
    public    $timestamps = false;
    protected $fillable  = ['page_id','section_id','ad_type','ad_code'];
    const cached_minutes = 1440;

	public function section()
    {
        $relation = $this->hasOne('App\Models\section','np_section_id','section_id');
        return $relation;
    }
	public function page()
    {
        $relation = $this->hasOne('App\Models\page','np_page_id','page_id');
        return $relation;
    }

	public static function find_ad($ad_type,$page_id=0,$section_id=0,$not_page_id=0){
        //caches the value for the lifetime of the request so once we set the page_id in the controller we would be able to access it with this service elsewhere
        $ad = Cache::remember("cachead_".$ad_type."_".$page_id."_".$section_id, self::cached_minutes, function ()use ($ad_type,$page_id,$section_id,$not_page_id) {
				$ad_tmp = self::where("ad_type",$ad_type);
				if($page_id > 0){
					$ad_tmp =$ad_tmp->where("page_id",$page_id);
				}
				if($section_id > 0 ){
					$ad_tmp =$ad_tmp->where("section_id",$section_id);
				}
				if($not_page_id > 0 ){
					$ad_tmp =$ad_tmp->where("page_id",'!=',$not_page_id);
				}
				$ad_tmp =$ad_tmp->first();
                return $ad_tmp;
			});
		return isset($ad) ? $ad : new ads_display();
    }
}
