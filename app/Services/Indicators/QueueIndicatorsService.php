<?php

namespace App\Services\Indicators;

use App\Models\Transaction;
use App\Repositories\Model\BroadcastLog\BroadcastLogRepository;

class QueueIndicatorsService
{
    public function __construct(
        protected BroadcastLogRepository $broadcastLogRepository
    ) {}

    public function getTotalQueueCount($userIds = [], $campaignIds = [])
    {
        $totalSentQuery = \DB::table('broadcast_logs')
            ->whereNotNull('batch');

        $messagesInQueueQuery = \DB::table('broadcast_logs')
            ->whereNull('batch');

        if (is_array($userIds) && count($userIds) > 0) {
            $totalSentQuery->whereIn('user_id', $userIds);
            $messagesInQueueQuery->whereIn('user_id', $userIds);

        } elseif (is_array($campaignIds) && count($campaignIds) > 0) {
            $totalSentQuery->whereIn('campaign_id', $campaignIds);
            $messagesInQueueQuery->whereIn('campaign_id', $campaignIds);

        }

        return [
            'totalSent'        => $totalSentQuery->count(),
            'messagesInQueue'  => $messagesInQueueQuery->count(),
        ];
    }

    public function getTotalSentOnWeekCount()
    {
        // Get total sent messages on the last week
        $dataArray = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);

            $dataArray[$date->format('m-d-Y')] = (int)\DB::table('batch_files')
                ->whereDate('created_at', $date->format('Y-m-d'))
                ->where('has_errors', 0)
                ->where('is_ready', 1)
                ->where('type', '!=', 'regen')
                ->sum('generated_count');
        }

        return $dataArray;
    }

    public function getTopFiveCampaigns()
    {
        // Get top 5 campaigns by total recipients
        return \DB::table('campaigns')->orderByDesc('total_recipients')->limit(5)->get();
    }

    public function getTopFiveAccounts()
    {
        // Get top 5 accounts by total amount of transactions
        $topTransactionsUser = Transaction::join('users', 'transactions.user_id', '=', 'users.id')
            ->select('users.*', \DB::raw('SUM(transactions.amount) as total_amount'))
            ->groupBy('transactions.user_id')
            ->orderByDesc('total_amount')
            ->limit(5)
            ->get()
            ->toArray();

        return $topTransactionsUser;
    }

    public function getTopFiveDomains()
    {
        $topUrlShortenerUsage = \DB::table('campaign_short_urls as csu')
            ->join('url_shorteners as us', 'csu.url_shortener_id', '=', 'us.id')
            ->select('us.name', \DB::raw('COUNT(csu.url_shortener_id) as usage_count'))
            ->groupBy('us.name')
            ->orderByDesc('usage_count')
            ->limit(5)
            ->pluck('usage_count', 'name')
            ->toArray();

        return $topUrlShortenerUsage;
    }
}
