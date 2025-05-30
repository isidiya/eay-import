<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class job_uni_degree extends Model {

    protected $table = "job_uni_degree";
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = ["degree","created_on","created_by_id","modified_on","modified_by_id"];

  

}
