<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class highschool_result_types extends Model {

    protected $table = "highschool_result_types";
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = ['type_name'];

}
