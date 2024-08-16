<?php

namespace App\Services\Dashboard;

use App\Models\BroadcastLog;
use App\Models\Campaign;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array[]
     */
    public function getAdminGraphData(Carbon $startDate, Carbon $endDate)
    {
        $click_data = BroadcastLog::select(DB::raw("DATE(created_at) AS date, COUNT(*) AS count"))
            ->where('created_at' ,'>=', $startDate)->where('created_at' ,'<=', $endDate)
            ->where('is_click', true)
            ->groupBy(DB::raw('DATE(created_at)'))->get();
        $click_map = [];
        foreach ($click_data as $datum){
            $click_map[$datum->date.''] = $datum->count;
        }

        $send_data = BroadcastLog::select(DB::raw("DATE(created_at) AS date, COUNT(*) AS count"))
            ->where('created_at' ,'>=', $startDate)->where('created_at' ,'<=', $endDate)
            ->where('is_sent', true)
            ->groupBy(DB::raw('DATE(created_at)'))->get();
        $send_map = [];
        foreach ($send_data as $datum){
            $send_map[$datum->date.''] = $datum->count;
        }

        $campaign_data = Campaign::select(DB::raw("DATE(created_at) AS date, COUNT(*) AS count"))
            ->where('created_at' ,'>=', $startDate)->where('created_at' ,'<=', $endDate)
            ->groupBy(DB::raw('DATE(created_at)'))->get();
        $campaign_map = [];
        foreach ($campaign_data as $datum){
            $campaign_map[$datum->date.''] = $datum->count;
        }

        $labels = [];
        $clicks = [];
        $send = [];
        $campaigns = [];
        $ctr = [];
        while ($startDate->lt($endDate)){
            $date = $startDate->format('Y-m-d');
            $labels[] = $date;
            $clicks[] = $click_map[$date] ?? 0;
            $send[] = $send_map[$date] ?? 0;
            $campaigns[] = $campaign_map[$date] ?? 0;
            $ctr[] = (($click_map[$date] ?? 0) == 0 || ($send_map[$date] ?? 0) == 0) ? 0 : (($click_map[$date] /  $send_map[$date])*100);
            $startDate = $startDate->addDay();
        }
        return [$labels, $campaigns, $send, $clicks, $ctr];
    }

}
