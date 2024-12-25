<?php

namespace App\Console\Commands\storage;

use App\Models\Campaign;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

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
        if (!env('STORAGE_WORKER_ENABLED', false)) {
            Log::debug('Storage worker is disabled.');
            return self::SUCCESS;
        }

        // set campaign status to expired if it's expired
//        $expiredCampaignIds = \DB::table('campaigns')
//            ->select('id')
//            ->whereNotNull('expires_at')
//            ->where('expires_at', '<', now()->toDateTimeString())
//            ->where('status', Campaign::STATUS_PROCESSING)
//            ->pluck('id');

//        \DB::table('campaigns')->whereIn('id', $expiredCampaignIds)
//            ->update(['status' => Campaign::STATUS_EXPIRED]);

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
//            ->orWhere(function ($query) use ($expiredCampaignIds) {
//                $query
//                    ->whereIn('campaign_id', $expiredCampaignIds);
//            })
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

        \DB::connection('storage_mysql')->statement('ALTER TABLE broadcast_storage_master DISABLE KEYS');
        \DB::connection('storage_mysql')->table('broadcast_storage_master')->insert($block);
        \DB::connection('storage_mysql')->statement('ALTER TABLE broadcast_storage_master ENABLE KEYS');

        $ids = $logs->pluck('id')->toArray();

        \DB::connection('mysql')->table('broadcast_logs')->whereIn('id', $ids)->delete();

        Log::debug(sprintf('Logs %s moved to storage database.', count($block)));

        return self::SUCCESS;
    }
}
