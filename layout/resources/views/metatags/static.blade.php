@php $page = \Layout\Website\Services\PageService::Page() @endphp

<meta name="news_keywords" content="{{ \Layout\Website\Services\ThemeService::ConfigValue('META_NEWSKEY') }}" />
<meta name="description" content="{{ \Layout\Website\Services\ThemeService::ConfigValue('META_DESC') }}" /> 
<meta name="keywords" content="{{ \Layout\Website\Services\ThemeService::ConfigValue('META_KEY') }}" />

<meta property="og:title" content="{{ $page->page_title }}"/>
<meta property="og:type" content="website" />
<meta property="og:description" content="{{ \Layout\Website\Services\ThemeService::ConfigValue('DESCRIPTION') }}" />
<meta property="og:url" content="{{ url("/")."/".$page->page_title }}" />
<meta property="og:site_name" content="{{ \Layout\Website\Services\ThemeService::ConfigValue('NEWSPAPER_NAME') }} "/>
<meta property="og:image" content="{{ url("/")."/theme_".\Layout\Website\Services\ThemeService::ConfigValue("THEME_NAME")."/images/logo.png"}}" />

<meta name="DC.title" lang="{{ \Layout\Website\Services\ThemeService::ConfigValue('LANGUAGE') }}" content="{{ $page->page_title }}" />
<meta name="DC.description" lang="{{ \Layout\Website\Services\ThemeService::ConfigValue('LANGUAGE') }}" content="{{ \Layout\Website\Services\ThemeService::ConfigValue('DESCRIPTION') }}"/>
<meta name="DC.date" scheme="W3CDTF" content="{{ date('Y-m-d') }}" />
<meta name="DC.date.issued" scheme="W3CDTF" content="{{ date('Y-m-d') }}" />
<meta name="DC.creator" content="{{ \Layout\Website\Services\ThemeService::ConfigValue('NEWSPAPER_NAME') }}" />
<meta name="DC.publisher" content="{{ \Layout\Website\Services\ThemeService::ConfigValue('PUBLISHER') }}" />
<meta name="DC.language" scheme="RFC1766" content="{{ \Layout\Website\Services\ThemeService::ConfigValue('LANGUAGE') }}" />

<link href="{{ url("/")."/".$page->page_title }}" rel="canonical" >


