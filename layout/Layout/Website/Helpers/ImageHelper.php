<?php

namespace Layout\Website\Helpers;

use App\Models\article;
use App\Models\article_archive;
use App\Models\image;
use App\Models\image_archive;
use Layout\Website\Services\PageService;
use Layout\Website\Services\ThemeService;
use App\Http\Controllers\CommonController;

class ImageHelper
{
    public static function getImageThumb($image_path,$image_size)
    {
        $path_info      = pathinfo($image_path);
        $image_thumb    = $path_info['dirname'] . '/thumbs/' . $image_size  .'/'. $path_info['filename'] . '.jpg';
        return $image_thumb;
    }

	public static function getImageSrc($image,$html =true,$thumb=false,$embed=false,$is_focal_point=true,$type='article',$enable_image_cropping=0,$view_image_caption= false,$isDefaultImage=false,$article=array()){

        $second_src = '';
        $initial_thumb = $thumb;
        $src_array =array();
		switch( $image->media_type )
		{
			case image::media_type_youtube:
                if(!empty($image->image_path)){
                    $src = self::returnYoutubeIdFromURL($image->image_path);
                } else {
                    $src = self::returnYoutubeIdFromURL($image->image_caption);
                }
				if($embed){
					$src_url = "https://www.youtube.com/embed/$src?rel=0";
				}else{
					$src_url = 'https://img.youtube.com/vi/'. $src .'/hqdefault.jpg';
				}
				break;
			case image::media_type_vimeo:
				$src = self::returnVimeoIdFromURL($image->image_path);
				if($embed){
					$src_url = "https://player.vimeo.com/video/$src?color=c92329&byline=0&portrait=0&badge=0";
				}else{
					$src_url = 'https://i.vimeocdn.com/video/'. $src . '.jpg?mw=350';
				}
				break;
			case image::media_type_vod:
				$vod_link_arr = explode('/' , $image->image_caption);
				$vod_id ='';
				foreach ($vod_link_arr as $item){
					$vod_id = trim($item);
				}
				$src = $vod_id;

                $theme_controller_class = 'Themes\\'.ThemeService::Name().'\\controllers\\'.ThemeService::ThemeController();
                if(class_exists($theme_controller_class)) {
                    $themeController = new $theme_controller_class();
                    if (method_exists($themeController, 'replaceVodFunction')) {
                        $src_url = $themeController->replaceVodFunction($embed,$image);
                        $src= $src_url;
                        break;
                    }
                }

				if($embed){
                    $src_url = 'https://embed.kwikmotion.com/Embed/' . $image->image_caption . '?CustomButton=ion-android-list';
				}else{
                    if (strpos($image->image_path, 'http') !== false) {
					    $src_url = $image->image_path ;
                    }else{
					    $src_url = ThemeService::ConfigValue('CDN_URL') . $image->image_path ;
                    }
					$src= $src_url;
				}
				break;
			case image::media_type_video:
                if($embed){
                    if (strpos($image->image_path, 'http') !== false) {
                        $src	=  $image->image_path;
                    }else{
                        $src    = ThemeService::ConfigValue('CDN_URL')  . $image->image_path;
                    }
                    $src_url = $src ;
                }else {
                    if (strpos($image->image_path, 'http') !== false) {
                        $src = self::getImgPath200($image->image_path);
                    }else{
                        $src = ThemeService::ConfigValue('CDN_URL') . self::getImgPath200($image->image_path);
                    }
                    $src_url = $src;
                }
                break;
            case image::media_type_instagram:
                $src = self::retrieveInstagramVideoIdFromEmbedCode($image->image_path);
                $src_url = $src;
                break;
            case image::media_type_embed:
                if($embed){
                    $src = $image->image_caption;
                    $src_url = $src ;
                    if(strpos($src, "dailymotion")){ 
                        $video_id = self::returnDailymotionVideoIdFromEmbedCode($src);
                        $src=$video_id;
                        $src_url = "https://www.dailymotion.com/embed/video/".$video_id;
                    }
                    elseif(strpos($image->image_path, "<iframe")!== false){ 
                        $src = $image->image_path;
                        $src_url = $image->image_path;  
                    }
                } else {  
                    $src = $image->image_caption; 
                    $src_url = $src ;
                    if(strpos($src, "dailymotion")){ 
                        $video_id = self::returnDailymotionVideoIdFromEmbedCode($src); 
                        $src=$video_id;
                        $src_url = "https://www.dailymotion.com/thumbnail/video/".$video_id;
                    } 
                    elseif(strpos($image->image_path, "<iframe")!== false){   
                        $src = $image->image_description;  
                        $src_url = $image->image_description;
                    }
                }
                break;
            case image::media_type_audio:
                $src = ThemeService::ConfigValue('CDN_URL') . $image->image_path;
                $src_url = $src ;
                break;
            case image::media_type_doc:
                $src = ThemeService::ConfigValue('CDN_URL') . $image->image_path;
                $src_url = $src ;
                break;
			case image::media_type_pdf:
				$src = ThemeService::ConfigValue('CDN_URL') . $image->image_path;
				$src_url = $src ;
				break;
			case image::media_type_image:
            default :

            //Here we check if we have a function getCropppingImage sin Theme controller
            $theme_controller_class = 'Themes\\'.ThemeService::Name().'\\controllers\\'.ThemeService::ThemeController();
            if(class_exists($theme_controller_class)){
                $themeController = new $theme_controller_class();
                if(method_exists($themeController, 'getCroppingImage')) {
                    $getCroppingImage = $themeController->getCroppingImage($image,$enable_image_cropping,$thumb,$isDefaultImage,$html);
                    extract($getCroppingImage);
                }else{
                    if($enable_image_cropping){
                        // $image->image_cropping coming null in case image archive
                        if(!empty($image->image_cropping && strlen($image->image_cropping) > 10)){
                            $image_cropping_array = $image->image_cropping;
                            $image_cropping_array = json_decode(stripslashes($image_cropping_array),true);
                            foreach($image_cropping_array  as $key => $image_cropping){
                                if($image_cropping['icd_image_type']==$thumb){
                                    $image_is_cropped = true;
                                    break;
                                }else{
                                    $image_is_cropped = false;
                                }
                            }
                        }
                        else{
                            $image_is_cropped = false;
                        }

                        if(!$image_is_cropped && !ThemeService::ConfigValue('IMAGE_ALWAYS_CROPPED')){
                            $thumb = 0;
                        }

                    }

                    if($thumb && !is_numeric(strpos($thumb, ",") )){
                        $src	    = ThemeService::ConfigValue('CDN_URL')  . self::getImageThumb($image->image_path,$thumb);
                        $src = strtolower($src);
                        $second_src	= ThemeService::ConfigValue('CDN_URL')  . $image->image_path;
                    }else{
                        if (strpos($image->image_path, 'http') !== false) {
                            $src	=  $image->image_path;
                        }else{
                            $src	= ThemeService::ConfigValue('CDN_URL')  . $image->image_path;
                        }
                    }
                    $src_url = $src ;
                }
            }
            break;
		}
		if(!$html){
			return $src_url;
		}

		switch( $type )
		{
			case 'amp-article':
				return View('theme::components.amp_image_src',['src'=>$src,'thumb'=>$thumb,'embed'=>$embed,'image'=>$image]);
				break;
			case 'rssia-article':
				return View('theme::components.rssia_image_src',['src'=>$src,'thumb'=>$thumb,'embed'=>$embed,'image'=>$image,'html'=>$html]);
				break;
			case 'no_html_image':
                return View('theme::components.no_image_src',[
                        'thumb'=>$thumb]
                );
				break;
			default :
				return View('theme::components.image_src',[
				    'src_array'=>$src_array,
				    'article'=>$article,
				    'src'=>$src,
                    'second_src'=>$second_src,
                    'thumb'=>$thumb,
                    'initial_thumb'=>$initial_thumb,
                    'embed'=>$embed,
                    'image'=>$image,
                    'view_image_caption'=>$view_image_caption,
                    'is_focal_point'=>$is_focal_point,
                    'isDefaultImage'=>$isDefaultImage
                ]);
				break;
		}
	}
    public static function getImagePath(image $image, article $article=null, $embed = false, $extraParams = false, $thumbs = false,$autoPlay = false)
    {
        $image_article = !is_null($article) ? $article : $image->article;
        $arrayOpinion = [];
            $imagePath = json_decode(stripslashes($image->image_path),true);//to get image in load_author_articles
            if(is_array($imagePath)){
                $src = $imagePath['image_path'];
                $image->image_path= $src;
            }else{
                $src = $image->image_path;
            }
            if($image->type == 'preview')
                return $src;

            switch( $image->media_type )
            {
                case image::media_type_youtube:
                    if( $embed )
                    {
                        $youtube_video_id	= self::returnYoutubeIdFromURL($image->image_path);

                        if($autoPlay){
                            $src		        = "https://www.youtube.com/embed/$youtube_video_id?rel=0&autoplay=1&mute=1&enablejsapi=1";
                        }else{
                            $src		        = "https://www.youtube.com/embed/$youtube_video_id?rel=0";
                        }
                    }
                    else
                    {
                        $src	= self::retrieveYoutubeThumbnail( $src );
                    }
                    break;
                case image::media_type_vimeo:
                    if ($embed) {
                        $vimeo_video_id = self::returnVimeoIdFromURL($image->image_path);
                        $src = "https://player.vimeo.com/video/$vimeo_video_id?color=c92329&byline=0&portrait=0&badge=0";
                    } else {
                        $src = self::retrieveVimeoThumbnail($src);
                    }
                    break;
                case image::media_type_vod:
                    if ($embed) {
                        if($autoPlay){
                            $src = 'https://vod-platform.net/Embed/' . $image->image_caption . '?' . $extraParams .'&autoplay=0';
                        }else{
                            $src = 'https://vod-platform.net/Embed/' . $image->image_caption . '?' . $extraParams;
                        }
                    } else {
                        $src = 'https://vod-platform.net/image/' . $image->image_caption ;
                    }
                    break;
                case image::media_type_video:
                    $src	= ThemeService::ConfigValue('CDN_URL') . self::getImgPath200($src);
                    break;
                case image::media_type_pdf:
                    $src = ThemeService::ConfigValue('CDN_URL') . $src;
                    break;
                case image::media_type_image:
                default :
                    $src	= ThemeService::ConfigValue('CDN_URL')  . $src;

                    if($thumbs){
                        if(!empty(ThemeService::ConfigValue('IMAGE_SIZE'))){
                            $thumbName			= pathinfo($src, PATHINFO_FILENAME) . '.jpg';
                            $src = pathinfo($src, PATHINFO_DIRNAME) . '/thumbs/'.ThemeService::ConfigValue('IMAGE_SIZE') .'/' . $thumbName;
                        }else{
                            $src = pathinfo($src, PATHINFO_DIRNAME) . '/thumbs/' . pathinfo($src, PATHINFO_BASENAME);
                        }
                    }
                    break;
            }

        return $src;
    }
    
