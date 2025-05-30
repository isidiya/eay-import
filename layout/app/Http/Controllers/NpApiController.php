<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Layout\Website\Services\ThemeService;


class NpApiController extends Controller{

    public static function AddArticleAPI($params_array){




//        dd($params_array);
        $download_image_path		= 'C:/wamp64/www/layout_migration/Themes/' . env('APP_THEME').'/public/images';


        // NP API DATA
        $np_api_url			= ThemeService::ConfigValue('API_URL');
         $np_api_username	= ThemeService::ConfigValue('API_USERNAME');
         $np_api_password	= ThemeService::ConfigValue('API_PASSWORD');

        goto_repeat_article_function:


        {//api
            $command_url	= $np_api_url . 'v1/articlesmanager.json';
            $curl_url		= $command_url;
            $ch				= curl_init( $curl_url );

            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt( $ch, CURLOPT_HEADER, false );
            curl_setopt( $ch, CURLOPT_TIMEOUT, 1800 ); // 30 minutes
            curl_setopt( $ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST );
            curl_setopt( $ch, CURLOPT_USERPWD, $np_api_username . ':' . $np_api_password );
            curl_setopt( $ch, CURLOPT_POSTFIELDS,  http_build_query($params_array)  );
            curl_setopt( $ch, CURLOPT_POST, TRUE );
            curl_setopt( $ch, CURLOPT_HTTPGET, FALSE );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );

            $response		= trim( curl_exec( $ch ) );

//            if(strpos($response, 'success') === false){
//
//                $curl_article_skip = DB::connection('mysql2')->table('curl_article_error')->where('old_article_id',$params_array['old_article_id'])->first();
//                if(empty($curl_article_skip)){
//                    DB::connection('mysql2')->table('curl_article_error')->insert([
//                        'old_article_id' => $params_array['old_article_id'],
//                    ]);
//                    goto goto_repeat_article_function;
//                }else{
//                    return 'article_error';
//                }
//            }


            echo $response . PHP_EOL;
            curl_close ($ch);
            $response		= json_decode($response, true);

            $newArticleId	= $response['success']['article_id'];
            
        }
        
