<?php

namespace App\Jobs;

use App\Services\GlobalCachingService;

class GlobalQueueWarmCacheJob extends BaseJob
{
    public $telemetry = true;

    public function handle(): void
    {
        \Log::alert('GlobalQueueWarmCacheJob started job');
        $IgnoredCampaigns = \DB::table('campaigns')->where('is_ignored_on_queue', 1)->pluck('id')->toArray();
        $filterOfCampaigns = count($IgnoredCampaigns) > 0;

        $total_in_queue = \DB::table('broadcast_logs')
            ->when($filterOfCampaigns, function ($query) use ($IgnoredCampaigns) {
                return $query->whereNotIn('campaign_id', $IgnoredCampaigns);
            })
            ->count();

        $total_not_downloaded_in_queue = \DB::table('broadcast_logs')
            ->when($filterOfCampaigns, function ($query) use ($IgnoredCampaigns) {
                return $query->whereNotIn('campaign_id', $IgnoredCampaigns);
            })
//            ->whereNull('batch')
            ->where('is_downloaded_as_csv', 0)
            ->count();

        cache()->put(GlobalCachingService::CACHE_PREFIX . 'global_queue', time(), GlobalCachingService::DEFAULT_CACHE_TTL);
        cache()->put(GlobalCachingService::CACHE_PREFIX . 'total_in_queue', $total_in_queue, GlobalCachingService::DEFAULT_CACHE_TTL);
        cache()->put(GlobalCachingService::CACHE_PREFIX . 'total_not_downloaded_in_queue', $total_not_downloaded_in_queue, GlobalCachingService::DEFAULT_CACHE_TTL);
        cache()->forget(GlobalCachingService::CACHE_PROCESSING_PREFIX . 'global_queue');
    }
}
