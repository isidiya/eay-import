<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ads_header_display extends Model
{
	protected  $table="ads_header_display";
    protected $primaryKey = 'id';
    public    $timestamps = false;
    protected $fillable  = ['page_id','section_id','header_code'];

	public function section()
    {
        $relation = $this->hasOne('App\Models\section','np_section_id','section_id');
        return $relation;
    }
	public function page()
    {
        $relation = $this->hasOne('App\Models\page','np_page_id','page_id');
        return $relation;
    }
}