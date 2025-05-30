<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class site_poll extends Model
{
    protected  $table="site_poll";
    protected $primaryKey = 'site_poll_id';
    public    $timestamps = false;

    public static function find_np($np_id){
        return self::where('np_poll_id', $np_id)->orderBy('np_poll_id','desc')->first();
    }

    public function site_poll_answer()
    {
        return $this->hasMany('App\Models\site_poll_answer','site_poll_id','np_poll_id');
    }
    
    public static function find_article($np_article_id){
        return self::where('np_article_id', $np_article_id);
    }
    
    public static function article_contain_poll($np_article_id){
        $count_poll=self::where('np_article_id', $np_article_id)->count();
        if($count_poll>0){
            return true;
        }
        return false;
    }

}