    public static function returnDailymotionVideoIdFromEmbedCode( $src )
    {
        $src = str_replace('&quot;', '"', $src);
        $re = '/video-id=("|\')(.*?)("|\')/';  
        $video_id = '';
        preg_match_all($re, $src, $matches, PREG_SET_ORDER, 0);
        if($matches){
        $video_id=$matches[0][2];
        }
        if($video_id == ''){
             $re = '/data-video=("|\')(.*?)("|\')/';  
            preg_match_all($re, $src, $matches, PREG_SET_ORDER, 0);
            if($matches){
            $video_id=$matches[0][2];
            }
        }
        if($video_id == ''){
            $re = '/src="([^"]+)"/'; 
            preg_match_all($re, $src, $matches, PREG_SET_ORDER, 0);
            if($matches){
            $urlToArray=explode("/", $matches[0][1]); 
            // split id if contain ?autoplay=1 and get id before ?
            if(isset($urlToArray[5])){
                $video_id_in_array=explode("?", $urlToArray[5], 2);
                $video_id = $video_id_in_array[0];
            }
            }  
        }
        if($video_id == ''){
            // ged video id where it s last parameter
            $re = '%^(.+)/(.*)%'; 
            preg_match_all($re, $src, $matches, PREG_SET_ORDER, 0); 
            if($matches){
            $video_id= $matches[0][2];  
            }
                
            } 
//            remove the options, example ?autoplay=1
        if(strpos($video_id, '?') !== FALSE){
            $video_id = substr($video_id, 0, strpos($video_id, "?"));
        }
       
            return $video_id; 
    }
    
