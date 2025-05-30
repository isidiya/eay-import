<?php
/**
 * Created by PhpStorm.
 * User: timur
 * Date: 25.11.2018
 * Time: 21:11
 */

namespace Layout\Website\Components;


use App\Models\article;
use App\Models\image;
use App\Models\image_archive;
use Layout\Website\Helpers\ImageHelper;
use Layout\Website\Models\WebsiteComponent;

class ArticleImages extends WebsiteComponent
{
    protected $name = WebsiteComponent::article_images;
    //protected $cached_minutes = 5;

    public $article;
    protected $images;

    public static function init(article $article){
        $obj = new static(); //returns the instance of this class

        $obj->article = $article;

        return $obj;
    }
    //sets the view_data of the component
    protected function handle(){
        if($this->article->is_old_article){
            
            $this->images = image_archive::where('np_related_article_id', $this->article->cms_article_id)
                ->where('image_is_deleted', 0)
                ->orderBy('media_order', 'asc') 
                ->get();  
        }else{
            $value = '';
		$re = "/(\*\*media\[(\d|,)*]\*\*)/";
		$matches = array();
		preg_match_all($re, $this->article->article_body, $matches);
		$matches = $matches[0]; 
        $npImageIds = array();
		for ($k = 0; $k < count($matches); $k++)
		{
			$strToSearch =$matches[$k];
			$npImageId = str_replace("**media[", "", $matches[$k]);
			$npImageId = str_replace("]**", "", $npImageId);  
            
            if(strpos($npImageId, ',') !== false){
                $getSeparateIds = explode(",", $npImageId);
                for ($c = 0; $c < count($getSeparateIds); $c++){
                    $separatedImageId =$getSeparateIds[$c];
                    if(is_numeric($separatedImageId)){
                        $npImageIds[]=$separatedImageId;
                    } 
                }
            } else{
                if(is_numeric($npImageId)){
                    $npImageIds[]=$npImageId;
                }
            }  
		}   

        $re = "/<img(.*?)(\**NP_IMAGE_BODY\[.*?]\**)(.*?)\/>/im";
		$matches = array();
		preg_match_all($re, $this->article->article_body, $matches);
		$matches = isset($matches[2])? $matches[2]: array(); 
		for ($k = 0; $k < count($matches); $k++)
		{
			$strToSearch =$matches[$k];
            $npImageId = str_replace("**NP_IMAGE_BODY[", "", $matches[$k]);
			$npImageId = str_replace("]**", "", $npImageId); 
            if(is_numeric($npImageId)){
                $npImageIds[]=$npImageId;
            } 
		}  
        $this->images = image::where('np_related_article_id', $this->article->np_article_id)
                ->whereNotIn('np_image_id', $npImageIds)
                ->where('image_is_deleted', 0)
                ->orderBy('media_order', 'asc') 
                ->get();  
        }
        
         
          
    }
}