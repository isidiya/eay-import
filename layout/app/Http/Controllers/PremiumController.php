<?php

namespace App\Http\Controllers;

use App\Models\section;
use App\Models\sub_section;
use App\Models\article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\CommonController;
use Layout\Website\Services\ThemeService;
use Layout\Website\Services\PageService;
use Illuminate\Support\Facades\Cookie;

class PremiumController extends Controller
{


	public function premium(Request $request) {

        view()->share('page_class', 'premium');

		if($request->input('action')){
			$action				= $request->input('action');
		}else{
			$action = '';
		}
		if( $request->status && (isset($_COOKIE['subscriber_session_cookie']))){
		    $action = $request->status;
		}
		$subscriber_session = $subscriber = $api_countries = $required_fields = $response  = '';

        if($request->input('cmsArticleId')){
            $cmsArticleId=$request->input('cmsArticleId');
            $cookie_name = "article_redirect_id";
            $cookie_value['cmsArticleId'] = $cmsArticleId; 
            setcookie($cookie_name, json_encode($cookie_value), time()+ 3600, "/"); 
        }else{
            $cmsArticleId='';
        }

		switch($action){
			case "login":
				$login_input		= $request->input('login_input');
				$password			= $request->input('password');
				$redirect_link		= $request->input('redirect_link'); 
				$field_type			= filter_var($login_input, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
				$response = PremiumController::call_subscriptions_api('authenticate', [$field_type => $login_input, 'password'=>$password]);

				if(isset($response->status) && $response->status=="success") {
					$cookie_name = "subscriber_session_cookie";  
                    
                    /* cookie have limit number of caracteres 4k*/
                    $infoToSaveInCookie=json_decode(json_encode($response->subscriber), true);
                    unset($infoToSaveInCookie["subscriptions"]); 
                    /* i remove subscriptions large field from array */
					$cookie_value['subscriber']		= $infoToSaveInCookie;
                    
					$cookie_value['session']		= $response->session;
					setcookie($cookie_name, json_encode($cookie_value), time() + (86400 * 30), "/"); // 86400 = 1 day
					$subscriber_session				= $response->session;
					$subscriber						= $response->subscriber;
                    if(isset($_COOKIE['article_redirect_id']) && !empty($_COOKIE["article_redirect_id"]) ) {
                        $article_redirect_id	= json_decode($_COOKIE['article_redirect_id'],true);
                        $cmsArticleId			= $article_redirect_id['cmsArticleId'];  
                        setcookie("article_redirect_id", null,time() -3600, "/");
                        unset($_COOKIE["article_redirect_id"]);
                        header('location: ' . ThemeService::ConfigValue('SITE_NAME') . '/article/'.$cmsArticleId); 
                    }else{
                        header('location: ' . ThemeService::ConfigValue('SITE_NAME') . $redirect_link);
                    }
					die();
				}
				break;
			case "logout":
				if(isset($_COOKIE['subscriber_session_cookie']) && !empty(json_decode($_COOKIE['subscriber_session_cookie'],true)['session']['session_token'])) {
					$session_token = json_decode($_COOKIE['subscriber_session_cookie'],true)['session']['session_token'];
					$response = PremiumController::call_subscriptions_api('logout', ['session_token' => $session_token]);
					if ($response->status == "success") {
						$cookie_name			= "subscriber_session_cookie";
						setcookie($cookie_name, null,time() -3600, "/");
						$subscriber_session		=$response->session;
					}
					header('location: ' . ThemeService::ConfigValue('SITE_NAME') .  'premium');
					die();
				}
				break;

		}

		if(isset($_COOKIE['subscriber_session_cookie'])){
			$subscriber_session_cookie	= json_decode($_COOKIE['subscriber_session_cookie'],true);
			$subscriber_session			= (object)$subscriber_session_cookie['session'];
			$subscriber					= (object)$subscriber_session_cookie['subscriber'];

			//this will get required fields
			$required_fields			= PremiumController::call_subscriptions_api('update_profile_fields', []);
			//countries
			$api_countries				= PremiumController::call_subscriptions_api('helper/countries', []);
		}

		$response_fb      = PremiumController::call_subscriptions_api(
			'facebook_login',
			array(
				'client_id' => ThemeService::ConfigValue('CLIENT_ID'),
				'client_secret' => ThemeService::ConfigValue('CLIENT_SECRET'),
				'callback_url' => ThemeService::ConfigValue('CALLBACK_URL'))
			);
		$response_twitter = PremiumController::call_subscriptions_api(
			'twitter_login',
			array(
				'client_id' => ThemeService::ConfigValue('TWITTER_CLIENT_ID'),
				'client_secret' => ThemeService::ConfigValue('TWITTER_CLIENT_SECRET'),
				'callback_url' => ThemeService::ConfigValue('TWITTER_CALLBACK_URL'))
			);

        $response_google = PremiumController::call_subscriptions_api(
            'google_login',
            array(
                'client_id' => ThemeService::ConfigValue('CLIENT_ID_GOOGLE'),
                'client_secret' => ThemeService::ConfigValue('CLIENT_SECRET_GOOGLE'),
                'callback_url' => ThemeService::ConfigValue('CALLBACK_URL_GOOGLE'))
            );





		PageService::SetStaticPage('premium');
		return view('theme::pages.premium.premium',[
			'subscriber_session'	=> $subscriber_session,
			'subscriber'			=> $subscriber,
			'response'				=> $response ,
			'api_countries'			=> $api_countries,
			'response_fb'			=> $response_fb,
			'required_fields'		=> $required_fields,
			'response_twitter'		=> $response_twitter,
			'response_google' => $response_google,
            'cmsArticleId' => $cmsArticleId
		]);
	}



    public function FacebookCallback(Request $request){

        if($request->input('code')){
			$fb_login_code	= $request->input('code');
		}else{
			$fb_login_code	= $request->query('code');
		}

        if($request->input('state')){
			$referer	= $request->input('state');
		}else{
			$referer	=  $request->query('state');
		}



		if ( !empty( $fb_login_code ) )
		{
			$authenticate_fb = PremiumController::call_subscriptions_api('facebook_authorize', array('client_id' => ThemeService::ConfigValue("CLIENT_ID"),'client_secret' => ThemeService::ConfigValue("CLIENT_SECRET"),'callback_url' => ThemeService::ConfigValue("CALLBACK_URL"), 'code' => $fb_login_code));

			if ( $authenticate_fb->status == "success" )
			{
				//redirect to /premium

                $cookie_name = "subscriber_session_cookie";
				$cookie_value['subscriber'] = $authenticate_fb->subscriber;
				$cookie_value['session'] = $authenticate_fb->session;
				setcookie($cookie_name, json_encode($cookie_value), time() + (86400 * 30), "/"); // 86400 = 1 day
				$subscriber_session = $authenticate_fb->session;
				$subscriber =  $authenticate_fb->subscriber;
                
                if(isset($_COOKIE['article_redirect_id']) && !empty($_COOKIE["article_redirect_id"]) ) {
                    $article_redirect_id	= json_decode($_COOKIE['article_redirect_id'],true);
                    $cmsArticleId			= $article_redirect_id['cmsArticleId'];  
                    setcookie("article_redirect_id", null,time() -3600, "/");
                    unset($_COOKIE["article_redirect_id"]);
                    header('location: ' . ThemeService::ConfigValue('SITE_NAME') . '/article/'.$cmsArticleId); 
                }else{
                    header('location: ' . ThemeService::ConfigValue('SITE_NAME') .  'premium');
                }

				die();
			}
		}
	}

    public function GoogleCallback(Request $request){

         if($request->input('code')){
			$google_login_code	= $request->input('code');
		}else{
			$google_login_code	= $request->query('code');
		}

		if ( !empty( $google_login_code ) )
		{
			$authenticate_google = PremiumController::call_subscriptions_api('google_authorize', array('client_id' => ThemeService::ConfigValue("CLIENT_ID_GOOGLE"),'client_secret' => ThemeService::ConfigValue("CLIENT_SECRET_GOOGLE"),'callback_url' => ThemeService::ConfigValue("CALLBACK_URL_GOOGLE"), 'code' => $google_login_code));

			if ( $authenticate_google->status == "success" )
			{
				//redirect to /premium

                $cookie_name = "subscriber_session_cookie";
				$cookie_value['subscriber'] = $authenticate_google->subscriber;/*$authenticate_google->subscriber*/
				$cookie_value['session'] = $authenticate_google->session;/*$authenticate_google->session*/
				setcookie($cookie_name, json_encode($cookie_value), time() + (86400 * 30), "/"); // 86400 = 1 day
				$subscriber_session = $authenticate_google->session;
				$subscriber =  $authenticate_google->subscriber;

                if(isset($_COOKIE['article_redirect_id']) && !empty($_COOKIE["article_redirect_id"]) ) {
                    $article_redirect_id	= json_decode($_COOKIE['article_redirect_id'],true);
                    $cmsArticleId			= $article_redirect_id['cmsArticleId'];  
                    setcookie("article_redirect_id", null,time() -3600, "/");
                    unset($_COOKIE["article_redirect_id"]);
                    header('location: ' . ThemeService::ConfigValue('SITE_NAME') . '/article/'.$cmsArticleId); 
                }else{
                    header('location: ' . ThemeService::ConfigValue('SITE_NAME') .  'premium');
                }

				die();
			}
		}
	}


	public function premiumSignup(Request $request) {

        view()->share('page_class', 'premium premiumSignup');

		$postForm			= $request->all();

		if($request->input('action')){
			$action				= $request->input('action');
		}else{
			$action = '';
		}

		$recaptcha_site_key	= ThemeService::ConfigValue('RECAPTCHA_SITE_KEY');

		if ( !empty ($request->query('action')) ){
			$action = $request->query('action');
		}

		switch($action){
		case "signup":
			$parameters = $request->all();
			$parameters['activation_url'] = PremiumController::current_url()."?action=activate&activation_token=";
			$response = PremiumController::call_subscriptions_api('signup', $parameters);
			break;
		case "activate":
			$activation_token = $request->query('activation_token');
			$response = PremiumController::call_subscriptions_api('signup_activate', ['activation_token' => $activation_token]);
			if(isset($response->status) && $response->status=="success"){

				$cookie_name = "subscriber_session_cookie";
				$cookie_value['subscriber'] = $response->subscriber;
				$cookie_value['session'] = $response->session;
				setcookie($cookie_name, json_encode($cookie_value), time() + (86400 * 30), "/"); // 86400 = 1 day
				$subscriber_session = $response->session;
				$subscriber =  $response->subscriber;
                if(isset($_COOKIE['article_redirect_id']) && !empty($_COOKIE["article_redirect_id"]) ) {
                        $article_redirect_id	= json_decode($_COOKIE['article_redirect_id'],true);
                        $cmsArticleId			= $article_redirect_id['cmsArticleId'];  
                        setcookie("article_redirect_id", null,time() -3600, "/");
                        unset($_COOKIE["article_redirect_id"]);
                        header('location: ' . ThemeService::ConfigValue('SITE_NAME') . '/article/'.$cmsArticleId); 
                }else{
                    header('location: ' . ThemeService::ConfigValue('SITE_NAME') . 'premium');
                }
				die();
			}else{
				header('location: ' . ThemeService::ConfigValue('SITE_NAME') .  'premium-signup');
				die();
			}
			break;
		default:
			$response = false;
			break;
		}

		$response_fields = PremiumController::call_subscriptions_api('signup_fields', []);

//		$response_fb      = PremiumController::call_subscriptions_api(
//			'facebook_login',
//			array(
//				'client_id' => ThemeService::ConfigValue('CLIENT_ID'),
//				'client_secret' => ThemeService::ConfigValue('CLIENT_SECRET'),
//				'callback_url' => ThemeService::ConfigValue('CALLBACK_URL'))
//			);
//		$response_twitter = PremiumController::call_subscriptions_api(
//			'twitter_login',
//			array(
//				'client_id' => ThemeService::ConfigValue('TWITTER_CLIENT_ID'),
//				'client_secret' => ThemeService::ConfigValue('TWITTER_CLIENT_SECRET'),
//				'callback_url' => ThemeService::ConfigValue('TWITTER_CALLBACK_URL'))
//			);
//        $response_google = PremiumController::call_subscriptions_api(
//            'google_login',
//            array(
//                'client_id' => ThemeService::ConfigValue('CLIENT_ID_GOOGLE'),
//                'client_secret' => ThemeService::ConfigValue('CLIENT_SECRET_GOOGLE'),
//                'callback_url' => ThemeService::ConfigValue('CALLBACK_URL_GOOGLE'))
//            );

		PageService::SetStaticPage('premium-signup');

         if($request->input('cmsArticleId')){
            $cmsArticleId=$request->input('cmsArticleId');
        }else{
            $cmsArticleId='';
        }

		return view('theme::pages.premium.premium-signup',[
			'response_fields'		=>$response_fields,
			'response_action'		=>$response,
			'recaptcha_site_key'	=>$recaptcha_site_key,
			'postForm'				=>$postForm,
			'cmsArticleId'				=>$cmsArticleId
//			'response_fb'			=> $response_fb,
//			'response_twitter'		=> $response_twitter,
//			'response_google' => $response_google
			]);
	}



	public function forgotPasswordPremium(Request $request) {
        view()->share('page_class', 'premium forgotPassword');

		$action	= $request->input('action');
		$response = '';
		if ( $action == "forgot" )
		{
			$captcha = $request->input('recaptcha_response');

			if (!CommonController::verifyRecaptcha($captcha)) {
				$response = array(
					'status' => 'error',
					'message' => 'Invalid re-captcha. Please try again.',
					'message_ar' => 'رمزالمرور غير صالح. حاول مرة اخرى.'
				);

				return $response;
			}

			$email    = $request->input('email');
			$response = PremiumController::call_subscriptions_api(
				'forgot_password',[
					'email' => $email ,
					'reset_password_url' => ThemeService::ConfigValue('APP_URL')."reset-password-premium"
				]);
		}
		PageService::SetStaticPage('forgot-password-premium');
		return view('theme::pages.premium.forgot-password-premium',[
			'response'		=>$response,
			]);
	}
	public function resetPasswordPremium(Request $request) {
        view()->share('page_class', 'premium resetPassword');

		$action	= $request->input('action');

		$response = '';
		if ( $action == "reset" )
		{
			$captcha = $request->input('recaptcha_response');
			if (!CommonController::verifyRecaptcha($captcha)) {
				$response = array(
					'status' => 'error',
					'message' => 'Invalid re-captcha. Please try again.',
					'message_ar' => 'رمزالمرور غير صالح. حاول مرة اخرى.'
				);

				return $response;
			}

			$email					= $request->input('email');
			$password				= $request->input('password');
			$confirm_password		= $request->input('confirm_password');

			if( isset($request->tokenId)){
				$tokenId = $request->tokenId;
			}

			$response = PremiumController::call_subscriptions_api(
				'reset_password', [
                'email' => $email ,
                'token' => $tokenId,
                'password' => $password,
                'password_confirmation' => $confirm_password]
            );
		}
		PageService::SetStaticPage('reset-password-premium');
		return view('theme::pages.premium.reset-password-premium',[
			'response'		=>$response,
			]);
	}

	public static function premiumChangePassword(Request $request) {

        view()->share('page_class', 'premium changePassword');

		$action	= $request->input('action');
		$response = '';

		if ($action == "change-password")
		{
			$subscriber_session_cookie = json_decode($_COOKIE['subscriber_session_cookie'],true);
			$session_token             = $subscriber_session_cookie['session']['session_token'];

			$params = $request->all();
			$params['session_token'] = $session_token;

			$response = PremiumController::call_subscriptions_api('change_password', $params);
		}
		PageService::SetStaticPage('premium-change-password');
		return view('theme::pages.premium.premium-change-password',[
			'response'		=>$response,
			]);
	}

	public function premiumUpdateProfile(Request $request){
		$response					= array();
		$response['message']		= '';
		$subscriber_session_cookie	= json_decode($_COOKIE['subscriber_session_cookie'],true);
		$original_session			= $subscriber_session_cookie['session'];
		$session_token				= $subscriber_session_cookie['session']['session_token'];

		$params = [];
		$params = $request->all();


                if(!isset($params['email'])){
                    $params['email'] = json_decode($_COOKIE['subscriber_session_cookie'])->subscriber->email ;
                }

                if(isset($params['birthdate'])){
                    $params['birthdate'] = date("d-m-Y", strtotime($params['birthdate']));
                    $params['birthdate']=str_replace("-",".",$params['birthdate']);
                }


		$params['session_token'] = $session_token;

               $converted_parameters = [];
                foreach($params as $key=>$value){
                    if(is_array($value)){
                        foreach($value as $k=>$v){
                            $converted_parameters[$key."[".$k."]"] = $v;
                        }
                    }else{
                        $converted_parameters[$key] = $value;
                    }
                }


		$update_profile  = PremiumController::call_subscriptions_api("update_profile", $converted_parameters);
		if ( $update_profile->status == "success" )
		{
			//unset cookie
			$cookie_name = "subscriber_session_cookie";
			setcookie($cookie_name, null,time() -3600, "/");
			unset($_COOKIE[$cookie_name]);
			//set it again with new values
			$cookie_name = "subscriber_session_cookie";
			$cookie_value['subscriber'] = $update_profile->subscriber;
			$cookie_value['session'] = $original_session;
			setcookie($cookie_name, json_encode($cookie_value), time() + (86400 * 30), "/"); // 86400 = 1 day
			$response['is_error']     = 0;
			$response['message']      = 'تم تحديث الملف الشخصي بنجاح';
			$response['redirect_url'] = ThemeService::ConfigValue('SITE_NAME') . 'premium';

		}
		else
		{
			$response['is_error']     = 1;
			$response['message']      = 'هناك خطأ ما ، يرجى المحاولة مرة أخرى لاحقًا';
			$response['redirect_url'] = ThemeService::ConfigValue('SITE_NAME') . 'premium';
		}

		return $response;
	}

	public static function call_subscriptions_api($endpoint, $parameters = []) {
		$parameters['website_token'] = ThemeService::ConfigValue("SUBSCRIPTIONS_API_TOKEN");
		if(isset($_SERVER['HTTP_X_SUCURI_CLIENTIP']))
		{
		    $_SERVER["REMOTE_ADDR"] = $_SERVER['HTTP_X_SUCURI_CLIENTIP'];
		}
		$parameters['subscriber_ip'] =  $_SERVER["REMOTE_ADDR"];
		// Get cURL resource
		$curl = curl_init();
		// Set some options - we are passing in a useragent too here
		curl_setopt_array($curl, array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => ThemeService::ConfigValue("SUBSCRIPTIONS_API_URL")."/".$endpoint,
			CURLOPT_USERAGENT => 'Sample Website API Request',
			CURLOPT_POST => 1,
			CURLOPT_SSL_VERIFYHOST => 0,            // don't verify ssl
			CURLOPT_SSL_VERIFYPEER => false,        //
			CURLOPT_POSTFIELDS => $parameters
		));

		// Send the request & save response to $resp
		$resp = curl_exec($curl);
//		echo "<pre>";
//		echo "\nURL:\n";
//                echo ThemeService::ConfigValue("SUBSCRIPTIONS_API_URL");
//                echo "\nURL:\n";
//		echo ThemeService::ConfigValue("SUBSCRIPTIONS_API_URL")."/".$endpoint;
//		echo "\nRequest:\n";
//		print_r($parameters);
//		echo "\nResponse:\n";
//		echo $resp;
//		echo "</pre>";
		// Close request to clear up some resources
		curl_close($curl);
		$resp = ltrim($resp,'+');

		return json_decode($resp);
	}

	public static function current_url() {
		$domain = $_SERVER['HTTP_HOST'];
		$script = $_SERVER['REQUEST_URI'];
		$protocol = strpos(strtolower($_SERVER['SERVER_PROTOCOL']), 'https') === FALSE ? 'http' : 'https';
		$FinalUrl = $protocol . '://' . $domain . $script;
		return $FinalUrl;
	}
//
//        public static function previewFullBody($articleBody, $new_article=true){
////        if ($new_article)
////        {
////                $articleBody = explode('<br><br>', $articleBody);
////        }
////        else
////        {
//                $articleBody = str_replace(array("\n\r","\r\n","\n"),array("","",""),$articleBody);
//                $articleBody = explode('<br /><br />', $articleBody);
////        }
//
//        return $articleBody[0];
//	}
//
//        public static function preventFullBody($articleBody, $new_article=true){
//
//            $response = self::call_subscriptions_api('info');
//
//            // only show full content for reg users
//            $articleBody = CommonController::limitArticleBodyByWord($articleBody, $response->website->config->word_limit->value);
//                    return $articleBody;
//            }


}