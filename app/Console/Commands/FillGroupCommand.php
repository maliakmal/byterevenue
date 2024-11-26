<?php

namespace App\Console\Commands;

use App\Jobs\FillingRecipientGroupJob;
use App\Models\Contact;
use App\Models\RecipientsGroup;
use App\Models\RecipientsList;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class FillGroupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:recipients-group';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualize recipients group ids and count fields';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // remove ths block after migration to alt table
        $recipientLists = RecipientsList::doesntHave('recipientsGroup')->get();

        foreach ($recipientLists as $recipientList) {
            $group = RecipientsGroup::create([
                'user_id' => $recipientList->user_id,
                'recipients_list_id' => $recipientList->id,
                'is_active' => 0,
                'created_at' => now(),
            ]);

            FillingRecipientGroupJob::dispatch($group);
        }
        // ###

        $groups = RecipientsGroup::query()
            ->where('is_active', 1)
            ->whereNotNull('ready_at')
            ->orderByDesc('updated_at')
            ->first();

        if ($groups) {
            $recipientsList = $group->recipientsList;
            $ids = [];

            \DB::table('contact_recipient_list')
                ->where('recipients_list_id', $recipientsList->id)
                ->select('id','contact_id')
                ->chunkById(10000, function (Collection $chunk) use (&$ids) {
                    $ids = array_merge($ids, $chunk->pluck('contact_id')->toArray());
                });

            $group->update([
                'ids' => $ids,
                'count' => count($ids),
                'updated_at' => now(),
            ]);
        }
    }
}
