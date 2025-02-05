<?php

namespace App\Console\Commands;

use App\Jobs\RefreshBroadcastLogCache;
use App\Models\BroadcastLog;
use App\Services\GlobalCachingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

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

        //

        $cachingService->warmCacheProcessing();

        // starting cache warming for broadcast logs (dashboard)
        $timeMarkerForCache = now()->subDay()->format('Y-m-d') . '_' . now()->addDay()->format('Y-m-d');

        if (!Cache::get('ready_'. $timeMarkerForCache) && !Cache::get(BroadcastLog::CACHE_STATUS_KEY)) {
            Cache::put(BroadcastLog::CACHE_STATUS_KEY, true, now()->addHour());
            RefreshBroadcastLogCache::dispatch();
        }
    }
}
