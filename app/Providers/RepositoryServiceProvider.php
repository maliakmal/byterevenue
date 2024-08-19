<?php

namespace App\Providers;

use App\Repositories\Contract\BlackListNumber\BlackListNumberRepositoryInterface;
use App\Repositories\Contract\BroadcastLog\BroadcastLogRepositoryInterface;
use App\Repositories\Contract\Campaign\CampaignRepositoryInterface;
use App\Repositories\Contract\CampaignShortUrl\CampaignShortUrlRepositoryInterface;
use App\Repositories\Contract\Contact\ContactRepositoryInterface;
use App\Repositories\Contract\Setting\SettingRepositoryInterface;
use App\Repositories\Contract\UrlShortener\UrlShortenerRepositoryInterface;
use App\Repositories\Contract\User\UserRepositoryInterface;
use App\Repositories\Contract\BatchFile\BatchFileRepositoryInterface;
use App\Repositories\Model\BlackListNumber\BlackListNumberRepository;
use App\Repositories\Model\BroadcastLog\BroadcastLogRepository;
use App\Repositories\Model\Campaign\CampaignRepository;
use App\Repositories\Model\CampaignShortUrl\CampaignShortUrlRepository;
use App\Repositories\Model\Contact\ContactRepository;
use App\Repositories\Model\Setting\SettingRepository;
use App\Repositories\Model\UrlShortener\UrlShortenerRepository;
use App\Repositories\Model\User\UserRepository;
use App\Repositories\Model\BatchFile\BatchFileRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(CampaignShortUrlRepositoryInterface::class, CampaignShortUrlRepository::class);
        $this->app->bind(CampaignRepositoryInterface::class, CampaignRepository::class);
        $this->app->bind(BroadcastLogRepositoryInterface::class, BroadcastLogRepository::class);
        $this->app->bind(UrlShortenerRepositoryInterface::class, UrlShortenerRepository::class);
        $this->app->bind(SettingRepositoryInterface::class, SettingRepository::class);
        $this->app->bind(BlackListNumberRepositoryInterface::class, BlackListNumberRepository::class);
        $this->app->bind(ContactRepositoryInterface::class, ContactRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(BatchFileRepositoryInterface::class, BatchFileRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
