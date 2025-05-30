<?php

use App\Http\Controllers\IndexController;
use Themes\jamalouki\controllers\JamaloukiController;

Route::get('/clear-cache', function() {
    $exitCode = Artisan::call('cache:clear');
    $exitCode = Artisan::call('view:clear');
    $exitCode = Artisan::call('config:clear');
    return redirect('/');
});






