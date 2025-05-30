<?php

namespace App\Http\Controllers;

use App\Models\pdf;
use App\Models\user;
use App\Mail\SendEmail;
use App\Models\ads_display;
use App\Models\ads_zones;
use App\Models\ads_rules;
use Illuminate\Http\Request;
use App\Models\ads_header_display;
use App\Models\enviroment_variables;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Layout\Website\Services\ThemeService;
use App\Http\Controllers\CommonController;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Schema;
use App\Models\sponsored_links;
use Illuminate\Support\Str;

class UserController extends Controller {

    public function doLogin(Request $request) {

        $email = $request->input('email');
        $client_name = $request->input('client_name');
        $user = user::find_admin($email);
        $captcha = $request->input('g-recaptcha-response');

		if(!isset($client_name)){
			 // Check if recaptcha exists
			if (!CommonController::verifyRecaptcha($captcha) || empty($captcha)) {
				return redirect('/login-mgt?error=2');
			}
		}

        if ($user && Hash::check($request->input('password'), $user->password)) {
            if(null !== $request->input('tfa_code')){
                $tfa_code = $request->input('tfa_code');
                require_once  base_path().'/Layout/TFA/autoload.php';
                $google2fa = new \PragmaRX\Google2FA\Google2FA();
                if ($google2fa->verifyKey($user->reset_code, $tfa_code)) {
                   $user = json_encode($user);
                    //encrypt
                    $user = Crypt::encrypt($user);

                    setcookie('user', $user);

					if(isset($client_name)){
						setcookie('from_iframe', $client_name);
						return "Authorization Done";
						exit;
					}

                    if (ThemeService::ConfigValue("LOGIN_REDIRECT")) {
                        return redirect(ThemeService::ConfigValue("LOGIN_REDIRECT"));
                    }
                    return redirect('/management');
                } else {
                  return redirect('/login-mgt?error=2');
                }
            }


            //encrypt


			if(isset($client_name)){
				$token = Str::random(32);
				$user->single_signin_token = $token;
				$user->save();
				return $token;
			}
			$user = json_encode($user);
			$user = Crypt::encrypt($user);
            setcookie('user', $user); 
            
            if (ThemeService::ConfigValue("LOGIN_REDIRECT")) {
                return redirect(ThemeService::ConfigValue("LOGIN_REDIRECT"));
            }
            return redirect('/management');
        } else {
            return redirect('/login-mgt?error=1');
        }
    }

    public function deleteUser(Request $request) {

    }

    public function saveEnv(Request $request) {
        $file = $request->file('filePath');
        $env_value = $request->input("env_value");
        $start_date = date('Y-m-d H:i:s', strtotime($request->input("start_date")));
        $end_date = date('Y-m-d H:i:s', strtotime($request->input("end_date")));

        if(!is_null($file)){
            $mimeType = $file->getMimeType();
            $extension = $file->getClientOriginalExtension();
            $allowed_mime_types = array('image/jpeg', 'image/jpg', 'image/png', 'image/svg+xml');
            if (in_array($mimeType, $allowed_mime_types)) {
                $upload_folder_path = 'uploads/logo/';
                $time_now = date('d-m-Y-His');
                $file_name = $time_now . '.' . $extension;
                $input_path_name = url('/') . '/' . $upload_folder_path . $file_name;
                Storage::putFileAs($upload_folder_path, $file, $file_name);
                $env_value = $input_path_name;
            }
        }
        if(Schema::hasColumn('enviroment_variables', 'start_date') && Schema::hasColumn('enviroment_variables', 'end_date')){
             $ad = enviroment_variables::updateOrCreate(
                        ['id' => $request->input("env_id")], ['env_variable' => str_replace(" ", "_", strtoupper($request->input("env_variable"))),
                    'env_description' => $request->input("env_description"),
                    'env_value' => $env_value,
                    'start_date' => $start_date,
                            'end_date' => $end_date]);
        }else{
             $ad = enviroment_variables::updateOrCreate(
                        ['id' => $request->input("env_id")], ['env_variable' => str_replace(" ", "_", strtoupper($request->input("env_variable"))),
                    'env_description' => $request->input("env_description"),
                    'env_value' => $env_value]);
        }

        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        return redirect('/management/manage-envs');
    }

