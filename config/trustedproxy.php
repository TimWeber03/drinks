<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Trusted Proxies
    |--------------------------------------------------------------------------
    |
    | Reverse proxies (such as Traefik) whose X-Forwarded-* headers are
    | trusted, so URLs are generated with the scheme and host the visitor
    | used. A comma-separated list of IPs or CIDR ranges, or "*" to trust
    | whoever connects directly, which is only safe when the app is not
    | reachable except through the proxy.
    |
    */

    'proxies' => env('TRUSTED_PROXIES'),

];
