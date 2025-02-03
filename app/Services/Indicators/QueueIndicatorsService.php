<?php

namespace App\Services\Indicators;

use App\Models\Campaign;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\Model\BroadcastLog\BroadcastLogRepository;

class QueueIndicatorsService
{
    public function __construct(
        protected BroadcastLogRepository $broadcastLogRepository
    ) {}

    public function getTotalQueueCount($userIds = [], $campaignIds = [])
    {
        if (!empty($user_ids) && !auth()->user()->hasRole('admin')) {
            $userIds = [auth()->id()];
        }

        if (!empty($campaignIds) && !auth()->user()->hasRole('admin')) {
            $campaignIds = Campaign::where('user_id', auth()->id())
                ->whereIn('id', $campaignIds)
                ->pluck('id')
                ->toArray();
        }

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
        $totalSentRaw = \DB::table('batch_files')
            ->select(\DB::raw('DATE(created_at) as date'), \DB::raw('SUM(generated_count) as count'))
            ->where('created_at', '>=', now()->subDays(6))
            ->where('has_errors', 0)
            ->where('is_ready', 1)
            ->where('type', '!=', 'regen')
            ->groupBy('date');

        if (!auth()->user()->hasRole('admin')) {
            $totalSentRaw->where('user_id', auth()->id());
        }

        $totalSentRaw = $totalSentRaw->get();

        foreach (now()->subDays(6)->daysUntil(now()) as $date) {
            $dataArray[$date->format('d-m-Y')] = (int)$totalSentRaw->where('date', $date->format('Y-m-d'))->first()?->count ?? 0;
        }

        return $dataArray;
    }

    public function getTopFiveCampaigns()
    {
        // Get top 5 campaigns by total recipients
        $campaigns = Campaign::orderByDesc('total_recipients')
            ->limit(5);

        if (!auth()->user()->hasRole('admin')) {
            $campaigns->where('user_id', auth()->id());
        }

        return $campaigns
            ->pluck('total_recipients', 'title')
            ->toArray();
    }

    public function getTopFiveAccounts()
    {
        // Get top 5 accounts by total amount of transactions
        $topTransactionsUser = Transaction::join('users', 'transactions.user_id', '=', 'users.id')
            ->where('transactions.type', 'usage')
            ->select('users.*', \DB::raw('SUM(ABS(transactions.amount)) as total_amount'))
            ->groupBy('transactions.user_id')
            ->orderByDesc('total_amount')
            ->limit(5);

        if (!auth()->user()->hasRole('admin')) {
            $topTransactionsUser->where('transactions.user_id', auth()->id());
        }

        return $topTransactionsUser->pluck('total_amount','name');
    }

    public function getTopFiveDomains()
    {
        $topUrlShortenerUsage = \DB::table('campaign_short_urls as csu')
            ->join('url_shorteners as us', 'csu.url_shortener_id', '=', 'us.id')
            ->select('us.name', \DB::raw('COUNT(csu.url_shortener_id) as usage_count'))
            ->groupBy('us.name')
            ->orderByDesc('usage_count')
            ->limit(5);

        if (!auth()->user()->hasRole('admin')) {
            $campaigns = Campaign::where('user_id', auth()->id())
                ->pluck('id')
                ->toArray();

            $topUrlShortenerUsage->whereIn('csu.campaign_id', $campaigns);
        }

        return $topUrlShortenerUsage->pluck('usage_count', 'name')->toArray();
    }

    public function getImportStatusRecipientLists()
    {
        $imports = \DB::table('import_recipients_lists')
            ->where('is_failed', 0)
            ->when(!auth()->user()->hasRole('admin'), function ($query) {
                return $query->where('user_id', auth()->id());
            })
            ->count();

        $failedImports = \DB::table('import_recipients_lists')
            ->where('is_failed', 1)
            ->when(!auth()->user()->hasRole('admin'), function ($query) {
                return $query->where('user_id', auth()->id());
            })
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
            ->when(!auth()->user()->hasRole('admin'), function ($query) {
                return $query->where('user_id', auth()->id());
            })
            ->where('created_at', '>=', now()->subDays(6))
            ->groupBy('date')
            ->get();

        foreach (now()->subDays(6)->daysUntil(now()) as $date) {
            $campaignsByWeek[$date->format('d-m-Y')] = (int)$campaignsByWeekRaw->where('date', $date->format('Y-m-d'))->first()?->count ?? 0;
        }

        return $campaignsByWeek;
    }

    public function getTotalContactsIndicator()
    {
        $total = \DB::table('contacts')
            ->when(!auth()->user()->hasRole('admin'), function ($query) {
                return $query->where('user_id', auth()->id());
            })
            ->count();

        $byWeekRaw = \DB::table('contacts')
            ->select(\DB::raw('DATE(created_at) as date'), \DB::raw('COUNT(id) as count'))
            ->when(!auth()->user()->hasRole('admin'), function ($query) {
                return $query->where('user_id', auth()->id());
            })
            ->where('created_at', '>=', now()->subDays(6))
            ->groupBy('date')
            ->get();

        foreach (now()->subDays(6)->daysUntil(now()) as $date) {
            $byWeek[$date->format('d-m-Y')] = (int)$byWeekRaw->where('date', $date->format('Y-m-d'))->first()?->count ?? 0;
        }

        return [
            'total'  => $total,
            'byWeek' => $byWeek,
        ];
    }

