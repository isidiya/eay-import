<?php
namespace App\Providers;


use Illuminate\Support\ServiceProvider;
use Layout\Website\Services\ThemeService;

class ThemeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //This registers the themes view path to the "theme" namespace so
        // any view called like view('theme::VIEW_NAME') will be get from the themes view folder
        // if the view in that folder is not available if will fallback to the default theme folder and get the view from there
        $views = [
            ThemeService::ViewPath(),
            ThemeService::DefaultViewPath(),
            ThemeService::ConfigValue('IS_ONLINE_CMS')? ThemeService::CmsViewPath(): null,

        ];

        $this->loadViewsFrom($views, 'theme');
    }
}