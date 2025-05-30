<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class related_articles extends Model
{
	protected $table="related_articles";
    protected $primaryKey = 'article_id';
    public    $timestamps = false;
	protected $fillable  = ['article_id','related_ids'];
}
