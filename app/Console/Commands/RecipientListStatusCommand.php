<?php

namespace App\Console\Commands;

use App\Models\Campaign;
use App\Models\RecipientsList;
use Illuminate\Console\Command;

class RecipientListStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:recipients-lists';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualize recipients lists status of count campaigns and etc.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $recipientLists = RecipientsList::with('campaigns')->get();

        foreach ($recipientLists as $recipientList) {
            $recipientList->update([
                'campaigns_count'           => $recipientList->campaigns->count(),
                'campaigns_processed_count' => $recipientList
                    ->campaigns
                    ->where('status', Campaign::STATUS_PROCESSING)
                    ->count(),
            ]);
        }
    }
}
