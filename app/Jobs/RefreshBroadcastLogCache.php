<?php

namespace App\Jobs;

use App\Models\BroadcastLog;
use App\Repositories\Model\BroadcastLog\BroadcastLogRepository;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class RefreshBroadcastLogCache implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;

    private $startDate;
    private $endDate;
    private $startEndString;

    public $timeout = 600; // 10 minutes
    public $tries = 1;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->startDate      = now()->subDay();
        $this->endDate        = now()->addDay();
        $this->startEndString = $this->startDate->format('Y-m-d') .'_'.
        $this->endDate->format('Y-m-d');
    }

    /**
     * Execute the job.
     */
    public function handle(BroadcastLogRepository $broadcastLogRepository): void
    {
        \Log::debug('Refreshing broadcast log cache');

        $start = microtime(true);

        Cache::forever('ready_'. $this->startEndString, true);

        Cache::forever(
            'click_data_' . $this->startEndString,
            $broadcastLogRepository->getClicked($this->startDate, $this->endDate)
        );

        Cache::forever(
            'archived_click_data_' . $this->startEndString,
            $broadcastLogRepository->getArchivedClicked($this->startDate, $this->endDate)
        );

        Cache::forever(
            'send_data_' . $this->startEndString,
            $broadcastLogRepository->getSendData($this->startDate, $this->endDate)
        );

        Cache::forever(
            'archived_send_data_' . $this->startEndString,
            $broadcastLogRepository->getArchivedSendData($this->startDate, $this->endDate)
        );

        Cache::forever(
            'totals_' . $this->startEndString,
            $broadcastLogRepository->getTotals($this->startDate, $this->endDate)
        );

        Cache::forever(
            'totalsFromStorage_' . $this->startEndString,
            $broadcastLogRepository->getArchivedTotals($this->startDate, $this->endDate)
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
