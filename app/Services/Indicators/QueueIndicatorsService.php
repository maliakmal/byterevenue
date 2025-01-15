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
        $totalSentRaw = \DB::table('batch_files')
            ->select(\DB::raw('DATE(created_at) as date'), \DB::raw('SUM(generated_count) as count'))
            ->where('created_at', '>=', now()->subDays(6))
            ->where('has_errors', 0)
            ->where('is_ready', 1)
            ->where('type', '!=', 'regen')
            ->groupBy('date')
            ->get();

        foreach (now()->subDays(6)->daysUntil(now()) as $date) {
            $dataArray[$date->format('Y-m-d')] = (int)$totalSentRaw->where('date', $date->format('Y-m-d'))->first()?->count ?? 0;
        }

        return $dataArray;
    }

    public function getTopFiveCampaigns()
    {
        // Get top 5 campaigns by total recipients
        return \DB::table('campaigns')->orderByDesc('total_recipients')
            ->limit(5)
            ->pluck('total_recipients','title')
            ->toArray();
    }

    public function getTopFiveAccounts()
    {
        // Get top 5 accounts by total amount of transactions
        $topTransactionsUser = Transaction::join('users', 'transactions.user_id', '=', 'users.id')
            ->where('transactions.type', 'usage')
            ->select('users.*', \DB::raw('SUM(transactions.amount) as total_amount'))
            ->groupBy('transactions.user_id')
            ->orderByDesc('total_amount')
            ->limit(5)
            ->pluck('total_amount','name');

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

    public function getImportStatusRecipientLists()
    {
        $imports = \DB::table('import_recipients_lists')
            ->where('is_failed', 0)
            ->count();

        $failedImports = \DB::table('import_recipients_lists')
            ->where('is_failed', 1)
            ->count();

        return [
            'total' => $imports + $failedImports,
            'success' => $imports,
            'failed' => $failedImports,
        ];
    }

    public function getCreatedCampaignsChartData()
    {
        $campaignsByWeekRaw = \DB::table('campaigns')
            ->select(\DB::raw('DATE(created_at) as date'), \DB::raw('COUNT(id) as count'))
            ->where('created_at', '>=', now()->subDays(6))
            ->groupBy('date')
            ->get();

        foreach (now()->subDays(6)->daysUntil(now()) as $date) {
            $campaignsByWeek[$date->format('Y-m-d')] = (int)$campaignsByWeekRaw->where('date', $date->format('Y-m-d'))->first()?->count ?? 0;
        }

        return $campaignsByWeek;
    }

    public function getTotalContactsIndicator()
    {
        $total = \DB::table('contacts')->count();

        $byWeekRaw = \DB::table('contacts')
            ->select(\DB::raw('DATE(created_at) as date'), \DB::raw('COUNT(id) as count'))
            ->where('created_at', '>=', now()->subDays(6))
            ->groupBy('date')
            ->get();

        foreach (now()->subDays(6)->daysUntil(now()) as $date) {
            $byWeek[$date->format('Y-m-d')] = (int)$byWeekRaw->where('date', $date->format('Y-m-d'))->first()?->count ?? 0;
        }

        return [
            'total'  => $total,
            'byWeek' => $byWeek,
        ];
    }

    public function getStatusUserList()
    {
        $totalCount     = \DB::table('contacts')->count();
        $blackListCount = \DB::table('black_list_numbers')->count();

        return [
            'available'     => $totalCount - $blackListCount,
            'not_available' => $blackListCount,
        ];
    }

    public function getCreatedDomains()
    {
        $byWeekRaw = \DB::table('url_shorteners')
            ->select(\DB::raw('DATE(created_at) as date'), \DB::raw('COUNT(id) as count'))
            ->where('created_at', '>=', now()->subDays(6))
            ->groupBy('date')
            ->get();

        foreach (now()->subDays(6)->daysUntil(now()) as $date) {
            $byWeek[$date->format('Y-m-d')] = (int)$byWeekRaw->where('date', $date->format('Y-m-d'))->first()?->count ?? 0;
        }

        return $byWeek;
    }

    public function getTotalAccountsIndicator() //ok
    {
        $total = \DB::table('users')->count();

        $byWeekRaw = \DB::table('users')
            ->select(\DB::raw('DATE(created_at) as date'), \DB::raw('COUNT(id) as count'))
            ->where('created_at', '>=', now()->subDays(6))
            ->groupBy('date')
            ->get();

        foreach (now()->subDays(6)->daysUntil(now()) as $date) {
            $byWeek[$date->format('Y-m-d')] = (int)$byWeekRaw->where('date', $date->format('Y-m-d'))->first()?->count ?? 0;
        }

        return [
            'total'  => $total,
            'byWeek' => $byWeek,
        ];
    }

    public function getSuspendedAccountsIndicator()
    {
        return [
            'total'   => \DB::table('users')->count(),
            'suspend' => 0,
            'percent' => 0,
        ];
    }
}
