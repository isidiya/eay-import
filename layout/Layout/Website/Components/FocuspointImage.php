<?php
/**
 * Created by PhpStorm.
 * User: timur
 * Date: 25.11.2018
 * Time: 21:11
 */

namespace Layout\Website\Components;


use App\Models\image;
use Layout\Website\Helpers\ImageHelper;
use Layout\Website\Models\WebsiteComponent;
use Layout\Website\Services\ThemeService;

class FocuspointImage extends WebsiteComponent
{
    protected $name = WebsiteComponent::focuspoint_image;
    protected $cached_minutes = 0;

    public $article;
    public $image;
    public $container_width;
    public $container_height;
    public $video_tag_slug;
    public $div_slug;
    public $div_size;
    public $focuspoint;
    public $no_image;

    public static function init($article, $image, $container_width=0, $container_height=0, $video_tag_slug = '', $div_slug = '' , $div_size="", $no_image='/images/no-image.png'){

        $obj = new static(); //returns the instance of this class
if(is_integer($image)){
echo "***********************************************".$image; exit;
}

		$no_image= '/'.ThemeService::ConfigValue("THEME_NAME").'/images/no-image.png'; 

		if($image->media_type == 2 || $image->media_type == 6 ){
			$video_tag_slug = 'isvideo';
			if(ThemeService::ConfigValue("DIV_SLUG")){
			$div_slug = ThemeService::ConfigValue("DIV_SLUG");
			}
		}

        $obj->article = $article;
        $obj->image = $image;
        $obj->container_width = $container_width;
        $obj->container_height = $container_height;
        $obj->video_tag_slug = $video_tag_slug;
        $obj->div_slug = $div_slug;
        $obj->div_size = $div_size;
        $obj->no_image = empty($no_image) ? '/images/no-image.png' : $no_image;

        return $obj;
    }

    //sets the view_data of the component
    protected function handle(){
        $this->focuspoint=!empty($this->image) ? $this->focuspoint_data($this->image, $this->container_width, $this->container_height) : false; //with false , it renders the no/image in the view
    }

    public function focuspoint_data(image $image, $container_width, $container_height)
    {
        $focuspoint              = new \stdClass();
        $focuspoint->div_style   = "";
        $focuspoint->focal_x     = 0;
        $focuspoint->focal_y     = 0;
        $focuspoint->focus_style = "";

        $crop_sizes       = json_decode($image->image_cropping, true);

        if ($image->is_video && $crop_sizes['original_image']['image_original_width'] === null) {
            $real_image_size                                       = getimagesize($displayVars['src']);
            $crop_sizes['original_image']['image_original_width']  = $real_image_size[0];
            $crop_sizes['original_image']['image_original_height'] = $real_image_size[1];
            $crop_sizes['original_image']['icd_image_type']        = 'original_image';
        }

        if (!empty($crop_sizes['focal_point'])) { // Gather data for focal point method
            $focuspoint->focal_x     = $crop_sizes['focal_point']['selectx1'];
            $focuspoint->focal_y     = $crop_sizes['focal_point']['selecty1'];
            $focuspoint->focus_style = 'style="' .
                                       ImageHelper::GetFocusStyle(
                                           $container_width,
                                           $container_height,
                                           $crop_sizes['focal_point']['image_original_width'],
                                           $crop_sizes['focal_point']['image_original_height'],
                                           $focuspoint->focal_x,
                                           $focuspoint->focal_y) .
                                       '"';
            $focuspoint->div_style   = 'style="display:block;"';
        }
        elseif ($crop_sizes['original_image']) {
            $imageWidth                    = $crop_sizes['original_image']['image_original_width'];
            $imageHeight                   = $crop_sizes['original_image']['image_original_height'];
            $focuspoint->focus_style = 'style="' .
                                       ImageHelper::GetFocusStyle(
                                           $container_width,
                                           $container_height,
                                           $imageWidth,
                                           $imageHeight,
                                           $focuspoint->focal_x,
                                           $focuspoint->focal_y) .
                                       '"';
            $focuspoint->div_style   = 'style="display:block;"';
        }

        return $focuspoint;
    }
}