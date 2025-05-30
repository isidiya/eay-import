<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\article;
use App\Models\related_articles;
use sngrl\SphinxSearch\SphinxSearch;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Layout\Website\Services\ThemeService;

class sphinxRelatedArticles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'related_articles:sphinx {days?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Related Articles By Sphinx';

    /**
     * Create a new command instance.
     *
     * @return void
     */

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
		$daysNumber = $this->argument('days');
		$this->getSphinxRelatedArticles($daysNumber);
    }
	public function getSphinxRelatedArticles($daysNumber=15) {
		header('Content-type: text/html; charset=utf-8');

//		$htmlEntities	= new HtmlEntities();
//		$daysNumber		= $htmlEntities->filter($this->params('daysNumber'));

		$re = '/<(?:[^>=]|=\'[^\']*\'|=""[^""]*""|=[^\'""][^\s>]*)*>/';

		$articles		= article::where("publish_time" ,">=",date("Y-m-d", strtotime("now -" .$daysNumber ." days")))->get();
		$flag_isfile = is_file(ThemeService::PublicPath().'/files/stopwords-ar.txt');
		foreach($articles as $articleInfo){
			$articleId = $articleInfo->np_article_id;

			if($flag_isfile){
				$file = fopen( ThemeService::PublicPath().'/files/stopwords-ar.txt',"r");
			}


			if(ThemeService::ConfigValue('WITH_BODY') > 0){
				$articleInfo->article_body= strip_tags($articleInfo->article_body);
				$articleInfo->article_body = substr($articleInfo->article_body, 0,ThemeService::ConfigValue('WITH_BODY'));
				$lastSpaceIndex = strrpos($articleInfo->article_body, ' ', -1);
				$articleInfo->article_body = substr($articleInfo->article_body, 0,$lastSpaceIndex);
			}
			$articleInfo->article_body = ' '. $articleInfo->article_body . ' ';
			$articleInfo->article_title = ' '. $articleInfo->article_title;

			//Remove HTML tags
			$matches =array();
			preg_match_all($re, $articleInfo->article_body, $matches, PREG_SET_ORDER, 0);
			foreach($matches as $match){
				$articleInfo->article_body = str_replace($match[0],'', $articleInfo->article_body);
			}
			//Remove Stop Words
			if($flag_isfile){
				while(!feof($file))
				{
					$stop_word=preg_replace( "/\r|\n/", "", fgets($file) );
					$articleInfo->article_title = str_replace($stop_word, ' ', $articleInfo->article_title);
					$articleInfo->article_body = str_replace($stop_word, ' ', $articleInfo->article_body);
				}
				fclose($file);
			}
			$articleInfo->article_title=str_replace('  ', ' ', $articleInfo->article_title);
			$articleInfo->article_title=str_replace('  ', ' ', $articleInfo->article_title);
			$articleInfo->article_title = trim($articleInfo->article_title);

			//Prepare Query to sphinx
			$query = $articleInfo->article_title ;
			if(ThemeService::ConfigValue('WITH_BODY') > 0){
				$articleInfo->article_body=str_replace('  ', ' ', $articleInfo->article_body);
				$articleInfo->article_body=str_replace('  ', ' ', $articleInfo->article_body);

				$articleInfo->article_body = trim($articleInfo->article_body);
				$query .= ' '. $articleInfo->article_body ;
			}


			$query = str_replace('  ' , ' ',$query);
			$query = str_replace('  ' , ' ',$query);
			$query = str_replace(' ' , '" | "',$query);
			$query ='"' .$query .'"';

			//Execute Sphinx
			$sphinx = new SphinxSearch();
			$sphinx->search('@article_title ' .$query, ThemeService::ConfigValue('WEBSITE_FULL').','. ThemeService::ConfigValue('WEBSITE_DELTA'));
			if(ThemeService::ConfigValue('WITH_SECTION') > 0){
				$sphinx->filter("section_id", explode(",", $articleInfo->section_id));
			}
			if(ThemeService::ConfigValue('WITH_AUTHOR') > 0 && $articleInfo->author_id > 0){
				$sphinx->filter("author_id", explode(",", $articleInfo->author_id));
			}

			if(!empty(ThemeService::ConfigValue('DATE_FROM'))&&  !empty(ThemeService::ConfigValue('DATE_TO'))){
				$sphinx->range( "publish_time", ThemeService::ConfigValue('DATE_FROM') , ThemeService::ConfigValue('DATE_TO') );

			}
			if(ThemeService::ConfigValue('ARTICLES_COUNT') > 0){
				$sphinx->limit(ThemeService::ConfigValue('ARTICLES_COUNT'));
			}else{
				$sphinx->limit(10);
			}
			$sphinx->setMatchMode(\Sphinx\SphinxClient::SPH_MATCH_EXTENDED2);
			$results = $sphinx->get();
$articles_id = array();
			//Retrieve Articles ID
			if(isset($results['matches'])){
				foreach ($results['matches'] as $values){
					$key =$values['attrs']['np_article_id'];
					if($key <> $articleId){
						$articles_id[] = $key;
					}
				}
				$json_array = implode(',',$articles_id);
				related_articles::updateOrCreate(['article_id' => $articleId],['related_ids' => $json_array]);
			}

		}
	}
}
