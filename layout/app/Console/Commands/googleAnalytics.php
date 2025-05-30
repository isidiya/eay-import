<?php

namespace App\Console\Commands;

use App\Http\Controllers\CommonController;
use Illuminate\Console\Command;
use App\Models\article;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Layout\Website\Services\ThemeService;

class googleAnalytics extends Command {

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected
		$signature = 'analytics:google {json_file?} {view_id?} {from_date?} {to_date?} {limit?} {dim_article_id?} {dim_section_name?} {section_name?}';

	/**
	 * The console command description.
	 *
	 * @var stringga:
	 */
	protected
		$description = 'Generate CronJob to get analytics data';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	protected $default_folder = '';
	protected $json_file = '';
	protected $view_id = '';
	protected $from_date = 'today';
	protected $to_date = 'today';
	protected $limit =50;
	protected $dim_article_id ="ga:dimension2";
	protected $dim_section_name ="ga:dimension1";
	protected $section_name ="";


	public
		function __construct() {
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public
		function handle() {
		// Use the developers console and download your service account
		// credentials in JSON format. Place them in this directory or
		// change the key file location if necessary.
		$this->json_file = $this->argument('json_file');
		// Replace with your view ID, for example XXXX.
		$this->view_id = $this->argument('view_id');
		$this->from_date = $this->argument('from_date');
		$this->to_date = $this->argument('to_date');
		$this->limit = $this->argument('limit');
		$this->dim_article_id = $this->argument('dim_article_id');
		$this->dim_section_name = $this->argument('dim_section_name');
		$this->section_name = $this->argument('section_name');
		$analytics = self::initializeAnalytics();
		$response = self::getReport($analytics);
		self::printResults($response);
	}

	public function initializeAnalytics() {

		$KEY_FILE_LOCATION = __DIR__ . '/jsons/' .$this->json_file ;

		// Create and configure a new client object.
		$client = new \Google_Client();
		$client->setApplicationName("Hello Analytics Reporting");
		$client->setAuthConfig($KEY_FILE_LOCATION);
		$client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
		$analytics = new \Google_Service_AnalyticsReporting($client);
		return $analytics;
	}

	/**
	 * Queries the Analytics Reporting API V4.
	 *
	 * @param service An authorized Analytics Reporting API V4 service object.
	 * @return The Analytics Reporting API V4 response.
	 */
	public function getReport($analytics) {



		// Create the DateRange object.
		$dateRange = new \Google_Service_AnalyticsReporting_DateRange();
		//$dateRange->setStartDate("2daysAgo");
		$dateRange->setStartDate($this->from_date);
		$dateRange->setEndDate($this->to_date);

		// Create the Metrics object.
		$sessions = new \Google_Service_AnalyticsReporting_Metric();
		$sessions->setExpression("ga:sessions");
		$sessions->setAlias("sessions");

		$sessions1 = new \Google_Service_AnalyticsReporting_Metric();
		$sessions1->setExpression("ga:pageviews");
		$sessions1->setAlias("pageviews");


//Create the Dimensions cms_article_id.
		$browser = new \Google_Service_AnalyticsReporting_Dimension();
		$browser->setName($this->dim_article_id);

		$browser1 = new \Google_Service_AnalyticsReporting_Dimension();
		$browser1->setName($this->dim_section_name);

//order
		$ordering = new \Google_Service_AnalyticsReporting_OrderBy();
		$ordering->setFieldName("ga:pageviews");
		$ordering->setOrderType("VALUE");
		$ordering->setSortOrder("DESCENDING");

// Create Dimension Filter.
		$dimensionFilter = new \Google_Service_AnalyticsReporting_DimensionFilter();
		$dimensionFilter->setDimensionName("ga:dimension1");
		$dimensionFilter->setOperator("EXACT");
		$dimensionFilter->setExpressions(array($this->section_name));
// Create the DimensionFilterClauses
		$dimensionFilterClause = new \Google_Service_AnalyticsReporting_DimensionFilterClause();
		$dimensionFilterClause->setFilters(array($dimensionFilter));

		// Create the ReportRequest object.
		$request = new \Google_Service_AnalyticsReporting_ReportRequest();
		$request->setViewId($this->view_id);
		$request->setDateRanges($dateRange);
		$request->setMetrics(array($sessions, $sessions1));
		$request->setDimensionFilterClauses(array($dimensionFilterClause));
		$request->setDimensions(array($browser,$browser1));
		$request->setOrderBys($ordering);
		$request->setPageSize($this->limit);

		$body = new \Google_Service_AnalyticsReporting_GetReportsRequest();
		$body->setReportRequests(array($request));
		return $analytics->reports->batchGet($body);
	}

	/**
	 * Parses and prints the Analytics Reporting API V4 response.
	 *
	 * @param An Analytics Reporting API V4 response.
	 */
	public function printResults($reports) {

		for ($reportIndex = 0; $reportIndex < count($reports); $reportIndex++) {
			$report = $reports[$reportIndex];
			$header = $report->getColumnHeader();
			$dimensionHeaders = $header->getDimensions();
			$metricHeaders = $header->getMetricHeader()->getMetricHeaderEntries();
			$rows = $report->getData()->getRows();
			$section_id = \App\Models\section::find_np_by_name(strtolower($this->section_name))->np_section_id;

			if(count($rows) > 0){
				\App\Models\article_most_read::where("from_date" ,$this->from_date )->where("np_section_id",$section_id)->delete();
			}

			for ($rowIndex = 0; $rowIndex < count($rows); $rowIndex++) {
				$cms_id = 0;
				$pageviews = 0;
				$sessions = 0;
				$row = $rows[$rowIndex];
				$dimensions = $row->getDimensions();
				$metrics = $row->getMetrics();
				for ($i = 0; $i < count($dimensionHeaders) && $i < count($dimensions); $i++) {
					print($dimensionHeaders[$i] . ": " . $dimensions[$i] . "\n");

					if($dimensionHeaders[$i] == "ga:dimension2"){
						$cms_id =$dimensions[$i];
					}

				}

				for ($j = 0; $j < count($metrics); $j++) {
					$values = $metrics[$j]->getValues();
					for ($k = 0; $k < count($values); $k++) {
						$entry = $metricHeaders[$k];
						print($entry->getName() . ": " . $values[$k] . "\n");
						if($entry->getName() == "pageviews"){
							$pageviews = $values[$k];
						}
						if($entry->getName() == "sessions"){
							$sessions = $values[$k];
						}
					}
				}
				if($cms_id > 0 && $pageviews > 0 && $sessions > 0 ){
					\App\Models\article_most_read::updateOrCreate(
					['np_article_id'=>$cms_id,"page_views" =>$pageviews,"sessions" =>$sessions,"from_date" => $this->from_date,"np_section_id"=>$section_id],
					['np_article_id'=>$cms_id,"page_views" =>$pageviews,"sessions" =>$sessions,"from_date" => $this->from_date,"np_section_id"=>$section_id]);
				}
			}
		}
	}

}