    public static function retrieveInstagramVideoIdFromEmbedCode($instagram_url){
        $video_id = str_replace('https://www.instagram.com/reel/', '', $instagram_url);
        $video_id = strtok($video_id, '/');
        return $video_id;
    }

    public static function retrieveYoutubeThumbnail( $youtube_link )
    {
        $youtube_video_id	= self::returnYoutubeIdFromURL($youtube_link);

        $file_name	= 'hqdefault.jpg'; // Always use largest thumbnail

        $thumbnail_url	= 'https://img.youtube.com/vi/' . $youtube_video_id . '/' . $file_name;

        return $thumbnail_url;
    }

    public static function returnYoutubeIdFromURL( $youtube_link )
    {
        if ( filter_var($youtube_link, FILTER_VALIDATE_URL) )
        { 
            $parse_yt =self::parseURL($youtube_link);
 
            if(isset($parse_yt['query']['v'])){
                $youtube_video_id	= $parse_yt['query']['v']; // https://www.youtube.com/watch?v=KlAl5RHAAns
            }else{
                $parse_yt				= parse_url($youtube_link);
                unset($parse_yt["query"]);
                $youtube_video_id = trim($parse_yt["path"],"/") ;
                if(is_numeric(strrpos($youtube_video_id, '/'))){ // case https://www.youtube.com/shorts/4QABiitInCw
                    $youtube_video_id = substr($youtube_video_id, strrpos($youtube_video_id, '/') + 1);
                }
            }
        }
        else // Assuming only the id is sent or link not valide url like https://youtu.be/CgXeNsSpJtQ
        { 
            if(is_numeric(strrpos($youtube_link, '/'))){ // case  https://youtu.be/CgXeNsSpJtQ
                $youtube_link = substr($youtube_link, strrpos($youtube_link, '/') + 1);
            } 
            $youtube_video_id	= $youtube_link;
                
        }

        return $youtube_video_id;
    }



