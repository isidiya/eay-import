<?php

namespace Themes\bayan\theme_commands;
use App\Models\image_archive;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Themes\bayan\controllers\BayanController;

class updateImagePath extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updateImagePath';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'php artisan updateImagePath';

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
            ,'article_archive.image_path as article_image_path'
            ,'article_archive.article_custom_fields'
            ,'image_archive.cms_image_id'
            ,'image_archive.np_related_article_id'
            ,'image_archive.image_path'
            ,'image_archive.image_custom_fields')
            ->join('image_archive','article_archive.cms_article_id','image_archive.np_related_article_id')
            ->get();


//        $timezone = 'Asia/Dubai';
//
//        $article['publish_time'] = $article_archive['published'];
//        $published = new \Carbon\Carbon($article_archive['published']);
//        $published->setTimezone($timezone);
//        $article['publish_time_1'] = $published;



        foreach($article_archives as $key => $article_archive){
            echo $article_archive->cms_article_id . PHP_EOL;
            echo $key . PHP_EOL;

            if(!empty($article_archive->image_custom_fields)){
                $image_custom_fields = json_decode($article_archive->image_custom_fields);
                $image_date = $article_archive->publish_time;
                $image_date = Carbon::parse($image_date)->addHours(4);;
                $image_extension  = pathinfo($article_archive->image_path, PATHINFO_EXTENSION);
                $image_path = 'albayan/uploads/archives/images/' . $image_date->format('Y') . '/' . $image_date->format('m') . '/' .  $image_date->format('d') .'/' . str_replace('1.','',$image_custom_fields->id). '.'.$image_extension ;
                image_archive::where('cms_image_id',$article_archive->cms_image_id)->update([
                    'image_path'=>$image_path
                ]);

            }

        }




    }


}
