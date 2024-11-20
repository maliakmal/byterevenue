<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!\Auth::check() || !\Auth::user()->hasRole('admin') || true) {
            // Redirect to a forbidden page or home if the user doesn't have the 'admin' role
            abort(403);
        }

        return $next($request);
    }
}