        if( !empty($newArticleId) && !empty($params_array) && !empty($params_array['image_data']))
        {
            $image_data = $params_array['image_data'];
            
            // dd($image_data);

            foreach ($image_data as $ImageKey => $ImageValue)
            {
                $imageData								= array();


                $imageData['caption']					= !empty($ImageValue['image_caption']) ? $ImageValue['image_caption'] : '';
                $imageData['image_alt_text']			= !empty($ImageValue['image_alt_text']) ? $ImageValue['image_alt_text'] : '';
                $imageData['im_credit_line']			= !empty($ImageValue['im_credit_line']) ? $ImageValue['im_credit_line'] : '';
//                $imageData['im_cms_category']			= !empty($ImageValue['im_cms_category']) ? $ImageValue['im_cms_category'] : 0;
                $imageData['im_cms_category']			= !empty($ImageValue['im_cms_category']) ? $ImageValue['im_cms_category'] : 0;

                $imageData['img_status']				= ThemeService::ConfigValue('STATUS_FORWEB_ID');
                $imageData['article_id']				= $newArticleId;
                $imageData['image_action']				= "link_and_upload";
                $imageData['img_type']					= "normal";
                $imageData['embed_image_flag']			= 0;
                $imageData['action']                    = 'new';
                $ImageFullUrl							= strtok($ImageValue['image_full_url'], '?');
                
                // $imageData['article_media_order']		= $ImageKey;
                // $imageData['im_cms_category']		    = $ImageValue['im_cms_category'];

                {//download image
                    $ImageArray		                        = pathinfo($ImageFullUrl);
                    $ImageName		                        = $ImageArray['filename'];
                    $extension		                        = $ImageArray['extension'];
                    $Destfullpath	                        = $download_image_path . "/" . $newArticleId . '/' . $ImageArray['filename'] .".". $extension;


                    $image_action			= "update";

                    if (!file_exists($download_image_path . "/" . $newArticleId . '/')) {
                        mkdir($download_image_path . "/" . $newArticleId . '/', 0777, true);
                    }

                    if(strpos($ImageFullUrl, '.jpg') !== false || strpos($ImageFullUrl, '.png') !== false || strpos($ImageFullUrl, '.jpeg') !== false ){
                        $valid_url = 1;
                    }else{
                        $valid_url = self::ValidUrl($ImageFullUrl);
                    }
//
                    if(strpos($ImageFullUrl, 'fakepath') !== false){
                        continue;
                    }

                    if($valid_url){
                        file_put_contents($Destfullpath, file_get_contents($ImageFullUrl));
                    } else {
////                        image_archive::insert([
////                            'article_id'=> $newArticleId,
////                            'old_article_id'=> $params_array['article_id'],
////                            'image_path'=> $ImageFullUrl,
////                        ]);
                        continue;
                    }


                    //    $test = curl_file_create('C:\/wamp64\/www\/saudipedia-laravel\/public\/images\/552\/1.jpg');
                    $imageData['image_name'] = curl_file_create($Destfullpath);

//                    goto_repeat_image_function:

                    $command_url	= $np_api_url . 'v1/imagesmanager.json';
                    $curl_url		= $command_url;
                    $ch				= curl_init( $curl_url );
                    

                    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
                    curl_setopt( $ch, CURLOPT_HEADER, false );
                    curl_setopt( $ch, CURLOPT_TIMEOUT, 1800 ); // 30 minutes
                    curl_setopt( $ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST );
                    curl_setopt( $ch, CURLOPT_USERPWD, $np_api_username . ':' . $np_api_password );
                    curl_setopt( $ch, CURLOPT_POST, TRUE );
                    curl_setopt( $ch, CURLOPT_POSTFIELDS,  ($imageData)  );
                    curl_setopt( $ch, CURLOPT_HTTPGET, FALSE );
                    curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
                    curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
                    
                    
                    $response			= trim( curl_exec( $ch ) );

//                    if(strpos($response, 'success') === false){
//                        $curl_skip = curl_image_error::where('old_article_id',$params_array['old_article_id'])->where('image_key',$ImageKey)->first();
//                        if(empty($curl_skip)){
//                            curl_image_error::insert([
//                                'old_article_id' => $params_array['old_article_id'],
//                                'np_article_id'=>$newArticleId,
//                                    'image_key'=>$ImageKey,
//                                'image_path'=>$ImageValue['image_full_url']
//                            ]);
//                            goto goto_repeat_image_function;
//                        }else{
//                            return 'image error';
//                        }
//                    }

                    echo $response . PHP_EOL;
                    $response			= json_decode($response, true);
                    curl_close ($ch);
                }
            }


            if($params_array['status_id']==ThemeService::ConfigValue('STATUS_PUBLISHED_ID')){

                $command_url	= $np_api_url . 'v1/articlesmanager.json';
                $curl_url		= $command_url;
                $ch				= curl_init( $curl_url );

                $params_array_update = [];
                $params_array_update['article_id'] = $newArticleId;
                $params_array_update['publication_id'] = $params_array['publication_id'];
                $params_array_update['article_type'] = $params_array['article_type'];
                $params_array_update['article_action'] = 'edit';
                $params_array_update['status_id']  = ThemeService::ConfigValue('STATUS_FORWEB_ID');

                curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
                curl_setopt( $ch, CURLOPT_HEADER, false );
                curl_setopt( $ch, CURLOPT_TIMEOUT, 1800 ); // 30 minutes
                curl_setopt( $ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST );
                curl_setopt( $ch, CURLOPT_USERPWD, $np_api_username . ':' . $np_api_password );
                curl_setopt( $ch, CURLOPT_POSTFIELDS,  http_build_query($params_array_update)  );
                curl_setopt( $ch, CURLOPT_POST, TRUE );
                curl_setopt( $ch, CURLOPT_HTTPGET, FALSE );
                curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
                curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );

                $response		= trim( curl_exec( $ch ) );
                curl_close ($ch);
            }


        }




        if(!empty($newArticleId)){
            echo $newArticleId . ' ' . date('h:i:s') . PHP_EOL ;
            echo $params_array['old_article_id'] . ' ' . date('h:i:s') . PHP_EOL ;
        }


    }


    public static function ValidUrl($url = ''){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ($httpCode);
    }


}