    public static function parseURL( $url )
    {
        $url_components				= parse_url($url);
		if(!isset($url_components['query'])){
			return 0;
		}
		$url_components['query'] = trim($url_components['query'],"&");
        $url_components['query']	= explode('&', $url_components['query']);
        $url_components['query']	= array_map(function($item){
            return array( explode('=', $item)[0] => explode('=', $item)[1] );
        }, $url_components['query']);

        foreach ( $url_components['query'] as $key => $value )
        {
            $url_components['query'][array_keys($value)[0]]	= array_values($value)[0];
            unset($url_components['query'][$key]);
        }

        return $url_components;
    }

    public static function returnVimeoIdFromURL( $vimeo_link )
    {
        if ( filter_var($vimeo_link, FILTER_VALIDATE_URL) )
        {
            $vimeo_link = explode('/', $vimeo_link);
            $vimeo_id = $vimeo_link[count($vimeo_link)-1];
        }
        else if(preg_match( '/vimeo.com/',$vimeo_link)){
            $vimeo_link = explode('/', $vimeo_link);
            $vimeo_id = $vimeo_link[count($vimeo_link)-1];
        }
        else // Assuming only the id is sent
        {
            $vimeo_id	= $vimeo_link;
        }

        return $vimeo_id;
    }

    public static function RetreiveYoutubeThumbnail( $youtube_link, $size = 'small' )
    {
        $youtube_video_id	= self::returnYoutubeIdFromURL($youtube_link);

        $file_name	= 'hqdefault.jpg'; // Always use largest thumbnail

        $thumbnail_url	= 'https://img.youtube.com/vi/' . $youtube_video_id . '/' . $file_name;

        return $thumbnail_url;
    }

