<?php

use HolidayAPI\Client;

require_once 'vendor/autoload.php';
$key = 'ce34b216-1666-4400-914f-b07660ed4ede';
$holiday_api = new Client(['key' => $key]);

try {
    // Fetch supported countries and subdivisions
    $countries = $holiday_api->countries();

    // Fetch supported languages
    $languages = $holiday_api->languages();

    // Fetch holidays with minimum parameters
    $holidays = $holiday_api->holidays([
      'country' => 'US',
      'year' => 2019,
    ]);

    var_dump($countries, $languages, $holidays);
} catch (Exception $e) {
    var_dump($e);
}