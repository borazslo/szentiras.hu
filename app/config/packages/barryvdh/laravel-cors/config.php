<?php

return [

    /*
     |--------------------------------------------------------------------------
     | Laravel CORS Defaults
     |--------------------------------------------------------------------------
     |
     | The defaults are the default values applied to all the paths that match,
     | unless overridden in a specific URL configuration.
     | If you want them to apply to everything, you must define a path with ^/.
     |
     | allow_origin and allow_headers can be set to * to accept any value,
     | the allowed methods however have to be explicitly listed.
     |
     */
    'defaults' => [
        'allow_credentials' => false,
        'allow_origin' => [],
        'allow_headers' => [],
        'allow_methods' => [],
        'expose_headers' => [],
        'max_age' => 0,
    ],

    'paths' => [
        '^/api/' => [
            'allow_origin' => ['*'],
            'allow_headers' => ['*'],
            'allow_methods' => ['POST', 'PUT', 'GET', 'DELETE', 'OPTIONS'],
            'max_age' => 3600,
        ],
    ],

];
