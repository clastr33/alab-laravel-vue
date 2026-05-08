<?php

$origins = env('CORS_ALLOWED_ORIGINS');
if (is_string($origins) && $origins !== '') {
    $allowedOrigins = array_values(array_filter(array_map(
        static fn (string $s): string => trim($s),
        explode(',', $origins)
    )));
} elseif (is_string(env('FRONTEND_URL')) && env('FRONTEND_URL') !== '') {
    $allowedOrigins = [env('FRONTEND_URL')];
} else {
    $allowedOrigins = ['http://localhost:5173'];
}

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Needed when the SPA is served from another origin than the API (e.g.
    | https://alab.is-best.net → https://alab-be.is-best.net).
    |
    | Set CORS_ALLOWED_ORIGINS to a comma-separated list, or set FRONTEND_URL.
    |
    */

    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => $allowedOrigins,

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