    public function getStatusUserListIndicator()
    {
        $totalCount = \DB::table('contacts')
            ->when(!auth()->user()->hasRole('admin'), function ($query) {
                return $query->where('user_id', auth()->id());
            })
            ->count();

        $blackListCount = \DB::table('black_list_numbers')
            ->when(!auth()->user()->hasRole('admin'), function ($query) {
                return $query->where('user_id', auth()->id());
            })
            ->count();

        return [
            'total'         => $totalCount,
            'available'     => $totalCount - $blackListCount,
            'not_available' => $blackListCount,
        ];
    }

    public function getCreatedDomainsIndicator()
    {
        $byWeekRaw = \DB::table('url_shorteners')
            ->select(\DB::raw('DATE(created_at) as date'), \DB::raw('COUNT(id) as count'))
            ->when(!auth()->user()->hasRole('admin'), function ($query) {
                return $query->where('user_id', auth()->id());
            })
            ->where('created_at', '>=', now()->subDays(6))
            ->groupBy('date')
            ->get();

        foreach (now()->subDays(6)->daysUntil(now()) as $date) {
            $byWeek[$date->format('d-m-Y')] = (int)$byWeekRaw->where('date', $date->format('Y-m-d'))->first()?->count ?? 0;
        }

        return $byWeek;
    }

    public function getTotalAccountsIndicator()
    {
        $total = \DB::table('users')->count();

        $byWeekRaw = \DB::table('users')
            ->select(\DB::raw('DATE(created_at) as date'), \DB::raw('COUNT(id) as count'))
            ->when(!auth()->user()->hasRole('admin'), function ($query) {
                return $query->where('user_id', auth()->id());
            })
            ->where('created_at', '>=', now()->subDays(6))
            ->groupBy('date')
            ->get();

        foreach (now()->subDays(6)->daysUntil(now()) as $date) {
            $byWeek[$date->format('d-m-Y')] = (int)$byWeekRaw->where('date', $date->format('Y-m-d'))->first()?->count ?? 0;
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

    public function getTokensGlobalSpentIndicator()
    {
        $total = (int)\DB::table('transactions')
            ->selectRaw('SUM(ABS(amount)) as total')
            ->when(!auth()->user()->hasRole('admin'), function ($query) {
                return $query->where('user_id', auth()->id());
            })
            ->where('created_at', '>=', now()->subDays(6))
            ->where('type', 'usage')
            ->value('total');

        $byWeekRaw = \DB::table('transactions')
            ->select(\DB::raw('DATE(created_at) as date'), \DB::raw('SUM(ABS(amount)) as sum_amount'))
            ->when(!auth()->user()->hasRole('admin'), function ($query) {
                return $query->where('user_id', auth()->id());
            })
            ->where('created_at', '>=', now()->subDays(6))
            ->groupBy('date')
            ->where('type', 'usage')
            ->get();

        foreach (now()->subDays(6)->daysUntil(now()) as $date) {
            $byWeek[$date->format('d-m-Y')] = (int)$byWeekRaw->where('date', $date->format('Y-m-d'))->first()?->sum_amount ?? 0;
        }

        return [
            'total'  => $total,
            'byWeek' => $byWeek,
        ];
    }

    public function getTopFiveAccountsBudgetIndicator()
    {
        // get 5 accounts with the highest balance
        $topFiveAccounts = \DB::table('users')
            ->select('name', 'tokens')
            ->orderByDesc('tokens')
            ->limit(5)
            ->pluck('tokens', 'name')
            ->toArray();

        return $topFiveAccounts;
    }

    public function getTokensPersonalBalanceIndicator($id)
    {
        $total = intval(User::find($id)?->tokens);

        $byWeekRaw = \DB::table('transactions')
            ->select(\DB::raw('DATE(created_at) as date'), \DB::raw('sum(amount) as balance'))
            ->whereNotIn('type', [Transaction::TYPE_HIDDEN_PURCHASE, Transaction::TYPE_HIDDEN_DEDUCTION])
            ->where('created_at', '>=', now()->subDays(6))
            ->where('user_id', $id)
            ->groupBy('date')
            ->get();

        $date = now()->addDay();

        foreach (range(0,7) as $item) {
            $delta = $byWeekRaw->where('date', $date->format('Y-m-d'))->first()?->balance ?? 0;
            $total -= $delta;
            $byWeek[$date->subDay()->format('d-m-Y')] = $total;
        }

        $byWeek = array_reverse($byWeek);
        array_shift($byWeek);

        return $byWeek;
    }

    public function getTokensPersonalSpentIndicator($id)
    {
        $total = \DB::table('transactions')
            ->selectRaw('SUM(ABS(amount)) as total')
            ->where('created_at', '>=', now()->subDays(6))
            ->where('user_id', $id)
            ->where('type', 'usage')
            ->value('total');

        $byWeekRaw = \DB::table('transactions')
            ->select(\DB::raw('DATE(created_at) as date'), \DB::raw('sum(abs(amount)) as sum_amount'))
            ->where('created_at', '>=', now()->subDays(6))
            ->where('user_id', $id)
            ->where('type', 'usage')
            ->groupBy('date')
            ->get();

        foreach (now()->subDays(6)->daysUntil(now()) as $date) {
            $byWeek[$date->format('d-m-Y')] = (int)$byWeekRaw->where('date', $date->format('Y-m-d'))->first()?->sum_amount ?? 0;
        }

        return [
            'total'  => (int)$total ?? 0,
            'byWeek' => $byWeek,
        ];
    }
}
