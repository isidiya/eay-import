<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Layout\Website\Helpers\UrlHelper;

class quartiers extends Model
{   
    protected  $table="quartiers";
    protected $primaryKey = 'id';
    public    $timestamps = false;
    protected $fillable  = ["nom_fr","nom_ar"];
    
    const cached_minutes = 10;

     public static function find_by_url($quartier_url) {
        //caches the value for the lifetime of the request so once we set the page_id in the controller we would be able to access it with this service elsewhere
        static $all = null;
        if (is_null($all)) {
            $all = Cache::remember("model_cache_all_quartiers_url", self::cached_minutes, function () {
                        $np_indexed = [];
                        $all_tmp = self::all();
                        if ($all_tmp) {
                            foreach ($all_tmp as $item) {
                                $np_indexed[UrlHelper::clean_url($item->nom_fr)] = $item;
                            }
                        }
                        return $np_indexed;
                    });
        }
        return isset($all[UrlHelper::clean_url($quartier_url)]) ? $all[UrlHelper::clean_url($quartier_url)] : new quartiers();
    }
    
    
}
