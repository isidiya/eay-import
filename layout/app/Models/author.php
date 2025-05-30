<?php

namespace App\Models;

use App\Http\Controllers\CommonController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Layout\Website\Helpers\UrlHelper;
use Layout\Website\Services\ThemeService;

class author extends Model
{
    protected $table = "author";
    protected $primaryKey = 'cms_author_id';
    public    $timestamps = false;    //
    protected $fillable = ['author_name', 'author_image', 'author_description'];

	const cached_minutes = 0;

	  public static function find_np($np_id){
        //caches the value for the lifetime of the request so once we set the page_id in the controller we would be able to access it with this service elsewhere
        static $all = null;
        if(is_null($all)){

            $all = Cache::remember("model_cache_all_authors", self::cached_minutes, function () {
                $np_indexed = [];
				if(ThemeService::ConfigValue("ONLY_NP_AUTHOR")){
					$all_tmp = self::where("np_author_id",">",0)->get();
				}else{
					$all_tmp = self::all();
				}
                if($all_tmp){
                    foreach ($all_tmp as $item){
                        $np_indexed[$item->np_author_id] = $item;
                    }
                }
					return $np_indexed;

            });
        }
		if($np_id != 0){
			return isset($all[$np_id]) ? $all[$np_id] : new author();
		}
        return $all;
    }

    public static function find_np_live($np_id){
        //caches the value for the lifetime of the request so once we set the page_id in the controller we would be able to access it with this service elsewhere
        static $all = null;
        if(is_null($all)){
            $all = Cache::remember("model_cache_all_authors_live", self::cached_minutes, function () {
                $np_indexed = [];
				$all_tmp = self::where('np_author_id','>',0)->orwhere(function($q){$q->where('np_author_id','<',0)->where('mini_cms',1);})->get();
				if($all_tmp){
                    foreach ($all_tmp as $item){
                        $np_indexed[$item->np_author_id] = $item;
                    }
                }
					return $np_indexed;

            });
        }
		if($np_id != 0){
			return isset($all[$np_id]) ? $all[$np_id] : new author();
		}
        return $all;
    }

    public static function find_cms($cms_id){
        //caches the value for the lifetime of the request so once we set the page_id in the controller we would be able to access it with this service elsewhere
        static $all = null;
        if(is_null($all)) {
            $all = Cache::remember("model_cache_all_cms_authors", self::cached_minutes, function () {
                $cms_indexed = [];
                $all_tmp = self::find_np(0); // returns all the authors from the static var memory, (0) returns all
                if ($all_tmp) {
                    foreach ($all_tmp as $item) {
                        $cms_indexed[$item->cms_author_id] = $item;
                    }
                }
                return $cms_indexed;

            });
        }
        if($cms_id >0){
            return isset($all[$cms_id]) ? $all[$cms_id] : new author();
        }
        return $all;
    }

	public static function find_name($name=''){
        //caches the value for the lifetime of the request so once we set the page_id in the controller we would be able to access it with this service elsewhere
        static $all = null;
        if(is_null($all)) {
            $all = Cache::remember("model_cache_all_authors_name", self::cached_minutes, function () {
                $np_indexed = [];
                $all_tmp = self::find_np(0); // returns all the authors from the static var memory,
                if ($all_tmp) {
                    foreach ($all_tmp as $item) {
                        $np_indexed[(trim($item->author_name))] = $item;
                    }
                }
                return $np_indexed;

            });
        }
		return isset($all[$name]) ? $all[$name] : new author();
    }

    public static function find_name_from_url($name=''){
        //caches the value for the lifetime of the request so once we set the page_id in the controller we would be able to access it with this service elsewhere
        static $all = null;
        if(is_null($all)) {
            $all = Cache::remember("model_cache_all_authors_name_url", self::cached_minutes, function () {
                $np_indexed = [];
                $all_tmp = self::find_np(0); // returns all the authors from the static var memory,
                if ($all_tmp) {
                    foreach ($all_tmp as $item) {
                        $np_indexed[UrlHelper::clean_url($item->author_name)] = $item;
                    }
                }
                return $np_indexed;
            });
        }
        return isset($all[$name]) ? $all[$name] : new author();
    }

    /**
     * sets the author->author_url
     */
    public function getAuthorUrlAttribute(){
        return UrlHelper::build_seo_url($this->np_author_id,'author',$this->author_name);
    }

    /**
     * sets the author->image
     */
    public function getImageAttribute(){
        if(!empty($this->author_image)){
            $author_image = new image();
            $author_image->media_type = image::media_type_image;
            $author_image->image_cropping = null;
            $author_image->image_caption = $this->author_name;
            $author_image->image_path = $this->author_image;

            return $author_image;
        }
        return false;
    }


    public function author_image_src($html=false,$view = 'author'){
        if($html){
            if($this->np_author_id){
                return View('theme::components.author_image_src',array(
                    'author' => $this,
                    'view' => $view
                ));
            }

        }else{
            if($this->np_author_id){
                return ThemeService::ConfigValue('CDN_URL').$this->author_image;
            }
        }

        return '';
    }


}
