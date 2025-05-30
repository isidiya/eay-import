<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class user_articles_track extends Model
{
    protected  $table="user_articles_track";
    protected $primaryKey = 'id';
	 protected $fillable  = ['user_id','cms_article_id','action ','action_date','created_at'];
}
