<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class job_education extends Model {

    protected $table = "job_education";
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = ["label","created_on","created_by_id","modified_on","modified_by_id"];

  

}
