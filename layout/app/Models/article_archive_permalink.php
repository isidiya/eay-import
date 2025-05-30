<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Layout\Website\Helpers\ImageHelper;
use Layout\Website\Helpers\UrlHelper;
use Layout\Website\Services\ThemeService;
use App\Http\Controllers\CommonController;
use \Illuminate\Support\Arr;
use Layout\Website\Services\WidgetService;

class article_archive_permalink extends Model {

    protected $table = "article_archive_permalink";
    public $timestamps = false;
}
