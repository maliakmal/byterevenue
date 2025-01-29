<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;

class UniqueCampaignsIdsUpdateJob extends BaseJob implements ShouldQueue
{
    public $timeout = 600; // 10 minutes
    public $tries = 1;

    const QUEUE_KEY = 'update_sent_messages_processing';

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->onQueue(self::QUEUE_KEY);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $uids = \DB::table('broadcast_logs')
            ->select('campaign_id')
            ->whereNull('batch')
            ->distinct()
            ->pluck('campaign_id')
            ->toArray();

        $existingIds = \DB::table('unique_campaigns_stacks')
            ->select('campaign_id')
            ->pluck('campaign_id')
            ->toArray();

//        \DB::table('unique_campaigns_stacks')->upsert(
//            array_map(fn($id) => ['campaign_id' => $id], $uids),
//            ['campaign_id'],
//            ['campaign_id']
//        );

        $insert = array_map(fn($id) => ['campaign_id' => $id], array_diff($uids, $existingIds));

        if (!empty($insert)) {
            \DB::table('unique_campaigns_stacks')->insert($insert);
        }

        \DB::table('unique_campaigns_stacks')->whereNotIn('campaign_id', $uids)->delete();

        \Log::info("Start processing unique Campaign selected: " . count($uids) . " campaigns", ['campaign_ids' => $uids]);
    }
}
