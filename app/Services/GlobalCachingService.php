<?php

namespace App\Services;

use App\Jobs\GlobalQueueWarmCacheJob;
use App\Jobs\UniqueCampaignsIdsWarmCacheJob;
use App\Repositories\Model\BroadcastLog\BroadcastLogRepository;

class GlobalCachingService
{
    const CACHE_PREFIX            = 'global_caching_';
    const CACHE_REQUEST_PREFIX    = 'global_caching_request_';
    const CACHE_PROCESSING_PREFIX = 'global_caching_processing_';
    const DEFAULT_CACHE_TTL = 60 * 60; // 1 hours
    const GLOBAL_CACHE_KEYS = [
        'global_queue',
        'unique_campaigns_ids',
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

    public function getUniqueCampaignsIds($cacheable = true): array
    {
        if ($cacheable) {
            $value = cache()->get(self::CACHE_PREFIX . 'unique_campaigns_ids');
        } else {
            $value = app(BroadcastLogRepository::class)->getUniqueCampaignsIds();
        }

        return $value ?: [];
    }

    // PROCESSING
    /** @param string|array $key */
    public function setWarmingCacheRequest(mixed $key): void
    {
        \Log::alert('set marker', ['m' => $key]);
        if (is_array($key)) {
            foreach ($key as $singleKey) {
                if (in_array($singleKey, self::GLOBAL_CACHE_KEYS)) {
                    cache()->put(self::CACHE_REQUEST_PREFIX . $singleKey, time(), self::DEFAULT_CACHE_TTL);
                }
            }
        } else {
            if (in_array($key, self::GLOBAL_CACHE_KEYS)) {
                cache()->put(self::CACHE_REQUEST_PREFIX . $key, time(), self::DEFAULT_CACHE_TTL);
            }
        }

    }

    public function warmCacheProcessing(): void
    {
        $this->runCurrentProcessing('global_queue', function () {
            GlobalQueueWarmCacheJob::dispatch();
        });

        $this->runCurrentProcessing('unique_campaigns_ids', function () {
            UniqueCampaignsIdsWarmCacheJob::dispatch();
        });
    }

    private function runCurrentProcessing(string $key, callable $callback): void
    {
        if (cache()->get(self::CACHE_REQUEST_PREFIX . $key)) {
            if (is_null(cache()->get(self::CACHE_PROCESSING_PREFIX . $key))) {
                \Log::alert('start warm cache', ['key' => $key]);
                cache()->forget(self::CACHE_REQUEST_PREFIX . $key);
                cache()->put(self::CACHE_PROCESSING_PREFIX . $key, time(), self::DEFAULT_CACHE_TTL);

                $callback();
            }
        }
    }
}
