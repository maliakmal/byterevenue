<?php

namespace App\Console\Commands;

use App\Models\BroadcastLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Laravel\Telescope\Telescope;

class AddBroadcastLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add:broadcast-logs {--milCount=5}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate broadcast logs with data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $millionsCount = $this->option('milCount');
        $steps = $millionsCount * 1000000 / 2000;
        $bar = $this->output->createProgressBar($steps);
        $telescopeRecording = true;

        DB::disableQueryLog();
        if (class_exists(Telescope::class) && Telescope::isRecording()) {
            Telescope::stopRecording();
            $telescopeRecording = false;
        }

        for ($i = 0; $i < $steps; $i++) {
            for ($log = 0; $log < 2000; $log++) {
                $data = BroadcastLog::factory()->make()->toArray();
                $values[] = "('" . implode("','", array_values($data)) . "')";
            }

            DB::statement('INSERT INTO broadcast_logs (
                user_id, recipients_list_id, message_id, sent_at, clicked_at, total_recipients_click_thru,
                status, message_body, recipient_phone, is_downloaded_as_csv, contact_id, campaign_id, batch,
                is_sent, is_click, is_bot, is_unique_global, is_unique_campaign, created_at, updated_at
                ) VALUES ' . implode(',', $values)
            );
            unset($data);
            unset($values);
            gc_collect_cycles();
            $bar->advance();
        }

        if (!$telescopeRecording) {
            Telescope::startRecording();
        }

        $bar->finish();
    }
}
