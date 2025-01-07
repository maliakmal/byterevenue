<?php

namespace App\Jobs;

use App\Models\BroadcastLog;
use App\Models\Campaign;
use App\Models\Transaction;
use App\Models\User;
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

        $topAccounts = Transaction::with('user')
            ->select('user_id', DB::raw('ABS(SUM(amount)) as total_amount_sum'))
            ->where('type', 'usage')
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

        $topTokensSpent = Transaction::with('user')
            ->select('user_id', DB::raw('ABS(SUM(amount)) as total_spent'))
            ->addSelect([
                'total_messages_sent' => Campaign::selectRaw('SUM(total_recipients_sent_to)')->whereColumn('user_id','transactions.user_id'),
                'ctr' => Campaign::selectRaw('AVG(total_ctr)')->whereColumn('user_id','transactions.user_id'),
            ])
            ->where('type', 'usage')
            ->groupBy('user_id')
            ->orderByDesc('total_spent')
            ->limit(5)
            ->get()
            ->toArray();

        Cache::put(
            'top_tokens_spent_' . $this->startEndString,
            $topTokensSpent,
            self::CACHE_TTL
        );

        $topUsers = User::withCount([
            'campaigns',
            'campaigns as processing_campaign_count' => function ($query) {
                $query->where('status', Campaign::STATUS_PROCESSING);
            },
        ])
            ->addSelect([
                'latest_campaign_total_ctr' => Campaign::select('total_ctr')
                    ->whereColumn('user_id', 'users.id')
                    ->latest('id')
                    ->limit(1),
            ])
            ->orderByDesc('processing_campaign_count')
            ->latest()
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
