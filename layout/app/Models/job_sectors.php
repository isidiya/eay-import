<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class job_sectors extends Model
{   
    protected  $table="job_sectors";
    protected $primaryKey = 'id';
    public    $timestamps = false;
    protected $fillable  = ["sector_name","slug","created_by_id","created_on","modified_by_id","modified_on"];
    const cached_minutes = 10;
     
    public static function find_sector($id = 0) {
        static $all = null;
        if (is_null($all)) {
            $all = Cache::remember("model_cache_all_sectors", self::cached_minutes, function () {
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
            return isset($all[$id]) ? $all[$id] : new job_sectors();
        }
        return $all;
    }
}