    public function deleteEnv(Request $request) {
        try {
            if (is_numeric($request->env_id) && $request->env_id > 0) {
                enviroment_variables::where('id', $request->env_id)->delete();
            }
        } finally {
            \Illuminate\Support\Facades\Artisan::call('cache:clear');
            return redirect('/management/manage-envs');
        }
    }
    public function saveSponsoredLink(Request $request) {
        $thumbnail = $request->file('thumbnail');
        $thumbnail_name = NULL;
        if(!is_null($thumbnail)){
            $mimeType = $thumbnail->getMimeType();
            $extension = $thumbnail->getClientOriginalExtension();
            $allowed_mime_types = array('image/jpeg', 'image/jpg', 'image/png', 'image/svg+xml');
            if (in_array($mimeType, $allowed_mime_types)) {
                $upload_folder_path = 'uploads/images/';
                $time_now = date('d-m-Y-His');
                $file_name = $time_now . '.' . $extension;
                $thumbnail_name = url('/') . '/' . $upload_folder_path . $file_name;
                Storage::putFileAs($upload_folder_path, $thumbnail, $file_name);
            }
        }

        $link = sponsored_links::updateOrCreate(
                        ['id' => $request->input("link_id")], ['link' => $request->input("link"),
                    'title' => $request->input("title"),
                    'thumbnail' => $thumbnail_name,
                    'advertiser' => $request->input("advertiser")]);

        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        return redirect('/management/manage-sponsored-links');
    }

    public function deleteSponsoredLink(Request $request) {
        try {
            if (is_numeric($request->link_id) && $request->link_id > 0) {
                sponsored_links::where('id', $request->link_id)->delete();
            }
        } finally {
            \Illuminate\Support\Facades\Artisan::call('cache:clear');
            return redirect('/management/manage-sponsored-links');
        }
    }

