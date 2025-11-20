<?php

use Illuminate\Http\Request;

return [

    /*
    |--------------------------------------------------------------------------
    | Trusted Proxies
    |--------------------------------------------------------------------------
    |
    | Specify the trusted proxy IPs for this application. You may also use
    | "*" or "**" to trust all proxies that connect directly or in a chain.
    |
    */

    'proxies' => env('TRUSTED_PROXIES', '*'),

    /*
    |--------------------------------------------------------------------------
    | Proxy Header Options
    |--------------------------------------------------------------------------
    |
    | Configure which headers are used to detect proxies. The default covers
    | all typical headers, including AWS ELB.
    |
    */

    'headers' => env('TRUSTED_PROXY_HEADERS', Request::HEADER_X_FORWARDED_FOR
        | Request::HEADER_X_FORWARDED_HOST
        | Request::HEADER_X_FORWARDED_PORT
        | Request::HEADER_X_FORWARDED_PROTO
        | Request::HEADER_X_FORWARDED_AWS_ELB),

];
