@extends('layouts.default_base')

@section('extra_js')

    <!-- THEME JS links -->
    @yield('theme_extra_js')
@endsection

@section('css_links')
    <!-- Common CSS links -->
    @include('common.css_includes')

    <!-- THEME CSS links -->
    @yield('theme_css_links')
@endsection

@section('js_links')
    <!-- Common JS links -->
    @include('common.js_includes')

    <!-- THEME JS links -->
    @yield('theme_js_links')
@endsection

@section('content')
    <body dir="{{ trans('application.language_direction') }}">
            <!-- Main THEME Content -->
            <!-- page content -->
            @yield('theme_content')
            <!-- /page content -->
@endsection

@section('widget_css_links')
    <!-- Widget CSS links -->
    @foreach(\Layout\Website\Services\WidgetService::stackCss() as $css_file_path)
        <link type="text/css" rel="stylesheet" href="{{ $css_file_path }}"/>
    @endforeach
@endsection

@section('widget_js_links')
    <!-- Widget JS links -->
    @foreach(\Layout\Website\Services\WidgetService::stackJs() as $js_file_path)
        <script type="text/javascript" src="{{ $js_file_path }}" ></script>
    @endforeach
@endsection