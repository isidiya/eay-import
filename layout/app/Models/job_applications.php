<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class job_applications extends Model {

    protected $table = "job_applications";
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [ "first_name", "last_name", "telephone", "email", "job_category_id", "job_sector_id", "country_id", "job_experience_years_id", "job_education_id", "job_uni_degree_id", "job_actual_salary_id", "job_salary_id", "availability", "job_offer_id", "applied_on", "cv", "cover_letter", "idCv", "created_by_id", "state", "created_on", "modified_by_id", "modified_on"];

    const cached_minutes = 10;
  
    
    public function offer() {
        return $this->belongsTo('App\Models\job_offers', 'job_offer_id', 'id');
    }
    
    public function sector() {
        return $this->belongsTo('App\Models\job_sectors', 'job_sector_id', 'id');
    }
    
    public function category() {
        return $this->belongsTo('App\Models\job_categories', 'job_category_id', 'id');
    }
    
    public function country() {
        return $this->belongsTo('App\Models\country', 'country_id', 'cms_country_id');
    }
    
    public function degree() {
        return $this->belongsTo('App\Models\job_uni_degree', 'job_uni_degree_id', 'id');
    }
    
    
    public function experience_years() {
        return $this->belongsTo('App\Models\job_experience_years', 'job_experience_years_id', 'id');
    }
    
    
    public function education() {
        return $this->belongsTo('App\Models\job_education', 'job_education_id', 'id');
    }
    
    
    public function actual_salary() {
        return $this->belongsTo('App\Models\job_salaries', 'job_actual_salary_id', 'id');
    }
    
    public function salary() {
        return $this->belongsTo('App\Models\job_salaries', 'job_salary_id', 'id');
    }
    


}
