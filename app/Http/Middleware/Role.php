<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Role
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $role)
    {
        if (Auth::user()->role === (int) $role) {
            return $next($request);
        }

        return response()->json([
            'status'    => 'error',
            'message'   => 'Unauthorized.'
        ], 401);
    }
}