    public function savePdf(Request $request) {
        $file = $request->file('filePath');
        $mimeType = $file->getMimeType();
        if (!empty($request->input("issue_date")) && $mimeType == 'application/pdf') {
            $upload_folder_path = 'pdf/';
            if (ThemeService::ConfigValue("PDF_UPLOADS_PATH")) {
                $upload_folder_path = 'uploads/pdf/';
            }
            $issue_date = $request->input("issue_date");
            $publication_name = $request->input("ddl_type");

//            check if there is a file uploaded in the same date and for the same publication: so append each time a count number
//            to the pdf and preview image name in order not to override the previously uploaded files
            if (is_array(ThemeService::ConfigValue('PDF_TYPES'))) {
                foreach (ThemeService::ConfigValue('PDF_TYPES') as $key => $type) {
                    if ($key == $publication_name) {
                        $publication_name_type = $type;
                    }
                }
            $pdf_same_date_count = pdf::where('issue_date',$issue_date)->where('publication_name', $publication_name_type)->count();
            }else{
            $pdf_same_date_count = pdf::where('issue_date',$issue_date)->where('publication_name', $publication_name)->count();
            }

            $file_name = $publication_name . "-" . str_replace("-", "", $issue_date)."-". ($pdf_same_date_count + 1) . ".pdf";

            $pdf_path = str_replace("-", "/", $issue_date);
            $pdf_name = $upload_folder_path . $pdf_path . "/" . $file_name;
            $path = Storage::putFileAs($upload_folder_path . $pdf_path, $file, $file_name);

            $file_preview = $request->file('filePathPreview');
            if (isset($file_preview)) {
                $file_name_preview = $publication_name . "-" . str_replace("-", "", $issue_date)."-". ($pdf_same_date_count + 1) . ".jpg";
                $preview_image = $upload_folder_path . $pdf_path . "/" . $file_name_preview;
                $path_preview = Storage::putFileAs($upload_folder_path . $pdf_path, $file_preview, $file_name_preview);
            } elseif (isset($file)) {
                $preview_image = CommonController::createPdfPreview($path);
            } else {
                $preview_image = "";
            }

            $timestamp = date('His', strtotime(now()));
            //get user
            $user = $_COOKIE['user'];
            $user = Crypt::decrypt($user);
            $user = json_decode($user);

            //set pdf
            if (is_array(ThemeService::ConfigValue('PDF_TYPES'))) {
                foreach (ThemeService::ConfigValue('PDF_TYPES') as $key => $type) {
                    if ($key == $publication_name) {
                        $publication_name = $type;
                    }
                }
            }
            $pdf = pdf::updateOrCreate(
                            ['pdf_id' => $request->input("pdf_id")], ['pdf_name' => $pdf_name . '?ts=' . $timestamp,
                        'publication_name' => $publication_name,
                        'issue_date' => $issue_date,
                        'issue_number' => 0,
                        'preview_image' => $preview_image . '?ts=' . $timestamp,
                        'upload_time' => date('Y-m-d H:i:s'),
                        'uploaded_by' => $user->user_id,
                        'uploader_ip' => $request->server('REMOTE_ADDR'),
                        'pdf_size' => 0,
                        'pdf_type' => 0,
                        'paid_issue' => 0]);

            return redirect('/management/manage-pdfs');
        }
    }
      public function savePdfOrExternalLink(Request $request) {
        $mimeType="";
        $file = $request->file('filePath');
        $externalLink = $request->input('externalLink');
        $issue_number = $request->input('issue_number') ? $request->input('issue_number') : 0 ;

        $file_preview = $request->file('filePathPreview');

        if(isset($file)){
        $mimeType = $file->getMimeType();
        }
        if (!empty($request->input("issue_date")) && ( (!is_null($file) && ($mimeType == 'application/pdf')) || !is_null($file_preview) || isset($externalLink) ) ) {
            $upload_folder_path = 'pdf/';
            if (ThemeService::ConfigValue("PDF_UPLOADS_PATH")) {
                $upload_folder_path = 'uploads/pdf/';
            }
            $issue_date = $request->input("issue_date");
            $publication_name = $request->input("ddl_type");

            //  check if there is a file uploaded in the same date and for the same publication: so append each time a count number
            //  to the pdf and preview image name in order not to override the previously uploaded files
            if (is_array(ThemeService::ConfigValue('PDF_TYPES'))) {
                foreach (ThemeService::ConfigValue('PDF_TYPES') as $key => $type) {
                    if ($key == $publication_name) {
                        $publication_name_type = $type;
                    }
                }
            $pdf_same_date_count = pdf::where('issue_date',$issue_date)->where('publication_name', $publication_name_type)->count();
            }else{
            $pdf_same_date_count = pdf::where('issue_date',$issue_date)->where('publication_name', $publication_name)->count();
            }
            $pdf_path = str_replace("-", "/", $issue_date);

            if(isset($externalLink) && !empty($externalLink)){
                $pdf_name=$externalLink;
                $pdf_same_date_count=0;
            } elseif(!is_null($file)) {

                $file_name = $publication_name . "-" . str_replace("-", "", $issue_date)."-". ($pdf_same_date_count + 1) . ".pdf";

                $pdf_name = $upload_folder_path . $pdf_path . "/" . $file_name;
                $path = Storage::putFileAs($upload_folder_path . $pdf_path, $file, $file_name);

            }else{
                $pdf_name = $request->input('externalLinkFromDb');
            }

            $file_preview = $request->file('filePathPreview');
            $preview_image = $request->input('filePathPreviewFromDb');
            if (isset($file_preview)) {
                $file_name_preview = $publication_name . "-" . str_replace("-", "", $issue_date)."-". ($pdf_same_date_count + 1) . ".jpg";
                $preview_image = $upload_folder_path . $pdf_path . "/" . $file_name_preview;
                $path_preview = Storage::putFileAs($upload_folder_path . $pdf_path, $file_preview, $file_name_preview);
            } elseif(($preview_image == '') && isset($file)) {
                $preview_image = CommonController::createPdfPreview($path);
            }

            $timestamp = date('His', strtotime(now()));
            //get user
            $user = $_COOKIE['user'];
            $user = Crypt::decrypt($user);
            $user = json_decode($user);

            //set pdf
            if (is_array(ThemeService::ConfigValue('PDF_TYPES'))) {
                foreach (ThemeService::ConfigValue('PDF_TYPES') as $key => $type) {
                    if ($key == $publication_name) {
                        $publication_name = $type;
                    }
                }
            }
            $pdf = pdf::updateOrCreate(
                            ['pdf_id' => $request->input("pdf_id")], ['pdf_name' => $pdf_name . '?ts=' . $timestamp,
                        'publication_name' => $publication_name,
                        'issue_date' => $issue_date,
                        'issue_number' =>$issue_number,
                        'preview_image' => $preview_image . '?ts=' . $timestamp,
                        'upload_time' => date('Y-m-d H:i:s'),
                        'uploaded_by' => $user->user_id,
                        'uploader_ip' => $request->server('REMOTE_ADDR'),
                        'pdf_size' => 0,
                        'pdf_type' => 0,
                        'paid_issue' => 0]);

            return redirect('/management/manage-pdfs');
        } else {
            //if no files or images updates
        $externalLinkFromDb = $request->input('externalLinkFromDb');
        $filePathPreviewFromDb = $request->input('filePathPreviewFromDb');
            $issue_date = $request->input("issue_date");
            $publication_name = $request->input("ddl_type");

            if (is_array(ThemeService::ConfigValue('PDF_TYPES'))) {
                foreach (ThemeService::ConfigValue('PDF_TYPES') as $key => $type) {
                    if ($key == $publication_name) {
                        $publication_name = $type;
                    }
                }
            }
            //get user
            $user = $_COOKIE['user'];
            $user = Crypt::decrypt($user);
            $user = json_decode($user);

            $pdf = pdf::updateOrCreate(
            ['pdf_id' => $request->input("pdf_id")], ['pdf_name' => $externalLinkFromDb,
            'publication_name' => $publication_name,
            'issue_date' => $issue_date,
            'issue_number' => $issue_number,
            'preview_image' => $filePathPreviewFromDb,
            'upload_time' => date('Y-m-d H:i:s'),
            'uploaded_by' => $user->user_id,
            'uploader_ip' => $request->server('REMOTE_ADDR'),
            'pdf_size' => 0,
            'pdf_type' => 0,
            'paid_issue' => 0]);

            return redirect('/management/manage-pdfs');

        }
    }

