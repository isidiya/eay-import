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
use Layout\Website\Helpers\ImageHelper;
use Layout\Website\Models\WebsiteComponent;

class ArticleImageSingle extends WebsiteComponent
{
    protected $name = WebsiteComponent::article_image_single;
    //protected $cached_minutes = 5;

    public $article;

    public static function init(article $article){
        $obj = new static(); //returns the instance of this class

        $obj->article = $article;

        return $obj;
    }

    //sets the view_data of the component
    protected function handle(){

    }
}