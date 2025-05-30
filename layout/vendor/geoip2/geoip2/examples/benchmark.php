<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require  'C:/htdocs/layout_migration/vendor/autoload.php';

use GeoIp2\Database\Reader;
$reader = new Reader('C:/htdocs/layout_migration/database/mmdb/GeoLite2-Country.mmdb');
$country = $reader->country('121.58.212.162');
$countryCode = $country->country->isoCode;
$countryName =$country->country->names['en'];
