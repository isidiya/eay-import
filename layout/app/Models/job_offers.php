<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class job_offers extends Model {

    protected $table = "job_offers";
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = ["ref_nb", "title", "mission", "entreprise_description", "contract_id", "profile_description", "publish_date", "expiry_date", "status", "ville_id", "anonymous", "job_advertiser_id", "job_sector_id", "job_category_id", "created_by_id", "created_on", "modified_by_id", "modified_on", "job_slug", "candidatures_spontanees","is_deleted"];

    const cached_minutes = 10;
    
    public static function find_offer($id = 0) {
        static $all = null;
        if (is_null($all)) {
            $all = Cache::remember("model_cache_all_offers", self::cached_minutes, function () {
                $id_indexed = [];
                $all_tmp = self::where('is_deleted',0)->get();
                if ($all_tmp) {
                    foreach ($all_tmp as $item) {
                        $id_indexed[$item->id] = $item;
                    }
                }
                return $id_indexed;
            });
        }
        if ($id != 0) {
            return isset($all[$id]) ? $all[$id] : new job_offers();
        }
        return $all;
    }

    public function ville() {
        return $this->belongsTo('App\Models\villes', 'ville_id', 'id');
    }
    
    public function advertiser() {
        return $this->belongsTo('App\Models\job_advertisers', 'job_advertiser_id', 'id');
    }
    
    public function sector() {
        return $this->belongsTo('App\Models\job_sectors', 'job_sector_id', 'id');
    }
    
    public function category() {
        return $this->belongsTo('App\Models\job_categories', 'job_category_id', 'id');
    }
    
    public function contract() {
        return $this->belongsTo('App\Models\job_contracts', 'contract_id', 'id');
    }


}
