<?php

namespace App\Jobs;

use App\Services\GlobalCachingService;
use Illuminate\Contracts\Queue\ShouldQueue;

class FinishLoopCsvQueueBatch extends BaseJob implements ShouldQueue
{
    public $timeout = 600; // 10 minutes
    // public $tries = 1;
    // TODO:: repeat this job if it fails with some delay

    protected $cache_service = null;

    const QUEUE_KEY = 'CSV_generate_processing';

    /**
     * Create a new job instance.
     */
    public function __construct(
        public $batch_file,
        public $batch_size = 1000,
    ) {
        $this->cache_service = app()->make(GlobalCachingService::class);

        $this->onQueue(self::QUEUE_KEY);
    }

    /**
     * Finish loop of CSV queue batch and set status complete the batch_file
     */
    public function handle(): void
    {
        // TODO:: save extra broadcast logs to database and set status complete the batch_file
    }
}
