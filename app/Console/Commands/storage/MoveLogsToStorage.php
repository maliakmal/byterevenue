<?php

namespace App\Console\Commands\storage;

use App\Jobs\RefreshBroadcastLogCache;
use App\Models\BroadcastLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class MoveLogsToStorage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:collect-logs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Move Broadcast logs to storage database.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $recordsMoved = false;

        if (!env('STORAGE_WORKER_ENABLED', false)) {
            return self::SUCCESS;
        }

        $logs = \DB::table('broadcast_logs')
            ->select('id', 'recipient_phone', 'contact_id', 'campaign_id', 'sent_at', 'clicked_at', 'created_at')
            ->where(function ($query) {
                $query
                    ->whereNotNull('sent_at')
                    ->whereNotNull('clicked_at');
            })
            ->orWhere(function ($query) {
                $query
                    ->whereNotNull('sent_at')
                    ->whereNull('clicked_at')
                    ->where('created_at', '<', now()->subDays(config('settings.storage.archive_logs.not_clicked_period', 7)));
            })
            ->orWhere(function ($query) {
                $query
                    ->whereNull('sent_at')
                    ->where('created_at', '<', now()->subDays(config('settings.storage.archive_logs.not_send_period', 7)));
            })
            ->limit(config('settings.storage.archive_logs.count'))
            ->get();

        $block = [];

        foreach ($logs as $log) {
            $block[] = [
                'id'          => $log->id,
                'contact_id'  => $log->contact_id,
                'campaign_id' => $log->campaign_id,
                'sent_at'     => $log->sent_at,
                'clicked_at'  => $log->clicked_at,
                'created_at'  => $log->created_at,
            ];
        }

        if (count($block) > 0) {
            $recordsMoved = true;
        }

        \DB::connection('storage_mysql')->table('broadcast_storage_master')->insert($block);

        $ids = $logs->pluck('id')->toArray();

        \DB::connection('mysql')->table('broadcast_logs')->whereIn('id', $ids)->delete();

        \Log::debug(sprintf('Logs %s moved to storage database.', count($block)));

        $broadcastLogUpdated = Cache::get(BroadcastLog::CACHE_STATUS_KEY, false);
        if ($broadcastLogUpdated || $recordsMoved) {
            RefreshBroadcastLogCache::dispatch();
            Cache::put(BroadcastLog::CACHE_STATUS_KEY, false);
        }

        return self::SUCCESS;
    }
}
