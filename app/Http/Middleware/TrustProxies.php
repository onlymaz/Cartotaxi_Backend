<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * Set TRUSTED_PROXIES in .env to the LB/proxy IPs (comma-separated), or '*'
     * if you terminate TLS at a trusted edge and there are no untrusted hops.
     *
     * @var array|string|null
     */
    protected $proxies;

    /**
     * The headers that should be used to detect proxies.
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO;

    public function __construct()
    {
        $proxies = env('TRUSTED_PROXIES');

        if ($proxies === '*' || $proxies === '**') {
            $this->proxies = $proxies;
        } elseif (is_string($proxies) && $proxies !== '') {
            $this->proxies = array_filter(array_map('trim', explode(',', $proxies)));
        }
    }
}
