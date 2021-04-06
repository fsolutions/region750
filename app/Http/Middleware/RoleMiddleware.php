<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * @param $request
     * @param Closure $next
     * @param $role
     * @return mixed
     */

    public function handle($request, Closure $next, $roles)
    {
        $roles = explode('|', $roles);

        foreach ($roles as $key => $role) :
            if (auth()->user()->hasRole($role)) return $next($request);
        endforeach;

        abort(404);
    }
}
