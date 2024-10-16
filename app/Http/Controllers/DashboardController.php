<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Campaign;
use App\Models\DataFeed;
use App\Models\BroadcastLog;
use Illuminate\Http\Request;
use App\Models\RecipientsList;
use Illuminate\Support\Facades\DB;
use App\Services\Dashboard\DashboardService;

class DashboardController extends Controller
{
    private DashboardService $dashboardService;
    public function __construct()
    {
        $this->dashboardService = new DashboardService();
    }

    public function index()
    {
        $dataFeed = new DataFeed();
        $accounts = [];
        $params = [];
        $_dateFormat = 'm/d/Y';
        $start_date = Carbon::now()->subDays(1)->format($_dateFormat);
        $end_date = Carbon::now()->format($_dateFormat);
        $campaigns_graph = $send_graph = $clicks_graph = $ctr = $labels = [];
        if (request()->isMethod('post')) {
            $date_range = request()->input('dates');
            $dates = explode('-', $date_range);
            $start_date = trim($dates[0]);
            $end_date = trim($dates[1]);

        }
        if (auth()->user()->hasRole('admin')):
            $campaigns = Campaign::select()->orderby('id', 'desc')->limit(100)->get();
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

            $totals = BroadcastLog::selectRaw("
                    COUNT(CASE WHEN is_sent = 1 AND sent_at BETWEEN ? AND ? THEN 1 END) as total_num_sent,
                    COUNT(CASE WHEN is_click = 1 AND clicked_at BETWEEN ? AND ? THEN 1 END) as total_num_clicks
                ", [$startDate, $endDate, $startDate, $endDate])
                ->first();

            $params['total_num_sent'] = $totals->total_num_sent;
            $params['total_num_clicks'] = $totals->total_num_clicks;

            $params['ctr'] = $this->calculateCTR($params['total_num_clicks'], $params['total_num_sent']);
            $params['start_date'] = $start_date;
            $params['end_date'] = $end_date;

            $params['campaigns_remaining_in_queue'] = BroadcastLog::select('campaign_id')->where('is_sent', 0)->groupby('campaign_id')->get()->count();
            // $params['campaigns_in_queue'] = BroadcastLog::select('campaign_id')->groupby('campaign_id')->get()->count('id');
            $params['campaigns_in_queue'] = DB::selectOne("select count(*) AS ct from (select campaign_id from broadcast_logs group by campaign_id) subq")->ct;
            $params['campaigns_completed_from_queue'] = $params['campaigns_in_queue'] - $params['campaigns_remaining_in_queue'];
            $params['users_campaigns'] = User::withCount('campaigns')->having('campaigns_count', '>', 0)->orderBy('campaigns_count', 'desc')->limit(10)->get();

            [$labels, $campaigns_graph, $send_graph, $clicks_graph, $ctr] = $this->dashboardService->getAdminGraphData($startDate, $endDate);
        else:
            $campaigns = auth()->user()->campaigns()->latest()->limit(30)->get();
        endif;
        $has_campaign = Campaign::where('user_id', auth()->id())->exists();
        $has_reception_list = RecipientsList::where('user_id', auth()->id())->exists();
        return view('dashboard', compact(
            'dataFeed',
            'campaigns',
            'accounts',
            'params',
            'has_campaign',
            'has_reception_list',
            'campaigns_graph',
            'send_graph',
            'clicks_graph',
            'ctr',
            'labels'
        ));
    }

    /**
     * Displays the analytics screen
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function analytics()
    {
        return view('pages/dashboard/analytics');
    }

    /**
     * Displays the fintech screen
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function fintech()
    {
        return view('pages/dashboard/fintech');
    }

    public function disableIntroductory()
    {
        User::where('id', auth()->id())->update(['show_introductory_screen' => false]);
        return response()->redirectTo('/dashboard');
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
