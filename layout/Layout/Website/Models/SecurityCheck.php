<?php
namespace Layout\Website\Models;

use Layout\Website\Models\WebsiteWidget;
use Illuminate\Support\Facades\Session;
use Layout\Website\Services\ThemeService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

class SecurityCheck extends WebsiteWidget
{
	public function __construct()
    {
		if(isset($_COOKIE['user'])){
			$user = $_COOKIE['user'];
		}

		$request = Request();

		if(null !== $request->query("token")){
			$token = $request->query("token");
			$user = \App\Models\user::where('single_signin_token',$token)->first();
			if(isset($user)){
				$user->single_signin_token ="";
				$user->save();
			}else{
				echo view('theme::errors.401');
				exit;
			}
			if ($user ) {
					$user = json_encode($user);
					//encrypt
					$user = Crypt::encrypt($user);
					setcookie('user', $user);
					setcookie('from_iframe', "true");
			} else {
				echo view('theme::errors.401');
				exit;
			}
		}



		if(empty($user)){
            echo view('theme::errors.401');
            exit;
		}
	}

}