<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Configuration\Middleware;

class AppMiddlewareManager
{
    public function __invoke(Middleware $middleware)
    {
        $middleware->alias(['swagger.allowed.access' => SwaggerAllowedAccess::class]);
    }
}
