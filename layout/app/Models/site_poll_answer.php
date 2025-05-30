<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class site_poll_answer extends Model
{
    protected  $table="site_poll_answer";
    protected $primaryKey = 'site_poll_answer_id';
    public    $timestamps = false;
    
    public static function find_np($site_poll_answer_id){
        return self::where('site_poll_answer_id', $site_poll_answer_id)->first();
    }
    public static function find_poll($poll_id){
        return self::where('site_poll_id', $poll_id)->get();
    }

}
