<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class job_salaries extends Model {

    protected $table = "job_salaries";
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = ["salary_range","created_on","created_by_id","modified_on","modified_by_id"];

  

}
