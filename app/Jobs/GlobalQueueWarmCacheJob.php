<?php

namespace App\Jobs;

use App\Services\GlobalCachingService;

class GlobalQueueWarmCacheJob extends BaseJob
{
    public $telemetry = true;

    public function handle(): void
    {
        \Log::alert('GlobalQueueWarmCacheJob started job');
        $notIgnoredCampaigns = \DB::table('campaigns')->where('is_ignored_on_queue', '=', 0)->pluck('id')->toArray();

        $total_in_queue = \DB::table('broadcast_logs')
            ->whereIn('campaign_id', $notIgnoredCampaigns)
            ->whereNull('batch')
            ->count();

        $total_not_downloaded_in_queue = \DB::table('broadcast_logs')
            ->whereIn('campaign_id', $notIgnoredCampaigns)
            ->whereNull('batch')
            ->where('is_downloaded_as_csv', 0)
            ->count();

        cache()->put(GlobalCachingService::CACHE_PREFIX . 'global_queue', time(), GlobalCachingService::DEFAULT_CACHE_TTL);
        cache()->put(GlobalCachingService::CACHE_PREFIX . 'total_in_queue', $total_in_queue, GlobalCachingService::DEFAULT_CACHE_TTL);
        cache()->put(GlobalCachingService::CACHE_PREFIX . 'total_not_downloaded_in_queue', $total_not_downloaded_in_queue, GlobalCachingService::DEFAULT_CACHE_TTL);
        cache()->forget(GlobalCachingService::CACHE_PROCESSING_PREFIX . 'global_queue');
    }
}
