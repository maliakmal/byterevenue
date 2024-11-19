<?php

namespace App\Services\Dashboard;

use App\Models\Campaign;
use App\Models\DataFeed;
use App\Models\RecipientsList;
use App\Models\User;
use App\Repositories\Model\BroadcastLog\BroadcastLogRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function __construct(
        protected BroadcastLogRepository $broadcastLogRepository
    ) {}

    /**
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array[]
     */
    public function getAdminGraphData(Carbon $startDate, Carbon $endDate)
    {
        $startEndString = $startDate->format('Y-m-d') . '_' . $endDate->format('Y-m-d');
        $getFromCache = !!Cache::get('ready_'. $startEndString);

        // operation table data
        $click_data = $getFromCache
            ? Cache::get('click_data_' . $startEndString, [])
            : $this->broadcastLogRepository->getClicked($startDate, $endDate);

        $click_map = [];

        foreach ($click_data as $datum) {
            $click_map[$datum->date] = $datum->count;
        }

        unset($datum);
        unset($click_data);

        // completed data from storage table
        $archived_click_data = $getFromCache
            ? Cache::get('archived_click_data_' . $startEndString, [])
            : $this->broadcastLogRepository->getArchivedClicked($startDate, $endDate);

        foreach ($archived_click_data as $datum) {
            $click_map[$datum->date] = ($click_map[$datum->date] ?? 0) + $datum->count;
        }

        unset($datum);
        unset($archived_click_data);

        $send_data = $getFromCache
            ? Cache::get('send_data_' . $startEndString, [])
            : $this->broadcastLogRepository->getSendData($startDate, $endDate);

        $send_map = [];

        foreach ($send_data as $datum) {
            $send_map[$datum->date] = $datum->count;
        }

        unset($datum);
        unset($send_data);

        $archived_send_data = $getFromCache
            ? Cache::get('archived_send_data_' . $startEndString, [])
            : $this->broadcastLogRepository->getArchivedSendData($startDate, $endDate);

        foreach ($archived_send_data as $datum) {
            $send_map[$datum->date] = ($send_map[$datum->date] ?? 0) + $datum->count;
        }

        unset($datum);
        unset($archived_send_data);

        $campaign_data = Campaign::select(DB::raw("DATE(created_at) AS date, COUNT(*) AS count"))
            ->where('created_at', '>=', $startDate)->where('created_at', '<=', $endDate)
            ->groupBy(DB::raw('DATE(created_at)'))->get();

        $campaign_map = [];

        foreach ($campaign_data as $datum) {
            $campaign_map[$datum->date] = $datum->count;
        }

        $labels = [];
        $clicks = [];
        $send = [];
        $campaigns = [];
        $ctr = [];

        while ($startDate->lt($endDate)) {
            $date = $startDate->format('Y-m-d');
            $labels[] = $date;
            $clicks[] = $click_map[$date] ?? 0;
            $send[] = $send_map[$date] ?? 0;
            $campaigns[] = $campaign_map[$date] ?? 0;
            $ctr[] = (($click_map[$date] ?? 0) == 0 || ($send_map[$date] ?? 0) == 0) ? 0 : (($click_map[$date] / $send_map[$date]) * 100);
            $startDate = $startDate->addDay();
        }

        return [$labels, $campaigns, $send, $clicks, $ctr];
    }

    /**
     * @return array
     */
    public function generateDashboardData()
    {
        $dataFeed = new DataFeed();
        $accounts = [];
        $params = [];
        $_dateFormat = 'm/d/Y';
        $start_date = Carbon::now()->subDay()->format($_dateFormat);
        $end_date = Carbon::now()->addDay()->format($_dateFormat);
        $campaigns_graph = $send_graph = $clicks_graph = $ctr = $labels = [];
        $cacheUpdatedAt = null;

        if (request()->isMethod('post')) {
            $date_range = request()->input('dates');
            $dates = explode('-', $date_range);
            $start_date = trim($dates[0]);
            $end_date = trim($dates[1]);
        }

        if (auth()->user()->hasRole('admin')) {
            $campaigns = Campaign::orderBy('id', 'desc')->limit(100)->get();
            $accounts = User::withCount([
                'campaigns',
                'campaigns as processing_campaign_count' => function ($query) {
                    $query->where('status', Campaign::STATUS_PROCESSING);
                }
            ])
                ->addSelect(['latest_campaign_total_ctr' => Campaign::select('total_ctr')
                    ->whereColumn('user_id', 'users.id')
                    ->latest('id')
                    ->limit(1)
                ])
                ->having('campaigns_count', '>', 0)
                ->orderBy('campaigns_count', 'desc')
                ->orderBy('updated_at', 'desc')
                ->limit(10)
                ->get();

            $startDate = Carbon::createFromFormat('m/d/Y', $start_date);
            $endDate = Carbon::createFromFormat('m/d/Y', $end_date);
            $startEndString = $startDate->format('Y-m-d') . '_' . $endDate->format('Y-m-d');

            if ($this->dataRangeInCache($startDate, $endDate)) {
                $totals = Cache::get('totals_' . $startEndString);
                $totalsFromStorage = Cache::get('totalsFromStorage_' . $startEndString);
                $cacheUpdatedAt = Cache::get('last_refreshed_at');
            } else {
                $totals = $this->broadcastLogRepository->getTotals($startDate, $endDate);
                $totalsFromStorage = $this->broadcastLogRepository->getArchivedTotals($startDate, $endDate);
            }

            $params['total_num_sent'] = ($totals->total_num_sent ?? 0)
                + ($totalsFromStorage->total_num_sent ?? 0);
            $params['total_num_clicks'] = ($totals->total_num_clicks ?? 0)
                + ($totalsFromStorage->total_num_clicks ?? 0);

            $params['ctr'] = $this->calculateCTR($params['total_num_clicks'], $params['total_num_sent']);
            $params['start_date'] = $start_date;
            $params['end_date'] = $end_date;

            $params['campaigns_remaining_in_queue'] = \DB::connection('mysql')
                ->table('broadcast_logs')
                ->where('is_sent', 0)
                ->distinct('campaign_id')
                ->count();

            // archived data from storage
            $params['campaigns_remaining_in_queue'] += \DB::connection('storage_mysql')
                ->table('broadcast_storage_master')
                ->whereNull('sent_at')
                ->distinct('campaign_id')
                ->count();

            $params['campaigns_in_queue'] = \DB::connection('mysql')
                ->table('broadcast_logs')
                ->distinct('campaign_id')
                ->count();

            // archived data from storage
            $params['campaigns_in_queue'] += \DB::connection('storage_mysql')
                ->table('broadcast_storage_master')
                ->distinct('campaign_id')
                ->count();

            $params['campaigns_completed_from_queue'] =
                $params['campaigns_in_queue'] - $params['campaigns_remaining_in_queue'];

            $params['users_campaigns'] = User::withCount('campaigns')
                ->having('campaigns_count', '>', 0)
                ->orderBy('campaigns_count', 'desc')
                ->limit(10)
                ->get();

            [$labels, $campaigns_graph, $send_graph, $clicks_graph, $ctr] = $this->getAdminGraphData($startDate, $endDate);
        } else {
            $campaigns = auth()->user()->campaigns()->latest()->limit(30)->get();
        }

        $has_campaign = Campaign::where('user_id', auth()->id())->exists();
        $has_reception_list = RecipientsList::where('user_id', auth()->id())->exists();

        return [
            'dataFeed' => $dataFeed,
            'campaigns' => $campaigns,
            'accounts' => $accounts,
            'params' => $params,
            'has_campaign' => $has_campaign,
            'has_reception_list' => $has_reception_list,
            'campaigns_graph' => $campaigns_graph,
            'send_graph' => $send_graph,
            'clicks_graph' => $clicks_graph,
            'ctr' => $ctr,
            'labels' => $labels,
            'cache_updated_at' => $cacheUpdatedAt,
        ];
    }

    /**
     * @param Carbon $startDate
     * @param Carbon $endDate
     *
     * @return bool
     */
    public function dataRangeInCache(Carbon $startDate, Carbon $endDate): bool
    {
        $startEndString = $startDate->format('Y-m-d') .'_'. $endDate->format('Y-m-d');

        return !!Cache::get('ready_'. $startEndString);
    }

    /**
     * Calculate Click-Through Rate (CTR).
     *
     * @param int $totalClicks
     * @param int $totalSent
     * @return float
     */
    private function calculateCTR(int $totalClicks, int $totalSent): float
    {
        if ($totalSent === 0 || $totalClicks === 0) {
            return 0.0;
        }

        return ($totalClicks / $totalSent) * 100;
    }
}
