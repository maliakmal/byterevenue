<?php

namespace App\Services\Dashboard;

use App\Models\Campaign;
use App\Models\User;
use App\Repositories\Model\BroadcastLog\BroadcastLogRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    public function __construct(
        protected BroadcastLogRepository $broadcastLogRepository
    ) {}

    public function getCacheableAdminData()
    {
        // tmp disable days selection
        $startDate = now()->subDay();
        $endDate   = now()->addDay();
        $startEndString = $startDate->format('Y-m-d') . '_' . $endDate->format('Y-m-d');

        // cacheable data from app/Jobs/RefreshBroadcastLogCache.php
        return [
            'clickCount'             => (int)Cache::get('click_count_' . $startEndString, 0),
            'archivedClickCount'     => (int)Cache::get('archived_click_count_' . $startEndString, 0),
            'sendCount'              => (int)Cache::get('send_count_' . $startEndString, 0),
            'archivedSendCount'      => (int)Cache::get('archived_send_count_' . $startEndString, 0),
            'totalCount'             => (int)Cache::get('total_count_' . $startEndString, 0),
            'totalFromStorageCount'  => (int)Cache::get('total_from_storage_count_' . $startEndString, 0),
            'unsentCount'            => (int)Cache::get('unsent_count_' . $startEndString, 0),
            'campaignCount'          => (int)Cache::get('campaign_count_' . $startEndString, 0),
            'topAccounts'            => Cache::get('top_accounts_' . $startEndString, []),
            'topTokensSpent'         => Cache::get('top_tokens_spent_' . $startEndString, []),
            'topUsers'               => Cache::get('top_users_' . $startEndString, []),
            'cacheUpdatedAt'         => Cache::get('last_refreshed_at') ?: 'never',
            'responseIsCached'       => !!Cache::get('ready_'. $startEndString),
        ];
    }

    /**
     * @return array
     */
    public function generateAdminDashboardData()
    {
        $cachedData = $this->getCacheableAdminData();

        $totalUsers            = User::count();
        $usersAddedLast24Hours = User::where('created_at', '>=', now()->subDay())->count();
        $campaignsInQueue      = Campaign::where('status', Campaign::STATUS_PROCESSING)->count();

        return [
            // general info
            'totalTeams'         => $totalUsers,
            'teamsAddedLast24H'  => $usersAddedLast24Hours,
            'campaignsInQueue'   => $campaignsInQueue,
            'totalSendCount'     => $cachedData['sendCount'] + $cachedData['archivedSendCount'],
            'totalClicksCount'   => $cachedData['clickCount'] + $cachedData['archivedClickCount'],
            'totalUnsentCount'   => $cachedData['unsentCount'], // maybe total - sent?
            'totalCount'         => $cachedData['totalCount'], // total count records in broadcast_logs table
            'archiveCount'       => $cachedData['totalFromStorageCount'], // total count records in broadcast_storage_master (archive)
            'topAccounts'        => $cachedData['topAccounts'],
            'topTokensSpent'     => $cachedData['topTokensSpent'],
            'topUsers'           => $cachedData['topUsers'], // top 5 users by number of active campaigns

            // cache info
            'cacheUpdatedAt'     => $cachedData['cacheUpdatedAt'], // date of last cache update
            'responseIsCached'   => $cachedData['responseIsCached'], // if this value is false - show "Data is being updated" message
        ];
    }

    public function generateUserDashboardData()
    {
        $user             = auth()->user();
        $totalSent        = $this->broadcastLogRepository->getSendCountByUserIds([$user->id]);
        $totalSent        += $this->broadcastLogRepository->getArchivedSendCountByUserIds([$user->id]);
        $messagesInQueue  = $this->broadcastLogRepository->getUnsentCountByUserIds([$user->id]);
        $totalClicks      = $this->broadcastLogRepository->getClickedCountByUserIds([$user->id]);
        $totalClicks      += $this->broadcastLogRepository->getArchivedClickedCountByUserIds([$user->id]);
        $averageCTR       = $this->calculateCTR($totalClicks, $totalSent);
        $campaignsInQueue = $user->campaigns()->where('status', Campaign::STATUS_PROCESSING)->count();
        $recentCampaigns  = $user->campaigns()->latest()->limit(30)->get();

        return [
            'totalSent'        => $totalSent,
            'messagesInQueue'  => $messagesInQueue,
            'totalClicks'      => $totalClicks,
            'averageCTR'       => $averageCTR,
            'campaignsInQueue' => $campaignsInQueue,
            'recentCampaigns'  => $recentCampaigns,
        ];
    }

    /**
     * Calculate Click-Through Rate (CTR).
     *
     * @param int $totalClicks
     * @param int $totalSent
     * @return float
     */
    private function calculateCTR(int $totalClicks, int $totalSent): float
    {
        if (0 == $totalSent || 0 == $totalClicks) {
            return 0.0;
        }

        return ($totalClicks / $totalSent) * 100;
    }
}
