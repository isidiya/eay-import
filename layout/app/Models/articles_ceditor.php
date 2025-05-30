<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class articles_ceditor extends Model
{ 
    protected $table = "articles_ceditor";
    protected $primaryKey = 'cms_article_id ';
    public    $timestamps = false;
    const cached_minutes = 10;
    
    public static function find_np($np_article_id){ 
        return self::where('np_article_id', $np_article_id)->first();
    }
    
    
}
