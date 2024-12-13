<?php

namespace App\Jobs;

use App\Services\GlobalCachingService;

class UniqueCampaignsIdsWarmCacheJob extends BaseJob
{
    public $telemetry = true;
    public $broadcastLogRepository;

    public function __construct()
    {
        $this->broadcastLogRepository = app()->make('App\Repositories\Contract\BroadcastLog\BroadcastLogRepositoryInterface');
    }

    public function handle(): void
    {
        $uniqueCampaignsIds = $this->broadcastLogRepository->getUniqueCampaignsIds();
        \Log::alert('UniqueCampaignsIdsWarmCacheJob uniqueCampaignsIds: ' . json_encode($uniqueCampaignsIds));

        cache()->put(GlobalCachingService::CACHE_PREFIX . 'unique_campaigns_ids', $uniqueCampaignsIds, GlobalCachingService::DEFAULT_CACHE_TTL);
        cache()->forget(GlobalCachingService::CACHE_PROCESSING_PREFIX . 'unique_campaigns_ids');
    }
}
