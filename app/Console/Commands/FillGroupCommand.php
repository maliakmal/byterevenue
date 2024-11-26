<?php

namespace App\Console\Commands;

use App\Jobs\FillingRecipientGroupJob;
use App\Models\RecipientsGroup;
use App\Models\RecipientsList;
use Illuminate\Console\Command;

class FillGroupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tmp:fill_group';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fill recipients group';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $recipientLists = RecipientsList::doesntHave('recipientsGroup')->get();

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
