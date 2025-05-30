<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Jenssegers\Agent\Agent;
use Layout\Website\Services\ThemeService;
use Illuminate\Support\Facades\Cookie;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */

	public static $section_info			= array();
	public static $sub_section_info		= array();
	public static $page		= array();
	public static $device_type = "web";

    public function boot()
    {

    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
