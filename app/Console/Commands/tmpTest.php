<?php

namespace App\Console\Commands;

use App\Services\Indicators\QueueIndicatorsService;
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
        $result = (new QueueIndicatorsService(
            new \App\Repositories\Model\BroadcastLog\BroadcastLogRepository(
                new \App\Models\BroadcastLog()
            )
        ))->getTopFiveDomains();
        dd($result);
    }
}
