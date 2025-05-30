<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class user_reads extends Model
{
    protected  $table="user_reads";
    protected $primaryKey = 'ur_id';
	 protected $fillable  = ['user_id','np_article_id','section_id ','sub_section_id','date_created'];
}
