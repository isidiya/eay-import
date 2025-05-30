<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Layout\Website\Helpers\ImageHelper;
use Layout\Website\Helpers\UrlHelper;

class image_archive extends Model
{
    protected  $table="image_archive"; 
    protected $primaryKey = 'cms_image_id';
    public    $timestamps = false;

    const media_type_image = 0;
    const media_type_vimeo = 1;
    const media_type_youtube = 2;
    const media_type_qrcode = 3;
    const media_type_video = 4;
    const media_type_vod =6;
    const media_type_pdf =8;
    const media_type_audio = 9;

    public static function find_np($np_id){
        return self::where('cms_image_id', $np_id)->first();
    }
     
    public function article_archive()
    {
        return $this->belongsTo('App\Models\article_archive','np_related_article_id','cms_article_id');
    }
    public function image_src($html =true,$thumb=true,$embed=false,$is_focal_point=false,$type='article_archive',$enable_image_cropping=0,$view_image_caption=false){
		return ImageHelper::getImageSrc($this,$html,$thumb,$embed,$is_focal_point,$type,$enable_image_cropping,$view_image_caption); 
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
    
}
