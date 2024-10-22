<?php

namespace App\Console\Commands\storage;

use Illuminate\Console\Command;

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
        $logs = \DB::table('broadcast_logs')
            ->select('id', 'recipient_phone', 'contact_id', 'campaign_id', 'sent_at', 'click_at', 'created_at')
            ->where(function ($query) {
                $query
                    ->where('sent_at', 1)
                    ->where('click_at', 1);
            })
            ->orWhere(function ($query) {
                $query
                    ->where('sent_at', 1)
                    ->where('click_at', 0)
                    ->where('created_at', '<', now()->subDays(config('settings.storage.not_clicked_period')));
            })
            ->orWhere(function ($query) {
                $query
                    ->where('sent_at', 0)
                    ->where('created_at', '<', now()->subDays(config('settings.storage.total_period')));
            })
            ->limit(config('settings.storage.archive_logs.count'))
            ->get();

        $block = [];

        foreach ($logs as $log) {
            $block[] = [
                // 'phone'       => intval($log->recipient_phone),
                'contact_id'  => $log->contact_id,
                'campaign_id' => $log->campaign_id,
                'sent_at'     => $log->sent_at,
                'click_at'    => $log->click_at,
                'created_at'  => $log->created_at,
            ];
        }

        \DB::connection('storage_mysql')->table('broadcast_storage_master')->insert($block);

        $ids = $logs->pluck('id')->toArray();

        \DB::connection('mysql')->table('broadcast_logs')->whereIn('id', $ids)->delete();

        dump(sprintf('Logs %s moved to storage database.', count($block)));

        return self::SUCCESS;
    }
}
