<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset-database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tables = ['broadcast_logs', 'batch_files', 'batch_file_campaign', 'broadcast_batches', 'campaigns', 'campaign_short_urls', 'recipients_lists', 'contacts', 'contact_recipient_list', 'batch_file_campaign', 'batch_file_campaign']; // Specify the tables to be emptied
        $attempts = 3; // Number of confirmation attempts
        $questions = [
            "Are you absolutely sure you want to empty these tables? This action cannot be undone.",
            "Just to confirm, do you really want to delete all data from the tables?",
            "Final confirmation: Are you 100% certain you want to proceed with clearing the tables?"
        ];

        foreach ($questions as $index => $question) {
            if (!$this->confirm($question)) {
                $this->warn("Operation aborted at attempt " . ($index + 1) . ".");
                return 0;
            }
        }

        $this->info('Clearing tables...');
        foreach ($tables as $table) {
            DB::table($table)->delete();
            $this->info("Emptied table: $table");
        }

        $this->info('All specified tables have been emptied.');

    }
}
