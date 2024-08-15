<?php

namespace App\Http\Controllers;

use App\Models\RecipientsList;
use App\Services\Dashboard\DashboardService;
use Illuminate\Http\Request;
use App\Models\DataFeed;
use App\Models\Campaign;
use App\Models\User;
use App\Models\BroadcastLog;
use Carbon\Carbon;

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
        $start_date = Carbon::now()->subMonths(3)->format($_dateFormat);
        $end_date = Carbon::now()->format($_dateFormat);
        $campaigns_graph = $send_graph = $clicks_graph = $ctr = $labels = [];

        if(request()->isMethod('post')){
            $date_range = request()->input('dates');
            $dates = explode('-', $date_range);
            $start_date = trim($dates[0]);
            $end_date = trim($dates[1]);

        }
        if(auth()->user()->hasRole('admin')):
            $campaigns = Campaign::select()->orderby('id', 'desc')->get()->take(100);
            $accounts = User::select()->orderby('updated_at', 'desc')->get()->take(5);
            $params['total_in_queue'] = BroadcastLog::select()->count();
            $params['total_not_downloaded_in_queue'] = BroadcastLog::select()->where('is_downloaded_as_csv', 0)->count();
            $startDate = Carbon::createFromFormat('m/d/Y', $start_date);
            $endDate = Carbon::createFromFormat('m/d/Y', $end_date);

            $params['total_campaigns'] = Campaign::select()->whereBetween('created_at', [$startDate, $endDate])->count();
            $params['total_num_sent'] = BroadcastLog::select()->where('is_sent', 1)->whereBetween('created_at', [$startDate, $endDate])->count();
            $params['total_num_clicks'] = BroadcastLog::select()->where('is_click', 1)->whereBetween('created_at', [$startDate, $endDate])->count();
            $params['ctr'] = $params['total_num_sent'] == 0 || $params['total_num_clicks'] == 0 ? 0 : ($params['total_num_clicks'] / $params['total_num_sent']) * 100;
            $params['start_date'] = $start_date;
            $params['end_date'] = $end_date;

            list($labels, $campaigns_graph, $send_graph, $clicks_graph, $ctr) = $this->dashboardService->getAdminGraphData($startDate, $endDate);

        else:
            $campaigns = auth()->user()->campaigns()->latest()->get()->take(30);
        endif;
        $has_campaign = Campaign::where('user_id', auth()->user()->id)->exists();
        $has_reception_list = RecipientsList::where('user_id', auth()->user()->id)->exists();
        return view('dashboard', compact('dataFeed', 'campaigns', 'accounts', 'params', 'has_campaign', 'has_reception_list',
        'campaigns_graph', 'send_graph','clicks_graph', 'ctr', 'labels'
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
}
