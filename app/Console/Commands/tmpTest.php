<?php

namespace App\Console\Commands;

use App\Helpers\MemoryUsageHelper;
use App\Jobs\FillingRecipientGroupJob;
use App\Models\RecipientsGroup;
use App\Models\RecipientsList;
use Illuminate\Console\Command;

class tmpTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tmp:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Temporary test command';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $recipientLists = RecipientsList::doesntHave('ContactGroup')->get();

        foreach ($recipientLists as $recipientList) {
            $group = RecipientsGroup::create([
                'user_id' => $recipientList->user_id,
                'recipients_list_id' => $recipientList->id,
                'is_active' => 0,
            ]);
            FillingRecipientGroupJob::dispatch($group);
        }
    }
}
