<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class job_advertisers extends Model
{   
    protected  $table="job_advertisers";
    protected $primaryKey = 'id';
    public    $timestamps = false;
    protected $fillable  = ["advertiser_name","description","ville_id","country_id","logo","job_sector_id","anonymous","slug","created_by_id","created_on","modified_by_id","modified_on"];
    const cached_minutes = 10;
     
    public static function find_advertiser($id = 0) {
        static $all = null;
        if (is_null($all)) {
            $all = Cache::remember("model_cache_all_advertisers", self::cached_minutes, function () {
                $id_indexed = [];
                $all_tmp = self::all();
                if ($all_tmp) {
                    foreach ($all_tmp as $item) {
                        $id_indexed[$item->id] = $item;
                    }
                }
                return $id_indexed;
            });
        }
        if ($id != 0) {
            return isset($all[$id]) ? $all[$id] : new job_advertisers();
        }
        return $all;
    }
    
    public function ville() {
        return $this->belongsTo('App\Models\villes', 'ville_id', 'id');
    }
    public function country() {
        return $this->belongsTo('App\Models\country', 'country_id', 'cms_country_id');
    }
    public function sector() {
        return $this->belongsTo('App\Models\job_sectors', 'job_sector_id', 'id');
    }
}
