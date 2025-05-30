<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class pdf extends Model
{
	protected  $table="pdf";
    protected $primaryKey = 'pdf_id';
    public    $timestamps = false;
    protected $fillable  = ['pdf_name','publication_name','issue_date','issue_number','preview_image','upload_time','uploaded_by','uploader_ip','pdf_size','pdf_type','paid_issue'];
}
