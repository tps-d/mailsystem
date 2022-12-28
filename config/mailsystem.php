<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Supported Locale
    |--------------------------------------------------------------------------
    |
    | This array holds the list of supported locale for Sendportal.
    |
    */
    'locale' => [
        'supported' => [
            'en' => ['name' => 'English', 'native' => 'English'],
            'zh' => ['name' => 'Chinese', 'native' => 'Chinese']
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Auth Settings
    |--------------------------------------------------------------------------
    |
    | Configure the Sendportal authentication functionality.
    |
    */
    'auth' => [
        'register' => env('SYSTEM_REGISTER', false),
        'password_reset' => env('SYSTEM_PASSWORD_RESET', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | API Throttle Settings
    |--------------------------------------------------------------------------
    |
    | Configure the Sendportal API throttling.
    | For more information see https://laravel.com/docs/master/routing#rate-limiting
    |
    */
    'throttle_middleware' => 'throttle:' . env('SYSTEM_THROTTLE_MIDDLEWARE', '60,1'),
];