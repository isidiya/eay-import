<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class subscriber extends Model
{
    protected $table = 'subscribers';
    protected $primaryKey = 'subscriber_id';
    public    $timestamps = false;
    protected $fillable = ['user_uuid','email','provider', 'provider_id', 'email_verified_at', 'first_name', 'last_name', 'birthday', 'access_token' , 'refresh_token', 'user_details'];

    public static function find_uuid($uuid) {
        return self::where('user_uuid', $uuid)->first();
    }

    public function getDisplayNameAttribute() {
        $d_name = '';
        if(isset($this->first_name)){
            $d_name .= $this->first_name;
        }
        if(isset($this->last_name)){
            $d_name .= " " . $this->last_name;
        }
        return $d_name;
    }
    public function user_details() {
        if(isset($this->user_details)){
            return json_decode($this->user_details, true);
        }
        return '';
    }
}