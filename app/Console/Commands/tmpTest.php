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
use Illuminate\Support\Facades\Cache;
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
        $startDate = now()->subDay();
        $endDate   = now()->addDay();
        $startEndString = $startDate->format('Y-m-d') . '_' . $endDate->format('Y-m-d');

        $data = [
            'clickCount'             => (int)Cache::get('click_count_' . $startEndString, 0),
            'archivedClickCount'     => (int)Cache::get('archived_click_count_' . $startEndString, 0),
            'sendCount'              => (int)Cache::get('send_count_' . $startEndString, 0),
            'archivedSendCount'      => (int)Cache::get('archived_send_count_' . $startEndString, 0),
            'totalCount'             => (int)Cache::get('total_count_' . $startEndString, 0),
            'totalFromStorageCount'  => (int)Cache::get('total_from_storage_count_' . $startEndString, 0),
            'unsentCount'            => (int)Cache::get('unsent_count_' . $startEndString, 0),
            'campaignCount'          => (int)Cache::get('campaign_count_' . $startEndString, 0),
            'topAccounts'            => Cache::get('top_accounts_' . $startEndString, []),
            'topTokensSpent'         => Cache::get('top_tokens_spent_' . $startEndString, []),
            'topUsers'               => Cache::get('top_users_' . $startEndString, []),
            'cacheUpdatedAt'         => Cache::get('last_refreshed_at') ?: 'never',
            'responseIsCached'       => !!Cache::get('ready_'. $startEndString),
        ];

        $totalUsers            = User::count();
        $usersAddedLast24Hours = User::where('created_at', '>=', now()->subDay())->count();
        $campaignsInQueue      = Campaign::where('status', Campaign::STATUS_PROCESSING)->count();

        $response = [
            'totalTeams'         => $totalUsers,
            'teamsAddedLast24H'  => $usersAddedLast24Hours,
            'campaignsInQueue'   => $campaignsInQueue,
            'totalSendCount'     => $data['sendCount'] + $data['archivedSendCount'],
            'totalClicksCount'   => $data['clickCount'] + $data['archivedClickCount'],
            'totalUnsentCount'   => $data['unsentCount'],
            'totalCount'         => $data['totalCount'],
            'archiveCount'       => $data['totalFromStorageCount'],
            'topAccounts'        => $data['topAccounts'],
            'topTokensSpent'     => $data['topTokensSpent'],
            'topUsers'           => $data['topUsers'],
            'cacheUpdatedAt'     => $data['cacheUpdatedAt'],
            'responseIsCached'   => $data['responseIsCached'],
        ];

        broadcast(new \App\Events\Admin\AdminDashboardEvent($response));
    }
}
