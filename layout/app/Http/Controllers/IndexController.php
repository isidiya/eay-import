<?php

namespace App\Http\Controllers;

use App\Models\page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Layout\Website\Models\PageBootstrap;
use Layout\Website\Services\PageService;
use Layout\Website\Services\ThemeService;
use Illuminate\Support\Facades\View;

class IndexController extends Controller {

    public function index(Request $request, $page_name = '') {
	
        die('index 1');
    }

    public function beauty(){
        die('index beauty 1');

    }


}
