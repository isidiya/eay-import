<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\article;
use App\Models\author;
use App\Models\page;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Layout\Website\Services\ThemeService;
use App\Http\Controllers\CommonController;
use Layout\Website\Services\MenuService;

class rssSitemapArticlesArchive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'articlesarchive:sitemap';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Sitemap as XML for google Sitemap';

    /**
     * Create a new command instance.
     *
     * @return void
     */
	protected $default_folder =  '';

    public function __construct()
    {
		$this->default_folder = ThemeService::PublicMainPath().'/sitemaps';

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
		$monthly =$this->CreateGenericSiteMap();

		foreach ($monthly as $month){
			$articles=[];
			$year = $month->years;
			$month = $month->months;
			$xml_dir = $this->default_folder ."/" . $year."/" . $month ;
			$xml_file =$xml_dir."/sitemap_archive.xml";
			if(!File::exists($xml_file)) {
				File::makeDirectory($xml_dir, $mode = 0777, true, true);
			}


			$articles_archive = \App\Models\article_archive::where('publish_time','<=', DB::raw(ThemeService::ConfigValue('GIVEN_TIME')))
				->where(DB::raw('YEAR(publish_time)'),$year)
				->where(DB::raw('MONTH(publish_time)'),$month)
				->get();
			$articles_archive_array= [];
			foreach ($articles_archive as $article_archive){
				$theme_controller_class = 'Themes\\'.ThemeService::Name().'\\controllers\\'.  CommonController::controller_class_name();
				if(class_exists($theme_controller_class)){
					$themeController = new $theme_controller_class();
					if(method_exists($themeController, 'getArchiveArticleSiteMap')) {
						$function_name ='getArchiveArticleSiteMap';
						$articles[]= $themeController->$function_name($article_archive);
					}
					elseif(method_exists($themeController, 'getArchiveArticle')) {
						$function_name ='getArchiveArticle';
						$articles[]= $themeController->$function_name($article_archive);
					}
				}
			}


			$view_content =View::make('theme::components.sitemap_articles', ['articles'=>$articles])->render();
			File::put($xml_file, $view_content);

		}
    }
	public function CreateGenericSiteMap(){
		$xml_dir = $this->default_folder;
		$xml_file =$xml_dir."/sitemap_article_archive.xml";
		if(!File::exists($xml_file)) {
			File::makeDirectory($xml_dir, $mode = 0777, true, true);
		}
		$monthly=[];
		$monthly_array = [];
		$monthly_archive = \App\Models\article_archive::select(DB::raw('YEAR(publish_time) as years,MONTH(`publish_time`) as months, MAX( publish_time ) as last_edited_archive'))
			->where('publish_time','<=',DB::raw('now()'))
			->groupBy(DB::raw('YEAR(publish_time)'))
			->groupBy(DB::raw('MONTH(`publish_time`)'))
			->get();
		foreach ($monthly_archive as $month){
			if (!in_array(array($month->years, $month->months), $monthly_array)) {
				$monthly[]=$month;
			}
		}
		$view_content =View::make('theme::components.sitemap_archive', ['monthly'=>$monthly] )->render();
		File::put($xml_file, $view_content);
		return $monthly;
	}
}
