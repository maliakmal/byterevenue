<?php

namespace App\Console\Commands;

use App\Models\BroadcastLog;
use App\Models\Campaign;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Laravel\Telescope\Telescope;

class AddBroadcastLogs extends Command
{
    const BATCH_SIZE = 2000;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add:broadcast-logs {--milCount=5} {--type=main}';

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
        $type = $this->option('type');
        $steps = $millionsCount * 1000000 / self::BATCH_SIZE;
        $bar = $this->output->createProgressBar($steps);
        $telescopeRecording = true;

        DB::disableQueryLog();
        if (class_exists(Telescope::class) && Telescope::isRecording()) {
            Telescope::stopRecording();
            $telescopeRecording = false;
        }

        $users = Campaign::select('user_id')
            ->groupBy('user_id')
            ->take(100)
            ->get();

        for ($i = 0; $i < $steps; $i++) {
            $randomUser = User::where('id', $users->random()->user_id)->first();
            $userCampaign = $randomUser->campaigns()->take(10)->get()->random();

            for ($log = 0; $log < self::BATCH_SIZE; $log++) {
                $message = $userCampaign->messages?->random();
                $data = BroadcastLog::factory()->make(
                    [
                        'user_id' => $randomUser->id,
                        'recipients_list_id' => $randomUser->recipientsLists?->random()->id ?? 1,
                        'contact_id' => $randomUser->contacts()->take(5)->get()->random()->id ?? 1,
                        'campaign_id' => $userCampaign->id,
                        'message_id' => $message->id ?? 1,
                    ]
                )->toArray();

                if ($type === 'main') {
                    $values[] = "('" . implode("','", array_values($data)) . "')";
                }

                if ($type === 'archive') {
                    $values[] = "('" .  implode("','", [
                        $data['id'],
                        $data['contact_id'],
                        $data['campaign_id'],
                        $data['sent_at'],
                        $data['clicked_at'],
                        $data['created_at'],
                    ]) . "')";
                }
            }

            $tableName = $type === 'main' ? 'broadcast_logs' : 'broadcast_storage_master';
            $connection = $type === 'main' ? 'mysql' : 'storage_mysql';
            $mainFields = 'id, user_id, recipients_list_id, message_id, sent_at, clicked_at, total_recipients_click_thru,
                status, message_body, recipient_phone, is_downloaded_as_csv, contact_id, campaign_id, batch,
                is_sent, is_click, is_bot, is_unique_global, is_unique_campaign, created_at, updated_at';
            $archiveFields = 'contact_id, campaign_id, sent_at, clicked_at, created_at';

            DB::connection($connection)->statement('INSERT INTO ' . $tableName
                . ' (' . ($type === 'main' ? $mainFields : $archiveFields)
                . ') VALUES ' . implode(',', $values)
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
