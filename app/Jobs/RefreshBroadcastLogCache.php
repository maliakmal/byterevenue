<?php

namespace App\Jobs;

use App\Models\BroadcastLog;
use App\Repositories\Model\BroadcastLog\BroadcastLogRepository;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class RefreshBroadcastLogCache implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    private $startDate;
    private $endDate;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->startDate = Carbon::now()->subDays(1);
        $this->endDate = Carbon::now();
    }

    /**
     * Execute the job.
     */
    public function handle(BroadcastLogRepository $broadcastLogRepository): void
    {
        $startEndString = $this->startDate->format('Y-m-d')
            . '_'
            . $this->endDate->format('Y-m-d');

        Cache::forever(
            'click_data_' . $startEndString,
            $broadcastLogRepository->getClicked($this->startDate, $this->endDate)
        );

        Cache::forever(
            'archived_click_data_' . $startEndString,
            $broadcastLogRepository->getArchivedClicked($this->startDate, $this->endDate)
        );

        Cache::forever(
            'send_data_' . $startEndString,
            $broadcastLogRepository->getSendData($this->startDate, $this->endDate)
        );

        Cache::forever(
            'archived_send_data_' . $startEndString,
            $broadcastLogRepository->getArchivedSendData($this->startDate, $this->endDate)
        );

        Cache::forever(
            'totals_' . $startEndString,
            $broadcastLogRepository->getTotals($this->startDate, $this->endDate)
        );

        Cache::forever(
            'totalsFromStorage_' . $startEndString,
            $broadcastLogRepository->getArchivedTotals($this->startDate, $this->endDate)
        );

        Cache::put('last_refreshed_at', Carbon::now()->format('Y-m-d H:i:s'));
        Cache::put(BroadcastLog::CACHE_STATUS_KEY, false);
    }
}
