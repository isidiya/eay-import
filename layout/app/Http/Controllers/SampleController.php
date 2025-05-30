<?php

namespace App\Http\Controllers;

use App\Models\article;
use App\Models\article_multi_section;
use App\Models\image;
use App\Models\page;
use App\Models\section;
use Illuminate\Http\Request;
use Layout\Website\Models\PageBootstrap;
use Layout\Website\Services\MenuService;
use Layout\Website\Services\PageService;
use Layout\Website\Services\WidgetService;

class SampleController extends Controller
{
	public	function __construct() {

	}

	public	function index(Request $request) {
		return view('theme::pages.__web_sample',[]);
	}
}