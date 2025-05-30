<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class article_author extends Model
{
    protected $primaryKey = 'article_author_id';
    public    $timestamps = false;

    public static function find_np($np_id){
        return self::where('np_article_id', $np_id)->first();
    }
    public static function find_np_all($np_id){
        return self::where('np_article_id', $np_id)->get();
    }
}
