<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class mgt_showtimes extends Model
{
	protected  $table="mgt_showtimes";
    protected $primaryKey = 'id_showtime';
    public    $timestamps = false; 
    protected $fillable  = ['id_showtime','channel_id','type','time_picker','serie_id'];
    
    public function channel(){
        return $this->belongsTo('App\Models\mgt_channels','channel_id','id');
    } 
    
    
}
