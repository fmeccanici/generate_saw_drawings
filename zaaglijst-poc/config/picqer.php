<?php

return [
    'api_key' => env('PICQER_API_KEY'),
    'datetime_format' => env('PICQER_DATETIME_FORMAT'),
    'subdomain' => env("PICQER_SUBDOMAIN"),
    'backorders_cache_key' => env('PICQER_BACKORDER_CACHE_KEY', 'picqer.backorders')
];
