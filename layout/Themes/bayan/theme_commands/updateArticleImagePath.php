<?php

namespace Themes\bayan\theme_commands;
use App\Models\article_archive;
use App\Models\image_archive;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Themes\bayan\controllers\BayanController;

class updateArticleImagePath extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updateArticleImagePath';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'php artisan updateArticleImagePath';

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
        ini_set('memory_limit', '-1');

        $article_archives = DB::table('article_archive')->select(
            'article_archive.cms_article_id'
            ,'article_archive.old_article_id'
            ,'article_archive.publish_time'
            ,'article_archive.image_path'
            ,'article_archive.article_custom_fields')
            ->where('cms_article_id',1)
            ->get();

//        $timezone = 'Asia/Dubai';
//
//        $article['publish_time'] = $article_archive['published'];
//        $published = new \Carbon\Carbon($article_archive['published']);
//        $published->setTimezone($timezone);
//        $article['publish_time_1'] = $published;


        foreach($article_archives as $key => $article_archive){

            if(empty($article_archive->image_path)){
                continue;
            }
            $image_path = '';
//            echo $article_archive->cms_article_id . PHP_EOL;
 //           echo $key . PHP_EOL;
dd(json_decode($article_archive->article_custom_fields));
            if(!empty($article_archive->article_custom_fields)){
                $article_custom_fields = json_decode($article_archive->article_custom_fields);

                if(isset($article_custom_fields->topImages[0])){
                    $image_custom_fields = $article_custom_fields->topImages[0];
                    $image_date = $article_archive->publish_time;
                    $image_date = Carbon::parse($image_date)->addHours(4);;
                    $image_extension  = pathinfo($image_custom_fields->link, PATHINFO_EXTENSION);
                    $image_path = 'emaratalyoum/uploads/archives/images/' . $image_date->format('Y') . '/' . $image_date->format('m') . '/' .  $image_date->format('d') .'/' . str_replace('1.','',$image_custom_fields->id). '.'.$image_extension ;
                }else{
                    $image_archive = image_archive::where('np_related_article_id',$article_archive->cms_article_id)->orderBy('cms_image_id','ASC')->first();
                    if(!empty($image_archive)){
                        $image_custom_fields = json_decode($image_archive->image_custom_fields);
                        $image_date = $article_archive->publish_time;
                        $image_date = Carbon::parse($image_date)->addHours(4);;
                        $image_extension  = pathinfo($image_archive->image_path, PATHINFO_EXTENSION);
                        $image_path = 'emaratalyoum/uploads/archives/images/' . $image_date->format('Y') . '/' . $image_date->format('m') . '/' .  $image_date->format('d') .'/' . str_replace('1.','',$image_custom_fields->id). '.'.$image_extension ;
                    }
                }


                if($image_path){
                    $article_image_path = json_decode(stripslashes($article_archive->image_path));
                    if(empty($article_image_path->image_path)){
                    }
                    $article_image_path->image_path = $image_path;
                    $article_image_path = json_encode($article_image_path);
                    article_archive::where('cms_article_id',$article_archive->cms_article_id)->update([
                        'image_path'=>$article_image_path
                    ]);
                }


            }

        }




    }


}
