<?php

namespace App\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Layout\Website\Services\ThemeService;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        //

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapThemeRoutes();

        $this->mapWebRoutes();

        //
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapThemeRoutes()
    {
		try{
			foreach (File::allFiles(ThemeService::RoutesPath()) as $route_file) {
				if(substr($route_file, -7)=="web.php"){
					continue;
				}

				Route::middleware('web')
					 ->namespace(ThemeService::RoutesNamespace())
					 ->group($route_file);
			}
		} catch (\InvalidArgumentException $ex) {
			echo view('errors.under_construction');
			exit;
		}
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        if(ThemeService::ConfigValue('IS_ONLINE_CMS')){
            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('Themes/cmsonline/routes/web.php'));
        }

        Route::middleware('web')
             ->namespace($this->namespace)
             ->group(ThemeService::RoutesPath().'/web.php');

        Route::middleware('web')
             ->namespace($this->namespace)
             ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
             ->middleware('api')
             ->namespace($this->namespace)
             ->group(base_path('routes/api.php'));
    }
}
