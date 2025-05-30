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

class rssSitemapArticles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'articles:sitemap {year?} {month?}';

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
		$articles = [];
		echo $year ." ". $month. "\n";
		$xml_dir = $this->default_folder ."/" .$year ."/" .$month  ;
		$xml_file =$xml_dir."/sitemap_0.xml";
		if(!File::exists($xml_file)) {
			File::makeDirectory($xml_dir, $mode = 0777, true, true);
		}
		if (ThemeService::ConfigValue('ENABLE_MAX_PUBLISH_TIME')){
			$articles_live = article::where('max_publish_time','<=', DB::raw(ThemeService::ConfigValue('GIVEN_TIME')))
				->where(DB::raw('YEAR(publish_time)'),$year)
				->where(DB::raw('MONTH(publish_time)'),$month)
				->get();
		}
		else{
			$articles_live = article::where('publish_time','<=', DB::raw(ThemeService::ConfigValue('GIVEN_TIME')))
				->where(DB::raw('YEAR(publish_time)'),$year)
				->where(DB::raw('MONTH(publish_time)'),$month)
				->get();
		}

		foreach ($articles_live as $article_live){
			$articles[]= $article_live;
		}
		if(!File::exists($xml_dir."/sitemap_archive.xml")) {
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
		}

		$view_content =View::make('theme::components.sitemap_articles', ['articles'=>$articles])->render();
		File::put($xml_file, $view_content);

		//Authors
		$xml_dir = $this->default_folder ;
		$xml_file =$xml_dir."/sitemap_authors.xml";

		$authors = author::where('np_author_id','>',0)->get();
		$view_content =View::make('theme::components.sitemap_authors', ['authors'=>$authors])->render();
		File::put($xml_file, $view_content);

		//Sections related to pages
		$xml_dir = $this->default_folder ;
		$xml_file =$xml_dir."/sitemap_sections.xml";
        if(ThemeService::ConfigValue('SECTIONS_SITEMAP_IDS')){
            $sectionsArray =ThemeService::ConfigValue('SECTIONS_SITEMAP_IDS');
            $header_menu = MenuService::menu(ThemeService::ConfigValue('WEB_MENU_ID'),0,1);
            $view_content =View::make('theme::components.sitemap_sections', ['sections'=>$sectionsArray,'header_menu'=>$header_menu])->render();
        } else {
            $pages = page::where("page_section_id",">", 0)->get();
            $view_content =View::make('theme::components.sitemap_sections', ['pages'=>$pages])->render();
        }
		File::put($xml_file, $view_content);


		if(ThemeService::ConfigValue('SITEMAP_LATEST') && date('Y')  == $year && date('m') == $month ) {
			$xml_dir = $this->default_folder;
			$xml_file =$xml_dir."/latest_articles.xml";
			if (ThemeService::ConfigValue('ENABLE_MAX_PUBLISH_TIME')){
				$articles_latest = article::where('max_publish_time','<=', DB::raw(ThemeService::ConfigValue('GIVEN_TIME')))
					->where(DB::raw('YEAR(publish_time)'), date('Y'))
					->where(DB::raw('MONTH(publish_time)'), date('m'))
					->get();
			}
			else{
				$articles_latest = article::where('publish_time','<=', DB::raw(ThemeService::ConfigValue('GIVEN_TIME')))
					->where(DB::raw('YEAR(publish_time)'), date('Y'))
					->where(DB::raw('MONTH(publish_time)'), date('m'))
					->get();
			}
			$view_content =View::make('theme::components.sitemap_latest_articles', ['articles'=>$articles_latest])->render();
			File::put($xml_file, $view_content);
		}
    }
	public function CreateGenericSiteMap(){
		$xml_dir = $this->default_folder;
		$xml_file =$xml_dir."/sitemap_0.xml";
		if(!File::exists($xml_file)) {
			File::makeDirectory($xml_dir, $mode = 0777, true, true);
		}
		$monthly=[];
		$monthly_array = [];
		if (ThemeService::ConfigValue('ENABLE_MAX_PUBLISH_TIME')){
			$monthly_live = article::select(DB::raw('YEAR(publish_time) as years,MONTH(`publish_time`) as months,MAX( last_edited ) as last_edited'))
			->where('max_publish_time','<=',DB::raw('now()'))
			->groupBy(DB::raw('YEAR(publish_time)'))
			->groupBy(DB::raw('MONTH(`publish_time`)'))
			->get();
		}
		else{
			$monthly_live = article::select(DB::raw('YEAR(publish_time) as years,MONTH(`publish_time`) as months,MAX( last_edited ) as last_edited'))
			->where('publish_time','<=',DB::raw('now()'))
			->groupBy(DB::raw('YEAR(publish_time)'))
			->groupBy(DB::raw('MONTH(`publish_time`)'))
			->get();
		}
		foreach ($monthly_live as $key => $month){
			$monthly[]=$month;
			$monthly_array[$key][]= $month->years;
			$monthly_array[$key][]= $month->months;

		}
		if(!File::exists($xml_dir."/sitemap_article_archive.xml")) {
			$monthly_archive = \App\Models\article_archive::select(DB::raw('YEAR(publish_time) as years,MONTH(`publish_time`) as months, MAX( publish_time ) as last_edited'))
				->where('publish_time','<=',DB::raw('now()'))
				->groupBy(DB::raw('YEAR(publish_time)'))
				->groupBy(DB::raw('MONTH(`publish_time`)'))
				->get();
			foreach ($monthly_archive as $month){
				if (!in_array(array($month->years, $month->months), $monthly_array)) {
					$monthly[]=$month;
				}
			}
		}
		$view_content =View::make('theme::components.sitemap', ['monthly'=>$monthly] )->render();

		File::put($xml_file, $view_content);
	}
}
