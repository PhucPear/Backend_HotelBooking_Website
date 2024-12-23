<?php
use Illuminate\Http\Request;

return [

    /*
    |--------------------------------------------------------------------------
    | Trusted Proxies
    |--------------------------------------------------------------------------
    |
    | The trusted proxies that should be considered trusted when inspecting
    | the headers of requests.
    |
    | By default, all proxies are trusted. You can specify a list of IPs
    | to trust or set the value to "null" to disable proxy trusting.
    |
    */

    'proxies' => env('TRUSTED_PROXIES', '*'),

    /*
    |--------------------------------------------------------------------------
    | Trusted Headers
    |--------------------------------------------------------------------------
    |
    | The headers that should be inspected when considering the trust of
    | proxies. The values should be either:
    |
    | - Request::HEADER_X_FORWARDED_ALL
    | - Request::HEADER_X_FORWARDED_FOR
    | - Request::HEADER_X_FORWARDED_HOST
    | - Request::HEADER_X_FORWARDED_PORT
    | - Request::HEADER_X_FORWARDED_PROTO
    | - Request::HEADER_X_FORWARDED_AWS_ELB
    |
    */

    'headers' => Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_HOST | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO,

];