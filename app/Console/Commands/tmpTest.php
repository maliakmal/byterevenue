<?php

namespace App\Console\Commands;

use App\Http\Middleware\AppMiddlewareManager;
use App\Jobs\RefreshBroadcastLogCache;
use App\Models\BroadcastLog;
use Hidehalo\Nanoid\CoreInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

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
        dd(Cache::get(BroadcastLog::CACHE_STATUS_KEY));
    }
}
