<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\IndicatorsApiController;
use App\Models\BroadcastLog;
use App\Models\Campaign;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\Contract\BroadcastLog\BroadcastLogRepositoryInterface;
use App\Repositories\Model\BroadcastLog\BroadcastLogRepository;
use App\Services\Clicks\ClickService;
use App\Services\Indicators\QueueIndicatorsService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
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
        Artisan::call('optimize:clear');
        broadcast(new \App\Events\PublicEvent('test_message'));
        broadcast(new \App\Events\PrivateEvent('private_test_message', request()->user()));
    }
}
