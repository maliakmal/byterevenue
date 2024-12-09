<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SwaggerAllowedAccess
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()/* && Auth::user()->hasRole('api_user')*/) {
            return $next($request);
        }

        return redirect()->route('login');
    }
}
