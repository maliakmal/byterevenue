<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Dashboard\DashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class DashboardController extends Controller
{
    private DashboardService $dashboardService;

    /**
     * @param DashboardService $dashboardService
     */
    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index()
    {
        $dashboardData = $this->dashboardService->generateAdminDashboardData();
//        $dashboardData = $this->dashboardService->generateUserDashboardData();

        if (request()->raw) dd($dashboardData);

        if ($dashboardData['totalClicksCount'] > $dashboardData['totalSendCount']) {
            $ctr = 0.00;
        } elseif ($dashboardData['totalClicksCount'] > 0 && $dashboardData['totalSendCount'] > 0) {
            $ctr = $dashboardData['totalClicksCount'] / $dashboardData['totalSendCount'] * 100;
        } else {
            $ctr = 0;
        }

        return view(
            'dashboard',
            [
                'cache_updated_at' => $dashboardData['cacheUpdatedAt'],
                'campaigns_remaining_in_queue' => $dashboardData['campaignsInQueue'],
                'total_num_sent' => $dashboardData['totalSendCount'],
                'total_num_clicks' => $dashboardData['totalClicksCount'],
                'accounts' => [],
                'labels' => [],
                'campaigns_graph' => [],
                'send_graph' => [],
                'clicks_graph' => [],
                'ctr' => round($ctr, 2),
            ]
        );
    }

    /**
     * Displays the analytics screen
     *
     * @return \Illuminate\Contracts\View\Factory|View
     */
    public function analytics()
    {
        return view('pages/dashboard/analytics');
    }

    /**
     * Displays the fintech screen
     *
     * @return \Illuminate\Contracts\View\Factory|View
     */
    public function fintech()
    {
        return view('pages/dashboard/fintech');
    }

    /**
     * @return RedirectResponse
     */
    public function disableIntroductory()
    {
        User::where('id', auth()->id())->update(['show_introductory_screen' => false]);
        return response()->redirectTo('/dashboard');
    }

    /**
     * @return JsonResponse
     */
    public function disableIntroductoryApi()
    {
        $user = User::where('id', auth()->id())->update(['show_introductory_screen' => false]);
        return $this->responseSuccess($user, 'Introductory screen disabled successfully.');
    }
}
