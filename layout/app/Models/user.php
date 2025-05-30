<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class user extends Model
{
	protected $table = "users";
    protected $primaryKey = 'user_id';
    public    $timestamps = false;

	public static function find_admin($email){
		return user::where('email', $email)->where('is_admin',1)->first();
    }
}