    public function deletePdf(Request $request) {
        try {
            if (is_numeric($request->pdf_id) && $request->pdf_id > 0) {
                $pdf = pdf::where('pdf_id', $request->pdf_id)->first();
                Storage::delete($pdf->pdf_name);
                Storage::delete($pdf->preview_image);
                pdf::where('pdf_id', $request->pdf_id)->delete();
            }
        } finally {
            return redirect('/management/manage-pdfs');
        }
    }

    /* ads_display table */

    public function saveZone(Request $request) {
        $zone = ads_zones::updateOrCreate(
                        ['id' => $request->input("zone_id")], [
                    'zone_name' => $request->input("zone_name"),
                    'is_active' => $request->input("is_active")]);

        return redirect('/management/manage-zones');
    }

    public function saveAdRule(Request $request) {
        $previous_url = $request->input("previous_url");
         $captcha = $request->input('g-recaptcha-response');

        // Check if recaptcha exists
        if (!CommonController::verifyRecaptcha($captcha) || empty($captcha)) {
            $ad_id = ($request->input("ad_id") > 0) ? $request->input("ad_id") : 0;
            return redirect('/management/manage-ad-rules/'.$ad_id.'?error=2');
        }

        $ad = ads_rules::updateOrCreate(
                        ['id' => $request->input("ad_id")], [
                    'amp_article' => (null !== $request->input("amp_article")) ? $request->input("amp_article") : 0,
                    'url' => $request->input("url"),
                    'url_segments_count' => ($request->input("url") != '') ? count(explode('/', $request->input("url"))) - 1 : 0,
                    'url_formula' => $request->input("url_formula"),
                    'ads_zone_id' => $request->input("ads_zone_id"),
                    'script_web' => addslashes($request->input("script_web")),
                    'header_script_web' => $request->input("header_script_web"),
                    'active_script_web' => (null !== $request->input("active_script_web")) ? $request->input("active_script_web") : 0,
                    'script_mobile' => addslashes($request->input("script_mobile")),
                    'header_script_mobile' => $request->input("header_script_mobile"),
                    'active_script_mobile' => (null !== $request->input("active_script_mobile")) ? $request->input("active_script_mobile") : 0]);

//        return redirect('/management/manage-ad-rules');
        return redirect($previous_url);
    }

    public function saveAd(Request $request) {
        $ad = ads_display::updateOrCreate(
                        ['id' => $request->input("ad_id")], ['page_id' => $request->input("ddl_page"),
                    'section_id' => $request->input("ddl_section"),
                    'ad_type' => $request->input("ad_type"),
                    'ad_code' => $request->input("ad_code")]);

        return redirect('/management/manage-ads');
    }

    public function saveHeaderAd(Request $request) {
        $ad = ads_header_display::updateOrCreate(
                        ['id' => $request->input("ad_id")], ['page_id' => $request->input("ddl_page"),
                    'section_id' => $request->input("ddl_section"),
                    'header_code' => $request->input("header_code")]);

        return redirect('/management/manage-ads-header');
    }

