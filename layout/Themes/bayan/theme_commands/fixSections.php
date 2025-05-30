<?php

namespace Themes\bayan\theme_commands;
use App\Models\article_multi_section_archive;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Themes\bayan\controllers\BayanController;

class fixSections extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fixSections';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'php artisan fixSections';

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

    public function handle(){

        ini_set('memory_limit', '-1');

        $article_archive_migration = DB::table('article_archive_migration')
//            ->where('cms_article_id',920091)
            ->get();

        foreach($article_archive_migration as $article_archive_key => $article_archive){


            echo $article_archive_key .PHP_EOL;

            $article_multi_sections= [];

            $permalink_array = explode('/',$article_archive->permalink);

//        $article['section_name'] = $permalink_array[3];
//            $article['sub_section_name'] = $permalink_array[4];



            if(isset($permalink_array[3])){
                $section = \DB::table('section_mapping')->where('section',$permalink_array[3])->where('sub_section','')->first();
                if($section){
                    $article['section_name'] = $section->arabic_name;
                    $article_multi_sections[] = $section->arabic_name;

                }
            }

            if(isset($permalink_array[4])){
                $sub_section = \DB::table('section_mapping')->where('sub_section',$permalink_array[4])->where('sub_sub_section','')->first();
                if($sub_section){
                    $article['sub_section_name'] = $sub_section->arabic_name;
                    $article_multi_sections[] = $sub_section->arabic_name;
                }
            }

            if(isset($permalink_array[5])){
                $sub_section_2 = \DB::table('section_mapping')->where('sub_sub_section',$permalink_array[5])->where('sub_sub_sub_section','')->first();
                if($sub_section_2){
                    $article['sub_section_name'] = $sub_section_2->arabic_name;
                    $article_multi_sections[] = $sub_section_2->arabic_name;
                }
            }


            if(isset($permalink_array[6])){
                $sub_section_3 = \DB::table('section_mapping')->where('sub_sub_sub_section',$permalink_array[6])->first();
                if($sub_section_3){
                    $article['sub_section_name'] = $sub_section_3->arabic_name;
                    $article_multi_sections[] = $sub_section_3->arabic_name;
                }
            }

            foreach($article_multi_sections as $article_multi_section_key => $article_multi_section){
                if($article_multi_section_key==0 || isset($article_multi_sections[$article_multi_section_key + 1])){
                    article_multi_section_archive::insert([
                        'ams_article_id'=>$article_archive->cms_article_id,
                        'section_name'=>$article_multi_sections[0],
                        'sub_section_name'=>isset($article_multi_sections[$article_multi_section_key+1]) ? $article_multi_sections[$article_multi_section_key+1] : '',
                        'ams_order'=>$article_multi_section_key + 1,
                    ]);
                }

            }

            if(isset($article_multi_sections[0])){
                DB::table('article_archive_migration')->where('cms_article_id',$article_archive->cms_article_id)->update([
                    'section_name'=>$article_multi_sections[0],
                    'sub_section_name'=>(isset($article_multi_sections[count($article_multi_sections) - 1]) && count($article_multi_sections) - 1 > 0 ) ? $article_multi_sections[count($article_multi_sections) - 1] : '',
                ]);
            }


        }

    }



}
