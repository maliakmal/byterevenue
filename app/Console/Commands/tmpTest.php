<?php

namespace App\Console\Commands;

use App\Helpers\MemoryUsageHelper;
use App\Jobs\CheckDomainPropagationJob;
use App\Jobs\FillingRecipientGroupJob;
use App\Models\BatchFile;
use App\Models\BroadcastLog;
use App\Models\CampaignShortUrl;
use App\Models\RecipientsGroup;
use App\Models\RecipientsList;
use App\Services\GlobalCachingService;
use App\Services\Keitaro\KeitaroCaller;
use App\Services\Keitaro\Requests\Domains\RegisterShortDomainRequest;
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
        \DB::table('broadcast_logs')->where('id', '>', 7)
            ->andWhere('id', '<', 12)
            ->update(['id' => 7]);

        dd('completed');
    }
}
