<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class quiz_results extends Model
{
	protected  $table="quiz_results";
    protected $primaryKey = 'quiz_result_id';
    public    $timestamps = false;   
    
}