    public function deleteAd(Request $request) {
        try {
            if (is_numeric($request->ad_id) && $request->ad_id > 0) {
                ads_display::where('id', $request->ad_id)->delete();
            }
        } finally {
            return redirect('/management/manage-ads');
        }
    }

    public function deleteZone(Request $request) {
        $message = '';
        try {
            if (is_numeric($request->zone_id) && $request->zone_id > 0) {
                $zone_rules = ads_rules::where('ads_zone_id', $request->zone_id)->get();
                if (count($zone_rules) == 0) {
                    ads_zones::where('id', $request->zone_id)->delete();
                }else{
                    $message = 'error';
                }
            }
        } finally {
            return redirect('/management/manage-zones/'.$message);
        }
    }

    public function deleteAdRule(Request $request) {
        try {
            if (is_numeric($request->rule_id) && $request->rule_id > 0) {
                ads_rules::where('id', $request->rule_id)->delete();
            }
        } finally {
            return redirect('/management/manage-ad-rules');
        }
    }

    public function deleteHeaderAd(Request $request) {
        try {
            if (is_numeric($request->ad_id) && $request->ad_id > 0) {
                ads_header_display::where('id', $request->ad_id)->delete();
            }
        } finally {
            return redirect('/management/manage-ads-header');
        }
    }

    public function newUserAction(Request $request) {

        $return_array = array(
            'is_error' => '0',
            'error_code' => '0',
            'paid_flag' => '0',
            'message_ar' => 'تم التسجيل بنجاح',
            'message_en' => 'successfully registered'
        );


        $email = $request->email;
        $username = isset($request->username) ? $request->username : $request->email;
        $password = $request->password;
        $confirm_password = $request->confirm_password;
        $full_name = isset($request->full_name) ? $request->full_name : '';
        $phone = isset($request->phone) ? $request->phone : '';
        $emailVerificationToken = bin2hex(openssl_random_pseudo_bytes(16));
        $userToken = bin2hex(openssl_random_pseudo_bytes(16));
        $captcha = $request->captcha_response;



        // Check if email exists
        $get_email_result = User::where('email', $email)->first();
        if (!empty($get_email_result)) {
            $return_array = array(
                'is_error' => '1',
                'error_code' => '1',
                'message_ar' => 'البريد الإلكتروني موجود',
                'message_en' => 'Email Exists'
            );
            return response()->json([$return_array], 200);
        }


        // Check if username exists
        $get_username_result = User::where('username', $username)->first();
        if (!empty($get_username_result)) {
            $return_array = array(
                'is_error' => '1',
                'error_code' => '2',
                'message_ar' => 'اسم المستخدم موجود ',
                'message_en' => 'Username Exists'
            );

            return response()->json([$return_array], 200);
        }


        // Passwords validation
        if (empty($password)) {
            $return_array = array(
                'is_error' => '1',
                'error_code' => '3',
                'message_ar' => 'تحقق من كلمة المرور  ',
                'message_en' => 'Check your password',
            );

            return response()->json([$return_array], 200);
        }


        if ($password != $confirm_password) {
            $return_array = array(
                'is_error' => '1',
                'error_code' => '4',
                'message_ar' => 'كلمة السر غير متطابقة',
                'message_en' => 'Password Does Not Match'
            );

            return response()->json([$return_array], 200);
        }

        // Check if recaptcha exists
        if (!CommonController::verifyRecaptcha($captcha)) {
            $return_array = array(
                'is_error' => '1',
                'error_code' => '5',
                'message' => 'Invalid re-captcha. Please try again.',
                'message_ar' => 'رمزالمرور غير صالح. حاول مرة اخرى.'
            );

            return response()->json([$return_array], 200);
        }

        User::insert([
            'username' => $username,
            'password' => Hash::make($password),
            'full_name' => $full_name,
            'email' => $email,
            'phone' => $phone,
            'email_verification_token' => $emailVerificationToken,
            'user_token' => $userToken,
            'start_date' => date('Y-m-d H:i:s'),
            'end_date' => date('Y-m-d H:i:s'),
            'last_saved_at' => date('Y-m-d H:i:s')
        ]);


        $lastInsert = User::Where('email', $email)->first();

        if (ThemeService::ConfigValue('COOKIE_LOGIC')) {
            $lastInsert = json_encode($lastInsert);
            $lastInsert_encrypt = Crypt::encrypt($lastInsert);
//          $lastInsert_decrypt = Crypt::decrypt($lastInsert_encrypt);
            CommonController::createCookie('logged_in_user', $lastInsert_encrypt, url('/'));
        }

        $emailVerifyUrl = ThemeService::ConfigValue('APP_URL') . 'verify-email/' . $emailVerificationToken;
        $newspaper_page_title = 'Email from ' . ThemeService::ConfigValue('NEWSPAPER_PAGE_TITLE');


        $objMail = new \stdClass();

        $objMail->view = 'theme::mails.verify-email';
        $objMail->subject = 'Resignation - ' . $full_name;
        $objMail->senderEmail = ThemeService::ConfigValue('EMAIL_FROM');
        $objMail->emailVerifyUrl = $emailVerifyUrl;
        $objMail->fullName = $full_name;
        $objMail->newspaperPageTitle = $newspaper_page_title;
        $objMail->currentDay = date('Y-m-d H:i:s');

        Mail::to($email)->send(new SendEmail($objMail));


        return response()->json([$return_array], 200);
    }

