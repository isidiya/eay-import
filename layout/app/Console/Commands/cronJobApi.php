<?php

namespace App\Console\Commands;

use App\Http\Controllers\CommonController;
use Illuminate\Console\Command;
use App\Models\article;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Layout\Website\Services\ThemeService;
use SimpleXMLElement;

class cronJobApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cronjob:cachefile {api_url?} {file_name?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate CronJob For thirdparty urls';

    /**
     * Create a new command instance.
     *
     * @return void
     */
	protected $default_folder =  '';

    public function __construct()
    {
		$this->default_folder = ThemeService::PublicPath().'/cache';
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    
	public function handle()
    {
		$api_url = $this->argument('api_url');
		$file_name = $this->argument('file_name');
        $file_name = str_replace('today', date("Ymd"),$file_name);
		if(empty($file_name)){
			$file_name = 'api_result.txt';
		}
		if(!empty($api_url)){
			$result = self::curl_get_result($api_url);
			if(!empty($result)){
				$file = $this->default_folder ."/". $file_name;
				if(!File::exists($file)) {
					File::makeDirectory($this->default_folder, $mode = 0777, true, true);
				}
				File::put($file, $result);
				echo "Parse Complete";
			}else{
				echo "Empty Result";
			}
		}else{
			echo "Please put API_URL";
		}
    }
	public static function curl_get_result($url) {
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, 4);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}
}
