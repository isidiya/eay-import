<?php

namespace App\Models;

use App\Http\Controllers\CommonController;
use Illuminate\Database\Eloquent\Model;
use Layout\Website\Helpers\ImageHelper;
use Layout\Website\Helpers\UrlHelper;
use Layout\Website\Services\PageService;
use Layout\Website\Services\ThemeService;

class image extends Model
{
    protected  $table="image";
    protected $primaryKey = 'cms_image_id';
    public    $timestamps = false;
    protected $fillable = ['np_image_id','image_caption','np_related_article_id','cms_type','image_description','image_path','media_type','is_old_image','small_image','is_updated','image_cropping','media_order','is_copied','image_is_deleted','image_alt_text','api_status','image_credit_line'];

    const media_type_image = 0;
    const media_type_vimeo = 1;
    const media_type_youtube = 2;
    const media_type_qrcode = 3;
    const media_type_video = 4;
    const media_type_instagram = 5;
    const media_type_vod =6;
    const media_type_embed =7;
    const media_type_pdf =8;
    const media_type_audio = 9;
    const media_type_soundcloud = 11;
    const media_type_doc = 12;

    public static function find_np($np_id){
        return self::where('np_image_id', $np_id)->first();
    }

    public function article()
    {
        return $this->belongsTo('App\Models\article','np_related_article_id','np_article_id');
    }

	public function image_src($html =true, $thumb=false ,$embed=false,$is_focal_point = true,$type='article',$isDefaultImage = true,$view_image_caption=false){
            $enable_image_cropping = ThemeService::ConfigValue('ENABLE_IMAGE_CROPPING'); 
            if(isset($this->article->article_shortlink)){
                $this->article_shortlink = $this->article->article_shortlink;
            }
            /*update parameters to get images in amp article body*/
            $article = PageService::Article();
            return ImageHelper::getImageSrc($this,$html,$thumb,$embed,$is_focal_point,$type,$enable_image_cropping, $view_image_caption, $isDefaultImage,$article);
    }

    /**
     * sets the image->src
     */
    public function getSrcAttribute(){
        return ImageHelper::getImagePath($this);
    }

    public function focus_point_src($article){
        return ImageHelper::getImagePath($this, $article);
    }
    /**
     * sets the image->is_video
     */
    public function getIsVideoAttribute(){
        return ( $this->media_type == self::media_type_video );
    }

    public function getVideoUrlAttribute(){
        if($this->media_type == self::media_type_video){
            return UrlHelper::main_url().$this->image_path;
        }

        return '';
    }

    /**
     * sets the image->image_cropping_array
     */
    public function getImageCroppingArrayAttribute() {
        if (!empty($this->image_cropping) && strlen($this->image_cropping) > 2) {
            $imgObj = json_decode(stripslashes($this->image_cropping));
            return $imgObj;
        }
        return '';
    }
    
    public static function find_main_article_image($np_related_article_id){
        $imgArticle=self::where('np_related_article_id', $np_related_article_id)->where('media_type', '0')->orderBy('media_order', 'asc')->first();
        if (!empty($imgArticle)){
            return ThemeService::ConfigValue('CDN_URL') . $imgArticle->image_path;
        }
        return '';
    }
}
