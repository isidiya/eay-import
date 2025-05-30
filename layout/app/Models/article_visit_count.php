<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class article_visit_count extends Model
{
    protected $table = "article_visit_count";
    protected $primaryKey = 'avc_id';
    public    $timestamps = false;    //
    protected $fillable = ['np_article_id','visit_count', 'comment_count','visit_date','max_publish_time'];
}
