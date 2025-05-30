<!DOCTYPE html>
<html lang="{{ \Layout\Website\Services\ThemeService::ConfigValue("LANGUAGE") }}" xml:lang="{{ \Layout\Website\Services\ThemeService::ConfigValue("LANGUAGE") }}">
<head>
    @yield('head_insert')
    @if(View::exists('theme::metatags.common'))
        @include("theme::metatags.common")
    @else
        @include("metatags.common")
    @endif
    @hasSection("theme_metatags")
        @yield('theme_metatags')
    @endif
    @switch(\Layout\Website\Services\PageService::PageType())
        @case(\Layout\Website\Services\PageService::page_type_index)
            @include('metatags.index')
            @include('theme::metatags.index')
            @break
        @case(\Layout\Website\Services\PageService::page_type_article)
            @include('metatags.article')
			@include('theme::metatags.article')
            @break
        @case(\Layout\Website\Services\PageService::page_type_static)
            @include('metatags.static')
            @break
    @endswitch
    @yield('extra_js')
    @yield('css_links')
    @yield('widget_css_links')
    @yield('page_style')
</head>
@yield('content')
<input type="hidden"  name="csrf-token" content="{{ csrf_token() }}"/>
@yield('js_links')
@yield('widget_js_links')
@yield('page_script')
</body>
</html>
<?php
if(Layout\Website\Services\ThemeService::ConfigValue("QUERY_LOG") && isset($_GET['query_log'])){
	$queries = Illuminate\Support\Facades\DB::getQueryLog();
	App\Http\Controllers\CommonController::WriteToTestFile($queries,'ab+',Layout\Website\Services\ThemeService::ConfigValue("QUERY_LOG"));
}
?>
