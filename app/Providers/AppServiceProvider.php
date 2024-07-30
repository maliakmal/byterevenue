<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Config;
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
        $this->registerConfigs();
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

    private function registerConfigs()
    {
        Setting::orderBy('id')->chunk(100, function ($items){
            foreach ($items as $item){
                $key = 'setting.'.$item->name;
                Config::set($key, $item->value);
            }
        });
    }
}
