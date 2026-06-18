<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Bootstrap Administrator Email
    |--------------------------------------------------------------------------
    |
    | The only email address allowed to receive the initial setup code and
    | become the platform's first administrator.
    |
    */

    'admin_email' => env('SETUP_ADMIN_EMAIL'),

    /*
    |--------------------------------------------------------------------------
    | Setup Code Lifetime
    |--------------------------------------------------------------------------
    |
    | Minutes before a setup code expires. Previous codes are invalidated when
    | a new code is requested.
    |
    */

    'token_lifetime' => (int) env('SETUP_TOKEN_LIFETIME', 60),

];
