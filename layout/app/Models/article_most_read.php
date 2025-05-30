<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class article_most_read extends Model
{
    protected  $table="article_most_read";
    protected $primaryKey = 'amr_id';
    public    $timestamps = false;
    protected $fillable  = ['np_article_id','page_views','sessions','from_date','np_section_id'];
}
