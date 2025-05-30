<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use DB;

class pharmacies extends Model {

    protected $table = "pharmacies";
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = ["nom_pharmacie_fr", "nom_pharmacie_ar", "adresse_fr", "adresse_ar", "quartier_id", "telephone", "ville_id", "longitude", "latitude", "publier", "slug_phar", "path_photo", "datecreation", "user_id", "modif_par", "date_modif", "is_deleted"];

    const cached_minutes = 1440;

    public function ville() {
        return $this->belongsTo('App\Models\villes', 'ville_id', 'id');
    }

    public function quartier() {
        return $this->belongsTo('App\Models\quartiers', 'quartier_id', 'id');
    }

    public function user() {
        return $this->belongsTo('App\Models\user', 'user_id', 'user_id');
    }

    public function editor() {
        return $this->belongsTo('App\Models\user', 'modif_par', 'user_id');
    }
   
    public static function find_by_id($id){
         return self::where('id', $id)->first();
    }
    public static function find_by_slug($slug){
        //caches the value for the lifetime of the request so once we set the page_id in the controller we would be able to access it with this service elsewhere
        static $all = null;
        if(is_null($all)){
            $all = Cache::remember("model_cache_all_pharmacies", self::cached_minutes, function () {
                $np_indexed = [];
                $all_tmp = self::all();
                if ($all_tmp) {
                    foreach ($all_tmp as $item) {
                        $gardes = pharmacie_garde::where('pharmacie_id', $item->id)->where('date_garde','<=', DB::raw('NOW()'))->where('date_fin_garde','>=', DB::raw('NOW()'))->get();
                        $item->gardes = $gardes;
                        $np_indexed[$item->slug_phar] = $item;
                    }
                }
                return $np_indexed;
            });
        }
        if($slug != ''){
            return isset($all[$slug]) ? $all[$slug] : new pharmacies();
        }
        return $all;
    }
    
}
