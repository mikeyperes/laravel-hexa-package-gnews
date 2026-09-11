<?php
return [
    'version' => '2.0.6',

    /*
    |--------------------------------------------------------------------------
    | Default country filter
    |--------------------------------------------------------------------------
    |
    | Comma separated ISO 3166-1 alpha-2 codes sent as GNews' `country`
    | parameter. Empty returns worldwide coverage.
    |
    */

    'default_country' => env('GNEWS_DEFAULT_COUNTRY', 'us,gb,ca,au'),

];
