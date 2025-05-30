<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class pharmacie_garde extends Model
{   
    protected  $table="pharmacie_garde";
    protected $primaryKey = 'id';
    public    $timestamps = false;
    protected $fillable  = ["pharmacie_id","quartier_id", "jour", "nuit", "date_garde", "date_fin_garde", "user_id","date_creation", "date_modif"];
    const cached_minutes = 1440;

    public function pharmacie(){
        return $this->belongsTo('App\Models\pharmacies','pharmacie_id','id');
    }
    public function user(){
        return $this->belongsTo('App\Models\user','user_id','user_id');
    }
}
