<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class site_poll_vote extends Model
{
    protected  $table="site_poll_vote";
    protected $primaryKey = 'site_poll_vote_id';
    public    $timestamps = false;
    
    public static function find_np($site_poll_vote_id){
        return self::where('site_poll_vote_id', $site_poll_vote_id)->first();
    }

    public static function find_by_ip_pollid($poll_id, $ip){
        return self::where('site_poll_id', $poll_id)->where('site_poll_vote_ip', '=', $ip)->first();
        
    }
    
    public static function find_by_pollid($poll_id){
        return self::where('site_poll_id', $poll_id)->get();
        
    }
    
    public function site_poll_answer(){ 
        $relation = $this->hasMany('App\Models\section','site_poll_answer_id','site_poll_answer_id'); 
        return $relation;
    }
    
}