    public static function GetSectionAndSubSectionFromName( $category_name )
    {
        $category_name		= ( !empty( $category_name ) ? $category_name : 'NULL' );
        $sectionMySqlExtDAO	= new \SectionMySqlExtDAO;
        $section_info_array	= $sectionMySqlExtDAO->getSectionInfoByName( $category_name );
        $section_id			= ( isset( $section_info_array->npSectionId ) && $section_info_array->npSectionId > 0 ? $section_info_array->npSectionId  : 0 );

        $sub_section_id		= 0;
        if( $section_id == 0 ){
            $subSectionMySqlExtDAO	= new \SubSectionMySqlExtDAO;
            $subSection_info_array	= $subSectionMySqlExtDAO->getSubSectionInfoByName( $category_name );
            $sub_section_id			= ( isset( $subSection_info_array->npSubSectionId ) && $subSection_info_array->npSubSectionId > 0 ? $subSection_info_array->npSubSectionId  : 0 );
            $section_id				= ( isset( $subSection_info_array->sectionId ) && $subSection_info_array->sectionId > 0 ? $subSection_info_array->sectionId  : 0 );
        }
        $return_array = array( 'section_id'=>$section_id, 'sub_section_id'=>$sub_section_id );
        return $return_array;
    }

    public static function retrieveVimeoThumbnail( $vimeo_link )
    {
        $vimeo_video_id		= self::returnVimeoIdFromURL($vimeo_link);
        $vimeo_preview_path	= ThemeService::ConfigValue('BASE_PATH') . '/uploads/images/vimeo_previews/' . $vimeo_video_id . '.jpg';

        if ( is_file($vimeo_preview_path) )
        {
            $vimeo_preview_link	= ThemeService::ConfigValue('BASE_URL') . '/uploads/images/vimeo_previews/' . $vimeo_video_id . '.jpg';
        }
        else
        {
            $vimeo_preview_link	= ThemeService::ConfigValue('BASE_URL') . "/images/vimeo-logo.png";
        }

        return $vimeo_preview_link;
    }

    public static function getImgPath200( $image_path, $is_old_image = '0' )
    {
        if ( empty($image_path) )
        {
            return '';
        }

        {// Set $is_old_image if the path contains imported_images; needed in case the flag was not indexed (lucene)
            if ( $is_old_image == '0' && strpos($image_path, 'imported_images') )
            {
                $is_old_image	= '1';
            }
        }

        if ( $is_old_image == '1' )
        {
            $thumbName			= pathinfo($image_path, PATHINFO_FILENAME) . '.' . pathinfo($image_path, PATHINFO_EXTENSION);
            $thumbPath			= pathinfo($image_path, PATHINFO_DIRNAME);
            $thumbPath			= $thumbPath . '/thumbs/';
            $originPath			= pathinfo($image_path, PATHINFO_DIRNAME)."/". $thumbName;
            $image_path_200		= $thumbPath . $thumbName;
            //$image_path_200 =is_file(ThemeService::ConfigValue('BASE_PATH')  . $image_path_200 ) ? $image_path_200 : $originPath;
       		$image_path_200            =  $image_path_200;
        }
        else
        {
            $thumbName			= pathinfo($image_path, PATHINFO_FILENAME) . '.jpg';
            $thumbPath			= pathinfo($image_path, PATHINFO_DIRNAME);
            $thumbPath			= $thumbPath . '/thumbs/';
            $originPath			= pathinfo($image_path, PATHINFO_DIRNAME)."/". $thumbName;
            $image_path_200		= $thumbPath . $thumbName;
            //$image_path_200 =is_file(ThemeService::ConfigValue('BASE_PATH')  . $image_path_200 ) ? $image_path_200 : $originPath;
		$image_path_200            =  $image_path_200;
        }

        return $image_path_200;
    }

