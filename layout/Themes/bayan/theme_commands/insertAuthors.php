<?php

namespace Themes\kuwaittimes\theme_commands;
use App\Http\Controllers\NpApiController;
use App\Models\section;
use App\Models\sub_section;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Layout\Website\Services\ThemeService;

class insertAuthors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'insertAuthors';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'php artisan insertAuthors';

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

    }



}
