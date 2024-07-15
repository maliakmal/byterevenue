<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Response;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->register(RepositoryServiceProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Response::macro('success', function ($message = '', $data = [], $code = 200, $headers = []) {
            return Response::make([
                'success' => true,
                'message' => $message,
                'data' => $data
            ], $code, $headers);
        });

        Response::macro('error', function ($errorMessages = [], $code = 400, $headers = []) {
            return Response::make([
                'success' => false,
                'message' => $errorMessages,
            ], $code, $headers);
        });
    }
}
