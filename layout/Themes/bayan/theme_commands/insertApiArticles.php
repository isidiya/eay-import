<?php
namespace Themes\bayan\theme_commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Themes\bayan\controllers\BayanController;
use Illuminate\Support\Facades\Storage;

class insertApiArticles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'insertApiArticles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'php artisan insertApiArticles';

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


//	$article_path= '/var/www/html/s3-byn-layout-data/article-json/2020/01/01/article-3740855.json';
//	BayanController::insertArticle($article_path);
//	dd('end');

        ini_set('memory_limit', '-1');

        $dir = '/var/polopoly-import/article-json/';
        $items = glob($dir . '/*');
	foreach ($items as $sub_dir) {

            $sub_dir_array = explode('/',$sub_dir);
            $year = $sub_dir_array[count($sub_dir_array) - 1];

            $sub_items = glob($sub_dir . '/*');
            foreach($sub_items as $sub_dir_2){
                $sub_dir_2_array = explode('/',$sub_dir_2);
                $month = $sub_dir_2_array[count($sub_dir_2_array) - 1];
//                    if((int)$year < 2024 || ((int)$year == 2024 && (int)$month < 10)){
   //                 if((int)$year != 2011){
     //                 continue;
       //        }

// dd(((int)$year == 2024 && (int)$month < 10),$year,$month);

                echo 'yaer: ' . $year . '& month: ' . $month . PHP_EOL;

                $sub_items_2 = glob($sub_dir_2 . '/*');
                foreach($sub_items_2 as $sub_dir_3){
                    $sub_items_3 = glob($sub_dir_3 . '/*');
                    foreach($sub_items_3 as $article_path){
                        BayanController::insertArticle($article_path);
                    }
                }
            }
        }

    }


}
