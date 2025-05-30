<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

/* Website endpoints */
Route::group(['middleware' => 'website_api'], function() {
    Route::get('/article/{np_article_id}/{format_type?}', 'ApiController@getArticleData');
    Route::get('/articles/{format_type?}', 'ApiController@getArticlesData');
    Route::get('/widget/{widget_id}/{format_type?}', 'ApiController@getWidgetData');
    Route::get('/page/{format_type?}', 'ApiController@getAllPageData');
    Route::get('/page/{np_page_id}/{format_type?}', 'ApiController@getPageData');
    Route::get('/authors/{format_type?}', 'ApiController@getAuthorData');
    Route::get('/sections/{format_type?}', 'ApiController@getSectionData');
    Route::get('/search/{format_type?}', 'ApiController@getSearchData');
    Route::get('/custom_fields', 'ApiController@getCustomFieldsData');
    Route::get('/brands/{format_type?}', 'ApiController@getAllBrands');
    Route::get('/agencies/{format_type?}', 'ApiController@getAllAgencies');
    Route::get('/contacts/{agency_id}/{format_type?}', 'ApiController@getContactsRelatedToAgency');
    Route::post('/contact/send_mail/', 'ApiController@contactSendMail');
    Route::get('/menu_items/{np_menu_id}/{format_type?}', 'ApiController@getMenuItemsData');
});