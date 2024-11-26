<?php

namespace App\Jobs;

use App\Models\RecipientsGroup;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class FillingRecipientGroupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // 10 minutes
    public $tries = 1;
    public $chunkSize = 10000;
    public $list;
    public $ids = [];

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

        if (!$group) {
            \Log::info('No recipients group found for job FillingRecipientGroupJob');
            return;
        }

        $group->update(['is_active' => 1]);

        $this->list = $group->recipientsList;
        $recipientsList = $group->recipientsList;

        \DB::table('contact_recipient_list')
            ->where('recipients_list_id', $recipientsList->id)
            ->select('id','contact_id')
            ->chunkById($this->chunkSize, function (Collection $chunk) {
                $this->ids = array_merge($this->ids, $chunk->pluck('contact_id')->toArray());
            });

        $group->update([
            'ids' => $this->ids,
            'ready_at' => now(),
            'count' => count($this->ids),
        ]);
    }
}
