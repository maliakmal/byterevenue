<?php

namespace App\Providers;

use App\Models\Campaign;
use App\Models\Setting;
use App\Models\User;
use App\Observers\CampaignObserver;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Response;
use Opcodes\LogViewer\Facades\LogViewer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->register(RepositoryServiceProvider::class);
        if (!$this->app->environment('production')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }

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

        ResetPassword::createUrlUsing(function (User $user, string $token) {
            return config('app.front_base_url')
                . '/reset-password?token='
                . $token
                . '&email='
                . $user->getEmailForPasswordReset();
        });

        Campaign::observe(CampaignObserver::class);
    }

    private function registerConfigs()
    {
        if (Schema::hasTable((new Setting)->getTable())) {
            Setting::on('mysql')->orderBy('id')->chunk(100, function ($items){
                foreach ($items as $item){
                    $key = 'setting.'.$item->name;
                    Config::set($key, $item->value);
                }
            });
        }
    }
}
