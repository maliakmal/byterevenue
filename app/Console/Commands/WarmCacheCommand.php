<?php

namespace App\Console\Commands;

use App\Services\GlobalCachingService;
use Illuminate\Console\Command;

class WarmCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:warm-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Warm cache';

    /**
     * Execute the console command.
     */
    public function handle(GlobalCachingService $cachingService)
    {
        if (
            is_null(cache()->get(GlobalCachingService::CACHE_REQUEST_PREFIX . 'global_queue')) &&
            is_null(cache()->get(GlobalCachingService::CACHE_PROCESSING_PREFIX . 'global_queue')) &&
            is_null(cache()->get(GlobalCachingService::CACHE_PREFIX . 'global_queue'))
        ) {
            $cachingService->setWarmingCacheRequest('global_queue');
        }

        //$cachingService->warmCacheProcessing();
    }
}
