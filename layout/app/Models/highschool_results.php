<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class highschool_results extends Model
{
	protected  $table="highschool_results";
    protected $primaryKey = 'id';
    public    $timestamps = false;
    protected $fillable  = ['seat_number','student_name','civil_id','result','school_name','division','errors','uploaded_by','uploader_ip','highschool_result_type_id','academic_year','country','university','major'];
}
