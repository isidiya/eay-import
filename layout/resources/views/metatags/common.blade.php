<meta charset="utf-8"/>
<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
<meta name="ROBOTS" content="index,follow"/>
@if(\Layout\Website\Services\ThemeService::ConfigValue('LANGUAGE_META'))
<meta name="language" content="{{ \Layout\Website\Services\ThemeService::ConfigValue('LANGUAGE_META') }}"/>
@else
<meta name="language" content="{{ \Layout\Website\Services\ThemeService::ConfigValue('LANGUAGE') }}"/>
@endif
<meta name="copyright" content="{!! \Layout\Website\Services\ThemeService::ConfigValue('COPYRIGHT') !!}"/>
<meta name="format-detection" content="telephone=no"/>

<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />

