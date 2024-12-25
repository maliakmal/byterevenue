<?php

namespace App\Jobs;

use App\Models\BroadcastLog;
use App\Models\Campaign;
use App\Repositories\Model\BroadcastLog\BroadcastLogRepository;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RefreshBroadcastLogCache extends BaseJob implements ShouldQueue
{
    private $startDate;
    private $endDate;
    private $startEndString;

    public $tries = 1;

    const CACHE_TTL = 60 * 60 * 24; // 24 hours

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->startDate      = now()->subDay();
        $this->endDate        = now()->addDay();
        $this->startEndString = $this->startDate->format('Y-m-d') .
            '_' . $this->endDate->format('Y-m-d');
    }

    /**
     * Execute the job.
     */
    public function handle(BroadcastLogRepository $broadcastLogRepository): void
    {
        $start = microtime(true);

        Cache::put('ready_'. $this->startEndString, true, self::CACHE_TTL);

        Cache::put(
            'click_count_' . $this->startEndString,
            $broadcastLogRepository->getClickedCount(),
            self::CACHE_TTL
        );

        Cache::put(
            'archived_click_count_' . $this->startEndString,
            $broadcastLogRepository->getArchivedClickedCount(),
            self::CACHE_TTL
        );

        Cache::put(
            'send_count_' . $this->startEndString,
            $broadcastLogRepository->getSendCount(),
            self::CACHE_TTL
        );

        Cache::put(
            'archived_send_count_' . $this->startEndString,
            $broadcastLogRepository->getArchivedSendCount(),
            self::CACHE_TTL
        );

        Cache::put(
            'total_count_' . $this->startEndString,
            $broadcastLogRepository->getTotalCount(),
            self::CACHE_TTL
        );

        Cache::put(
            'total_from_storage_count_' . $this->startEndString,
            $broadcastLogRepository->getArchivedTotalCount(),
            self::CACHE_TTL
        );

        Cache::put(
            'unsent_count_',
            $broadcastLogRepository->getUnsentCount(),
            self::CACHE_TTL
        );

        $campaign = \DB::table('campaigns')
            ->where('created_at', '>=', $this->startDate)
            ->where('created_at', '<=', $this->endDate)
            ->count();

        Cache::put(
            'campaign_count_' . $this->startEndString,
            $campaign,
            self::CACHE_TTL
        );

        $topAccounts = \DB::table('transactions')
            ->select('user_id', DB::raw('SUM(amount) as total_amount_sum'))
            ->where('type', 'purchase')
            ->groupBy('user_id')
            ->orderByDesc('total_amount_sum')
            ->limit(5)
            ->get()
            ->toArray();

        Cache::put(
            'top_accounts_' . $this->startEndString,
            $topAccounts,
            self::CACHE_TTL
        );

        $topMessagesSent = Campaign::with('user')
            ->select('user_id', DB::raw('SUM(total_recipients) as total_recipients_sum'))
            ->groupBy('user_id')
            ->orderByDesc('total_recipients_sum')
            ->limit(5)
            ->get()
            ->toArray();

        Cache::put(
            'top_messages_sent_' . $this->startEndString,
            $topMessagesSent,
            self::CACHE_TTL
        );

        $topUsers = Campaign::with('user')
            ->select('user_id', DB::raw('COUNT(*) as total_campaigns'))
            ->where('status', Campaign::STATUS_PROCESSING)
            ->groupBy('user_id')
            ->orderByDesc('total_campaigns')
            ->limit(5)
            ->get()
            ->toArray();

        Cache::put(
            'top_users_' . $this->startEndString,
            $topUsers,
            self::CACHE_TTL
        );

        Cache::put('last_refreshed_at', Carbon::now()->format('Y-m-d H:i:s'));

        \Log::debug('time of caching: '. sprintf('%.6f sec.',microtime(true) - $start));

        Cache::forget(BroadcastLog::CACHE_STATUS_KEY);
    }

    public function failed(\Exception $exception)
    {
        \Log::error('RefreshBroadcastLogCache failed', ['message' => $exception->getMessage()]);
    }
}
