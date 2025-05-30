<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class article_multi_section_archive extends Model
{
    protected $table = 'article_multi_section_archive';
    protected $primaryKey = 'ams_id';
    public    $timestamps = false;    //

    public function article()
    {
        return $this->belongsTo('App\Models\article_archive','ams_article_id','cms_article_id');
    }
	public function section()
    {
        return $this->belongsTo('App\Models\section','ams_section_id','cms_section_id');
    }

}
