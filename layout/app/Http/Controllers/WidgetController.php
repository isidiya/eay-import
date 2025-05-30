<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Layout\Website\Services\WidgetService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

class WidgetController extends Controller {

    public function __construct() {

    }

		 public
		function firstimage(Request $request) {
		$articles = \App\Models\article::select("np_article_id","image_path")
			->where("sinar",1)
			->get();

		foreach ($articles as $article) {
			$data = stripslashes($article->image_path);
			$data = json_decode($data,true);
			if(isset($data["media_type"]) && !is_numeric(strpos($data["image_path"], "https")) && $data["media_type"] == 0){
				$data["image_path"] = "https://www.sinarharian.com.my/". $data["image_path"];
				$data = json_encode($data);
				$data = addslashes($data);
				\App\Models\article::where("np_article_id", $article->np_article_id)
					->update(["image_path"=>$data ]);
				echo $article->np_article_id. "<br>";
			}
		}
		echo "done";
	}

	public function index(Request $request, $widget_id = 0) {
		$language = \Layout\Website\Services\ThemeService::ConfigValue('LANGUAGE');
        View::share('language', $language);
		$widget = \App\Models\widget::find_np($widget_id);
		$widget = WidgetService::widget_by_widget_data($widget)->render();
		$html = str_replace(["\n\r","\n","\r"], "", $widget);
		$path = \Layout\Website\Services\ThemeService::PublicPath()."/widget/";
		Storage::disk('theme_path')->put($widget_id.'.html', $html);
		echo $html;
	}
}
