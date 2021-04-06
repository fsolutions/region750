<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckIpForExternalCreateOrder
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $whiteListIps = [
            '178.210.82.28',
            '127.0.0.1'
        ];

        $ip = $request->ip();

        if (in_array($ip, $whiteListIps)) {
            return $next($request);
        }

        return abort('404');
    }
}
