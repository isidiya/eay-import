<?php
/**
 * Created by PhpStorm.
 * User: timur
 * Date: 25.11.2018
 * Time: 20:45
 */

namespace Layout\Website\Models;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Layout\Website\Services\ThemeService;

class WebsiteComponent
{

    const chartbeat     = 'chartbeat';
    const addthis       = 'addthis';
    const disqus       = 'disqus';
    const footer_menu   = 'footer_menu';
    const header_menu   = 'header_menu';
    const header_section  = 'header_section';
    const header_ad  = 'header_ad';
    const footer_section  = 'footer_section';
    const focuspoint_image = 'focuspoint_image';
    const article_image_single = 'article_image_single';
    const article_image_multiple = 'article_image_multiple';
    const article_images = 'article_images';

    protected $cached_minutes = 0;
    protected $cache_key;
    protected $name='';
    protected $view_data;

    protected function __construct(){
        $this->cache_key  = "component_cache::".$this->name;
    }

    protected function add_cache_key_variant($value){
        $this->cache_key  .= "::".$value;
        return true;
    }

    protected static function init_component($init_data = []){
        $obj = new static();

        foreach($init_data as $key=>$value){
            $obj->view_data[$key] = $value;
        }
        return $obj;
    }

    public function render(){
        if(method_exists($this, 'handle')) {
            $this->view_data = $this->handle();
        }
        //$obj->view_data is already set on construction with the extending classes handle() method
        if($this->cached_minutes > 0 && $this->name != 'header_ad') {
            return Cache::remember($this->cache_key, $this->cached_minutes, function () {
                return $this->RenderComponent($this->name, $this->view_data);
            });
        }

        return $this->RenderComponent($this->name, $this->view_data);
    }

    public static function html($view_data = []){
        $obj = self::init_component($view_data);
        return $obj->render();
    }

    private function RenderComponent($component_name, $data=[]){
        if(!is_array($data)){
            $data = [$data];
        }

        //public variables of the component instance added to the view data
        $data = array_merge($data, $this->to_array());

        $config_value = !empty(ThemeService::ConfigValue($component_name,'','components')) ? ThemeService::ConfigValue($component_name,'','components') : [] ;
        //return view('theme::components.'.$component_name, array_merge($data, $config_value));
        //By changing the view() as below we can catch the errors when an exception happens, else se get an "__to_string must not throw an expection" on any kind of error in the view
        try {
            return View::make('theme::components.'.$component_name, array_merge($data, $config_value))->render();
        }
        catch (Exception $e) {
            //dd(ThemeService::ViewPath());
            //dd($this);
            dd($e);
        }
    }

    /**
     * this is used to inject any public variables of the extended component into its view
     * @return mixed
     */
    protected function to_array(){
        return call_user_func('get_object_vars', $this);
    }
}