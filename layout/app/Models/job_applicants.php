<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class job_applicants extends Model {

    protected $table = "job_applicants";
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = ['np_article_id', 'full_name', 'mobile', 'gender', 'education', 'nationality', 'residence','cv_path'];

}
