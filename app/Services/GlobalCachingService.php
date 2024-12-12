<?php

namespace App\Services;

use App\Jobs\GlobalQueueWarmCacheJob;

class GlobalCachingService
{
    const CACHE_PREFIX            = 'global_caching_';
    const CACHE_REQUEST_PREFIX    = 'global_caching_request_';
    const CACHE_PROCESSING_PREFIX = 'global_caching_processing_';
    const DEFAULT_CACHE_TTL = 60 * 60; // 1 hours
    const GLOBAL_CACHE_KEYS = [
        'global_queue',
    ];

    // GETTERS
    public function getTotalInQueue(): int
    {
        $value = cache()->get(self::CACHE_PREFIX . 'total_in_queue');

        return $value ?: 0;
    }

    public function getTotalNotDownloadedInQueue(): int
    {
        $value = cache()->get(self::CACHE_PREFIX . 'total_not_downloaded_in_queue');

        return $value ?: 0;
    }

    // PROCESSING
    public function setWarmingCacheRequest(string $key): void
    {
        if (in_array($key, self::GLOBAL_CACHE_KEYS)) {
            cache()->put(self::CACHE_REQUEST_PREFIX . $key, time(), self::DEFAULT_CACHE_TTL);
        }
    }

    public function warmCacheProcessing(): void
    {
        // global_queue data
        $this->runCurrentProcessing('global_queue', function () {
            GlobalQueueWarmCacheJob::dispatch();
        });

        // other keys data
        $this->runCurrentProcessing('other_key', function () {
            //
        });
    }

    private function runCurrentProcessing(string $key, callable $callback): void
    {
        if (cache()->get(self::CACHE_REQUEST_PREFIX . $key)) {
            if (is_null(cache()->get(self::CACHE_PROCESSING_PREFIX . $key))) {
                cache()->forget(self::CACHE_REQUEST_PREFIX . $key);
                cache()->put(self::CACHE_PROCESSING_PREFIX . $key, time(), self::DEFAULT_CACHE_TTL);

                $callback();
            }
        }
    }
}
