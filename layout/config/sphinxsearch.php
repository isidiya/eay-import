<?php
return array(
    'host'    => \Layout\Website\Services\ThemeService::ConfigValue('host','127.0.0.1'),
    'port'    => \Layout\Website\Services\ThemeService::ConfigValue('port',9312),
    'timeout' => 30,
    'indexes' => array(
        \Layout\Website\Services\ThemeService::ConfigValue('WEBSITE_FULL','website_full') => false,
        \Layout\Website\Services\ThemeService::ConfigValue('WEBSITE_ARCHIVE_FULL','website_archive_full') => false,
        \Layout\Website\Services\ThemeService::ConfigValue('WEBSITE_DELTA','website_delta') => false
    )
);
