<?php

namespace Themes\bayan\theme_commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Themes\bayan\controllers\BayanController;
use Illuminate\Support\Facades\Storage;
use App\Models\article_archive;

class updateArticlesSection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updateArticlesSection';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'php artisan updateArticlesSection';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    protected $default_folder =  '';
    protected $api_url ='';
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */


	public function handle()
    {

	$article_archives = article_archive::whereRaw('YEAR(publish_time) IN (?)', [2021])->get();
	//$article_archives = article_archive::where('cms_article_id',2339096)->get();
        foreach($article_archives as $article_archive){
            BayanController::updateArticleSections($article_archive);
        }
        dd('end');

    }


}
