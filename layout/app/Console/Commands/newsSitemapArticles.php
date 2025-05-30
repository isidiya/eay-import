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

class newsSitemapArticles extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'newsArticles:sitemap';

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
    protected $default_folder = '';

    public function __construct() {
        $this->default_folder = ThemeService::PublicMainPath() . '/sitemaps';

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $xml_dir = $this->default_folder;
        $xml_file = $xml_dir . "/newsSitemap.xml";
        if (!File::exists($xml_file)) {
            File::makeDirectory($xml_dir, $mode = 0777, true, true);
        }
        $articles = article::where('publish_time', '>=', DB::raw('NOW()-INTERVAL 2 DAY'))->where('publish_time','<=', DB::raw('NOW()'))->orderBy('publish_time', 'desc')->get();
        if (count($articles) < 50) {
             $articles = article::where('publish_time','<=', DB::raw('NOW()'))->orderBy('publish_time', 'desc')->limit(50)->get();
        }
        $view_content = View::make('theme::components.news_sitemap_articles', ['articles' => $articles])->render();
        File::put($xml_file, $view_content);
    }

}