    public function verifyEmailAction(Request $request) {
        $emailVerificationToken = $request->token;
        try {
            if (!empty($emailVerificationToken)) {
                User::where('email_verification_token', $emailVerificationToken)->update(['email_verified' => 1, 'email_verification_token' => '']);
            }
        } finally {
            return redirect('/');
        }
    }

    public function loginUserAction(Request $request) {


        $return_array = array(
            'is_error' => '0',
            'error_code' => '0',
            'message_ar' => 'تم تسجيل الدخول بنجاح',
            'message_en' => 'successfully logged in',
        );

        $username = isset($request->username) ? $request->username : $request->email;
        $password = $request->password;

        $captcha = $request->captcha_response;

        // Check if recaptcha exists
        if (!CommonController::verifyRecaptcha($captcha)) {
            $return_array = array(
                'is_error' => '1',
                'error_code' => '3',
                'message' => 'Invalid re-captcha. Please try again.',
                'message_ar' => 'رمزالمرور غير صالح. حاول مرة اخرى.'
            );

            return response()->json([$return_array], 200);
        }


        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $result = User::Where('email', $username)->Where('email_verified', '1')->first();
        } else {
            $result = User::Where('username', $username)->Where('email_verified', '1')->first();
        }


        if ($result) {

            $hash_password = Hash::make($password);
            $check_pass = Hash::check($password, $result['password']);

            if (!$check_pass) {
                $return_array = array(
                    'is_error' => '1',
                    'error_code' => '1',
                    'message_ar' => 'لقد أدخلت كلمة مرور خاطئة',
                    'message_en' => 'You entered Wrong Password'
                );
                return response()->json([$return_array], 200);
            }
        } else {
            $return_array = array(
                'is_error' => '1',
                'error_code' => '2',
                'message_ar' => 'تحقق من بريدك الالكتروني',
                'message_en' => 'Check your email'
            );
            return response()->json([$return_array], 200);
        }

        $result = json_encode($result);
        $result = Crypt::encrypt($result);

        if (ThemeService::ConfigValue('COOKIE_LOGIC')) {
            CommonController::createCookie('logged_in_user', $result, url('/'));
        } else {
            session(['user_loggin' => $result]);
        }

        return response()->json([$return_array], 200);
    }

    public function logoutUserAction() {

        if (ThemeService::ConfigValue('COOKIE_LOGIC')) {
            unset($_COOKIE['logged_in_user']);
            setcookie('logged_in_user', null, -1, "/");
        }

        return redirect('/');
    }

    public function forgotPasswordAction(Request $request) {

        $username = $request->username;
        $language = $request->language;

        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $userInfo = User::Where('email', $username)->first();
        } else {
            $userInfo = User::Where('username', $username)->first();
        }

        if ($userInfo) {
            $user_name = $userInfo['full_name'];
            $userId = $userInfo['user_id'];
            $email = $userInfo['email'];

            $random = rand(10000, 100000);
            $random_encoded = Crypt::encrypt($random);

            User::where('user_id', $userId)->update(['reset_code' => $random_encoded]);

            if ($language == 'ar') {
                $body = "  لإعادة تعيين كلمة المرور ، يجب الانتقال إلى هذا الرابط <br> " . env('APP_URL') . "reset-password/" . $userId . "/" . $random . ". <br>  	شكرا لك.";
            }
        }
    }

}
