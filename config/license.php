<?php

return [

    /*
    |--------------------------------------------------------------------------
    | License Server URL
    |--------------------------------------------------------------------------
    |
    | The full URL to the Jo-Tech license verification endpoint.
    |
    */

    'server_url' => env('LICENSE_SERVER_URL', 'https://jo-tech.org/api/verify-license'),

    /*
    |--------------------------------------------------------------------------
    | License Secret (Per-License Key)
    |--------------------------------------------------------------------------
    |
    | The unique license key (UUID) assigned to this project's domain.
    | This is generated in the Jo-Tech admin panel when creating a license.
    |
    */

    'secret' => env('LICENSE_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Cache Duration (Hours)
    |--------------------------------------------------------------------------
    |
    | How many hours to cache the license status. This provides:
    | - Offline tolerance (app works temporarily without reaching the server)
    | - Performance (avoids frequent API calls)
    |
    | Set to 0 to disable caching (not recommended in production).
    |
    */

    'cache_hours' => env('LICENSE_CACHE_HOURS', 24),

];