	public static function GetFocusStyle($containerW,$containerH,$imageWidth,$imageHeight,$focalX,$focalY )
    {
		$focusStyle ='position:absolute;opacity:1;';
		if (!($containerW > 0 && $containerH > 0 && $imageWidth > 0 && $imageHeight > 0)) {
			return '';
		}
		$hShift = 0;
		$vShift =0;

		$wR = $imageWidth / $containerW;
		$hR = $imageHeight / $containerH;


		if($wR > $hR){
			$hShift = self::calcShift($hR, $containerW, $imageWidth, $focalX);
			$focusStyle .= 'height:100%;width:auto;top:0;left:'.$hShift . ';';
		}elseif ($hR > $wR){
			$vShift =self::calcShift($wR, $containerH, $imageHeight, $focalY, true);
			$focusStyle .= 'width:100%;height:auto;left:0;top:'.$vShift .';';
		}
		return $focusStyle;
    }

	public static function calcShift($conToImageRatio, $containerSize, $imageSize, $focusSize, $toMinus= false)
	{
		$containerCenter = floor($containerSize / 2); //Container center in px
		$focusFactor = ($focusSize + 1) / 2; //Focus point of resize image in px
		$scaledImage = floor($imageSize / $conToImageRatio); //Can't use width() as images may be display:none
		$focus =  floor($focusFactor * $scaledImage);
		if ($toMinus) {
			$focus = $scaledImage - $focus;
		}
		$focusOffset = $focus - $containerCenter; //Calculate difference between focus point and center
		$remainder = $scaledImage - $focus; //Reduce offset if necessary so image remains filled
		$containerRemainder = $containerSize - $containerCenter;//50
		if ($remainder < $containerRemainder)
		{
			$focusOffset -= $containerRemainder - $remainder;
		}
		if ($focusOffset < 0){
			$focusOffset = 0;
		}
		$percentage = ($focusOffset * -100 / $containerSize)  . '%';
		return $percentage;
	}

	public static function checkFileType($file_path,$allowed_extensions){
		$mimet	= array(
			'txt' => 'text/plain',
			'htm' => 'text/html',
			'html' => 'text/html',
			'php' => 'text/html',
			'css' => 'text/css',
			'js' => 'application/javascript',
			'json' => 'application/json',
			'xml' => 'application/xml',
			'swf' => 'application/x-shockwave-flash',
			'flv' => 'video/x-flv',

			// images
			'png' => 'image/png',
			'jpe' => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'jpg' => 'image/jpeg',
			'gif' => 'image/gif',
			'bmp' => 'image/bmp',
			'ico' => 'image/vnd.microsoft.icon',
			'tiff' => 'image/tiff',
			'tif' => 'image/tiff',
			'svg' => 'image/svg+xml',
			'svgz' => 'image/svg+xml',

			// archives
			'zip' => 'application/zip',
			'rar' => 'application/x-rar-compressed',
			'exe' => 'application/x-msdownload',
			'msi' => 'application/x-msdownload',
			'cab' => 'application/vnd.ms-cab-compressed',

			// audio/video
			'mp3' => 'audio/mpeg',
			'qt' => 'video/quicktime',
			'mov' => 'video/quicktime',

			// adobe
			'pdf' => 'application/pdf',
			'psd' => 'image/vnd.adobe.photoshop',
			'ai' => 'application/postscript',
			'eps' => 'application/postscript',
			'ps' => 'application/postscript',

			// ms office
			'doc' => 'application/msword',
			'rtf' => 'application/rtf',
			'xls' => 'application/vnd.ms-excel',
			'ppt' => 'application/vnd.ms-powerpoint',
			'docx' => 'application/msword',
			'xlsx' => 'application/vnd.ms-excel',
			'pptx' => 'application/vnd.ms-powerpoint',


			// open office
			'odt' => 'application/vnd.oasis.opendocument.text',
			'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
		);

		$extension	= pathinfo($file_path, PATHINFO_EXTENSION);

		$fi				= new \finfo(FILEINFO_MIME_TYPE);
		$file_mime_type	= $fi->buffer(file_get_contents( $file_path ));
		if ( $mimet[$extension] == $file_mime_type )
		{
			return $result = 0;
//			echo 'OK';
		}
		else
		{
			return $result = 1;
//			echo 'Problem';
		}

	}
}
