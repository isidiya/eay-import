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

class makkahnewSiteMap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'articles:makkahnewSiteMap {year?} {month?}';

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
        $this->CreateMainSiteMap();

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

        $articles_live = article::where('publish_time','<=', DB::raw(ThemeService::ConfigValue('GIVEN_TIME'))) 
            ->where(DB::raw('YEAR(publish_time)'),$year)
            ->where(DB::raw('MONTH(publish_time)'),$month)
            ->whereRaw(DB::raw('MONTH(publish_time)'))
            ->orderby('publish_time','DESC')
            ->get();

        foreach ($articles_live as $article_live){
            $articles[]= $article_live;
        }
        $view_content =View::make('theme::components.sitemap_older_months_articles', ['articles'=>$articles])->render();
        File::put($xml_file, $view_content); 

        $xml_dir = $this->default_folder;
        $xml_file =$xml_dir."/sitemap_latest.xml";
        if(!File::exists($xml_file)) {
            File::makeDirectory($xml_dir, $mode = 0777, true, true);
        }
        $articles_live = [];

        $articles_latest_live = article::where('publish_time','<=', DB::raw(ThemeService::ConfigValue('GIVEN_TIME')))
            ->where(DB::raw('YEAR(publish_time)'),$year)
            ->where(DB::raw('MONTH(publish_time)'),$month)
            ->whereRaw(DB::raw('MONTH(publish_time)'))
            ->orderby('publish_time','DESC') 
            ->get();

        foreach ($articles_latest_live as $article_latest_live){
            $articles_live[]= $article_latest_live;
        }
        $view_content =View::make('theme::components.sitemap_older_months_articles', ['articles'=>$articles_live])->render();
        File::put($xml_file, $view_content);
        
        //Authors
		$xml_dir = $this->default_folder ;
		$xml_file =$xml_dir."/sitemap_authors.xml";

		$authors = author::all();
		$view_content =View::make('theme::components.sitemap_authors', ['authors'=>$authors])->render();
		File::put($xml_file, $view_content);

		//Sections related to pages
		$xml_dir = $this->default_folder ;
		$xml_file =$xml_dir."/sitemap_sections.xml";
        $pages = page::where("page_section_id",">", 0)->get();
        $view_content =View::make('theme::components.sitemap_sections', ['pages'=>$pages])->render();
		File::put($xml_file, $view_content);

    }

    public function CreateGenericSiteMap(){
        $xml_dir = $this->default_folder;
        $xml_file =$xml_dir."/sitemap_older_months.xml";
        if(!File::exists($xml_file)) {
            File::makeDirectory($xml_dir, $mode = 0777, true, true);
        }
        $monthly=[];
        $monthly_array = [];
        $monthly_live = article::select(DB::raw('YEAR(publish_time) as years,MONTH(`publish_time`) as months,MAX( last_edited ) as last_edited'))
            ->where('publish_time','<=',DB::raw('now()'))
            ->groupBy(DB::raw('YEAR(publish_time)'))
            ->groupBy(DB::raw('MONTH(`publish_time`)'))
            ->get();
        foreach ($monthly_live as $key => $month){
            $monthly[]=$month;
            $monthly_array[$key][]= $month->years;
            $monthly_array[$key][]= $month->months;

        }
        $view_content =View::make('theme::components.sitemap_older_months', ['monthly'=>$monthly] )->render();

        File::put($xml_file, $view_content);
    }
    public function CreateMainSiteMap(){
        $xml_dir = $this->default_folder;
        $xml_file =$xml_dir."/index.xml";
        $view_content =View::make('theme::components.index_sitemap')->render();
        File::put($xml_file, $view_content);
    }
}
