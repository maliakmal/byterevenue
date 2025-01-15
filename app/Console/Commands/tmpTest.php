<?php

namespace App\Console\Commands;

use App\Models\Campaign;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Indicators\QueueIndicatorsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
            broadcastLogRepository: app()->make(\App\Repositories\Model\BroadcastLog\BroadcastLogRepository::class)
        ))->getTotalSentOnWeekCount();
        dd($result);
    }
}
