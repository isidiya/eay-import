<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\article;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Layout\Website\Services\ThemeService;

class weatherApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'weatherApi:weatherData';

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
    public function handle() {
        $file_name = 'api-weather.json';

            $point = array('arCountry' => 'دبي', 'long' => 55.2962, 'lat' => 25.2684, 'flag' => '1');
            $Points[] = $point;
            $point = array('arCountry' => 'أبو ظبي', 'long' => 54.3706, 'lat' => 24.4748, 'flag' => '1');
            $Points[] = $point;
            $point = array('arCountry' => 'الشارقة', 'long' => 55.3895, 'lat' => 25.3585, 'flag' => '1');
            $Points[] = $point;
            $point = array('arCountry' => 'رأس الخيمة', 'long' => 55.9432, 'lat' => 25.7895, 'flag' => '1');
            $Points[] = $point;
            $point = array('arCountry' => 'عجمان', 'long' => 55.4451, 'lat' => 25.3937, 'flag' => '1');
            $Points[] = $point;
            $point = array('arCountry' => 'الفجيرة', 'long' => 56.3344, 'lat' => 25.1221, 'flag' => '1');
            $Points[] = $point;
            $point = array('arCountry' => 'أم القيوين', 'long' => 55.75, 'lat' => 25.5, 'flag' => '1');
            $Points[] = $point;
            
            
            $point = array('arCountry' => 'الرياض', 'long' => 46.72, 'lat' => 24.68, 'flag' => '2');
            $Points[] = $point;
            $point = array('arCountry' => 'الكويت', 'long' =>  47.99, 'lat' => 29.37, 'flag' => '2');
            $Points[] = $point;
            $point = array('arCountry' => 'مسقط', 'long' =>  58.54, 'lat' => 23.61, 'flag' => '2');
            $Points[] = $point;
            $point = array('arCountry' => 'المنامة', 'long' =>  50.60, 'lat' => 26.20, 'flag' => '2');
            $Points[] = $point;
            $point = array('arCountry' => 'القدس', 'long' =>  35.21, 'lat' => 31.77, 'flag' => '2');
            $Points[] = $point; 
            $point = array('arCountry' => 'عمّان', 'long' =>  35.93, 'lat' => 31.96, 'flag' => '2');
            $Points[] = $point;
            $point = array('arCountry' => 'بيروت', 'long' =>  35.49, 'lat' => 33.88, 'flag' => '2');
            $Points[] = $point;
            
            
            $point = array('arCountry' => 'القاهرة', 'long' =>  31.34, 'lat' => 30.04, 'flag' => '3');
            $Points[] = $point;
            $point = array('arCountry' => 'الجزائر', 'long' =>  3.08, 'lat' => 36.72, 'flag' => '3');
            $Points[] = $point;
            $point = array('arCountry' => 'الرباط', 'long' =>  -6.84, 'lat' => 34.02, 'flag' => '3');
            $Points[] = $point;
            $point = array('arCountry' => 'الخرطوم', 'long' =>  32.52, 'lat' => 15.56, 'flag' => '3');
            $Points[] = $point;
            $point = array('arCountry' => 'بكين', 'long' =>  116.36, 'lat' => 39.91, 'flag' => '3');
            $Points[] = $point;
            $point = array('arCountry' => 'لندن', 'long' =>  -0.11, 'lat' => 51.50, 'flag' => '3');
            $Points[] = $point;
            $point = array('arCountry' => 'واشنطن', 'long' =>  	-120.74, 'lat' => 	47.75, 'flag' => '3');
            $Points[] = $point;

            foreach ($Points as $point) {
                $lat = $point['lat'];
                $long = $point['long'];
                $flag = $point['flag'];
                $arCountry = $point['arCountry'];
                $ch = curl_init("http://api.openweathermap.org/data/2.5/weather?lat=$lat&lon=$long&appid=9a419bf8284e1da365272888e64bc127&units=metric");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $x = curl_exec($ch);
                $Y = json_decode($x);
                $weather = $Y->weather;
                $icon = $weather[0]->icon;
                $main = $Y->main;
                $temp = $main->temp;
                $description=$weather[0]->description;
                $temp_min = $main->temp_min;
                $temp_max = $main->temp_max;

                $resultArray[] = array(
                    'icon' => $icon,
                    'temp' => $temp,
                    'temp_min' => ceil($temp_min),
                    'temp_max' => ceil($temp_max),
                    'lat' => $lat,
                    'long' => $long,
                    'arCountry' => $arCountry,
                    'label' => $arCountry,
                    'flag' => $flag,
                    'description'=>$description
                );
                curl_close($ch);
            }
            $result = json_encode($resultArray);

            if (!empty($result)) {
                $file = $this->default_folder . "/" . $file_name;
                if (!File::exists($file)) {
                    File::makeDirectory($this->default_folder, $mode = 0777, true, true);
                }
                File::put($file, $result);
            } else {
                echo "Empty Result";
            }
    }

}
