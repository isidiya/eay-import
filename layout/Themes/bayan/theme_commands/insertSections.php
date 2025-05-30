<?php

namespace Themes\kuwaittimes\theme_commands;
use App\Http\Controllers\NpApiController;
use App\Models\section;
use App\Models\sub_section;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Layout\Website\Services\ThemeService;

class insertSections extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'insertSections';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'php artisan insertSections';

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

        $xtmrl_term_taxonomy  = DB::table('xtmrl_term_taxonomy')
            ->join('xtmrl_terms', 'xtmrl_terms.term_id', '=', 'xtmrl_term_taxonomy.term_id')
            ->where('xtmrl_term_taxonomy.taxonomy','category')
            ->orderBy('parent','ASC')
            ->get();


        foreach($xtmrl_term_taxonomy as $xtmrl_term_taxonomy_key => $xtmrl_term_taxonomy_list){
            echo $xtmrl_term_taxonomy_key . PHP_EOL;
            if($xtmrl_term_taxonomy_list->parent == 0){

                $section_found = DB::connection('mysql2')->table('section')->where('section_name',$xtmrl_term_taxonomy_list->name)->first();
                if(empty($section_found)){
                    DB::connection('mysql2')->table('section')->insert([
                        'section_name' =>$xtmrl_term_taxonomy_list->name,
                        'old_section_id' =>$xtmrl_term_taxonomy_list->term_id,
                        'np_section_id' =>$xtmrl_term_taxonomy_list->term_id
                    ]);
                }
            }else{
                $section = DB::connection('mysql2')->table('section')->where('old_section_id',$xtmrl_term_taxonomy_list->parent)->first();
                $sub_section = DB::connection('mysql2')->table('sub_section')->where('sub_section_name',$xtmrl_term_taxonomy_list->name)->first();
                if(empty($sub_section)){
                    DB::connection('mysql2')->table('sub_section')->insert([
                        'sub_section_name' => $xtmrl_term_taxonomy_list->name,
                        'old_sub_section_id' => $xtmrl_term_taxonomy_list->term_id,
                        'np_sub_section_id' => $xtmrl_term_taxonomy_list->term_id,
                        'section_id' => $section->cms_section_id,
                    ]);
                }
            }
        }
    }



}
