<?php

namespace App\Jobs;

use App\Models\RecipientsGroup;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;

class FillingRecipientGroupJob extends BaseJob implements ShouldQueue
{
    public $timeout = 600; // 10 minutes
    public $tries = 1;
    public $chunkSize = 10000;
    public $ids = [];
    public $telemetry = true;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $group = RecipientsGroup::where('is_active', 0)
            ->whereNull('ready_at')
            ->first();

        \Log::info('FillingRecipientGroupJob dispatched', ['group' => $group]);

        if (!$group) {
            \Log::info('No recipients group found for job FillingRecipientGroupJob');
            return;
        }

        $group->update(['is_active' => 1]);

        $recipientsList = $group->recipientsList;

        \DB::table('contacts')
            ->where('recipients_list_id', $recipientsList->id)
            ->select('id')
            ->chunkById($this->chunkSize, function (Collection $chunk) {
                $this->ids = array_merge($this->ids, $chunk->pluck('id')->toArray());
            });

        $group->update([
            'ids' => $this->ids,
            'ready_at' => now(),
            'count' => count($this->ids),
        ]);
    }
}
