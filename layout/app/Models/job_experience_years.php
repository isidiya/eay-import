<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class job_experience_years extends Model {

    protected $table = "job_experience_years";
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = ["years","created_on","created_by_id","modified_on","modified_by_id"];

  

}
