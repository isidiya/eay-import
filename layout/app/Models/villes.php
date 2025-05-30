<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class villes extends Model
{   
    protected  $table="villes";
    protected $primaryKey = 'id';
    public    $timestamps = false;
    protected $fillable  = ["nom_fr","nom_ar"];
    const cached_minutes = 10;

    public static function find_ville($id = 0) {
        static $all = null;
        if (is_null($all)) {
            $all = Cache::remember("model_cache_all_villes", self::cached_minutes, function () {
                $id_indexed = [];
                $all_tmp = self::orderBy('ville_order', 'asc')->get();
                if ($all_tmp) {
                    foreach ($all_tmp as $item) {
                        $id_indexed[$item->id] = $item;
                    }
                }
                return $id_indexed;
            });
        }
        if ($id != 0) {
            return isset($all[$id]) ? $all[$id] : new villes();
        }
        return $all;
    }
}
