<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Hidehalo\Nanoid\Client;

class FinishLoopContactGeneration extends BaseJob implements ShouldQueue
{
    public $timeout = 600; // 10 minutes

    const QUEUE_KEY = 'campaign_contact_processing';

    /**
     * Create a new job instance.
     */
    public function __construct(
        public $campaign,
        public $batch_size = 500,
    ) {
        $this->onQueue(self::QUEUE_KEY);
    }

    /**
     * Finish loop of CSV queue batch and set status complete the batch_file
     */
    public function handle(): void
    {
        $extra  = \DB::table('extra_broadcast_logs')->get();
        $chunks = $extra->chunk($this->batch_size)->toArray();

        foreach ($chunks as $chunk) {
            $insert       = [];
            $processedIds = [];

            foreach ($chunk as $item) {
                $processedIds[] = $item->id;
                $item->id       = \Str::ulid()->toString();
                $item->slug     = (new Client())->generateId(size: 8, mode: Client::MODE_DYNAMIC);
                $insert[]       = (array) $item;
            }

            try {
                \DB::beginTransaction();

                \DB::table('broadcast_logs')->insert($insert);

                \Log::info("Inserted extra broadcast logs: " . count($chunk));

                \DB::table('extra_broadcast_logs')->whereIn('id', $processedIds)->delete();

                \DB::commit();
            } catch (\Exception $e) {
                \DB::rollBack();
                \Log::error("Error inserting extra broadcast logs: " . $e->getMessage());
            }
        }

        // $this->campaign->markAsProcessed(); // need change statuses in Campaign for this
    }
}
