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

class newsRssSitemapArticles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'articles:newssitemap {year?} {month?}';

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
		$year = $this->argument('year');
		$month = $this->argument('month');
		$this->CreateGenericSiteMap();
		$year = $year ? $year : date('Y');
		$month = $month ? $month : date('n');
		$first_of_month = $year."-" .$month . "-01";
		$first_of_next_month =$year."-" .($month+1) . "-01";
		$articles = [];
		echo $year ." ". $month. "\n";
		$xml_dir = $this->default_folder ."/" .$year ."/" .$month  ;
		$xml_file =$xml_dir."/sitemap_news.xml";
		if(!File::exists($xml_file)) {
			File::makeDirectory($xml_dir, $mode = 0777, true, true);
		}
                $time_field = (ThemeService::ConfigValue('ENABLE_MAX_PUBLISH_TIME')) ? 'max_publish_time' : 'publish_time';
		$articles_live = article::where($time_field,'<=', DB::raw(ThemeService::ConfigValue('GIVEN_TIME')))
			->where($time_field,">=",$first_of_month)
			->where($time_field,"<",$first_of_next_month)
			->get();

		foreach ($articles_live as $article_live){
			$articles[]= $article_live;
		}

		$articles_archive = \App\Models\article_archive::where('publish_time',">=",$first_of_month)
			->where('publish_time',"<",$first_of_next_month)
			->get();
		$articles_archive_array= [];
		foreach ($articles_archive as $article_archive){
			$theme_controller_class = 'Themes\\'.ThemeService::Name().'\\controllers\\'.  CommonController::controller_class_name();
			if(class_exists($theme_controller_class)){
				$themeController = new $theme_controller_class();
				if(method_exists($themeController, 'getArchiveArticle')) {
					$function_name ='getArchiveArticle';
					$articles[]= $themeController->$function_name($article_archive);
				}
			}
		}


		$view_content =View::make('theme::components.sitemap_news_articles', ['articles'=>$articles])->render();
		File::put($xml_file, $view_content);
    }
	public function CreateGenericSiteMap(){
		$xml_dir = $this->default_folder;
		$xml_file =$xml_dir."/sitemap_news.xml";
		if(!File::exists($xml_file)) {
			File::makeDirectory($xml_dir, $mode = 0777, true, true);
		}
		$monthly=[];
		$monthly_array = [];
                $time_field = (ThemeService::ConfigValue('ENABLE_MAX_PUBLISH_TIME')) ? 'max_publish_time' : 'publish_time';
		$monthly_live = article::select(DB::raw('YEAR(publish_time) as years,MONTH(`publish_time`) as months,MAX( last_edited ) as last_edited'))
			->where($time_field,'<=',DB::raw('now()'))
			->groupBy(DB::raw('YEAR(publish_time)'))
			->groupBy(DB::raw('MONTH(`publish_time`)'))
			->get();
		foreach ($monthly_live as $key => $month){
			$monthly[]=$month;
			$monthly_array[$key][]= $month->years;
			$monthly_array[$key][]= $month->months;

		}

		$monthly_archive = \App\Models\article_archive::select(DB::raw('YEAR(publish_time) as years,MONTH(`publish_time`) as months, MAX( publish_time ) as last_edited'))
			->groupBy(DB::raw('YEAR(publish_time)'))
			->groupBy(DB::raw('MONTH(`publish_time`)'))
			->get();
		foreach ($monthly_archive as $month){
			if (!in_array(array($month->years, $month->months), $monthly_array)) {
				$monthly[]=$month;
			}
		}
		$view_content =View::make('theme::components.sitemap_news', ['monthly'=>$monthly] )->render();

		File::put($xml_file, $view_content);
	}
}
