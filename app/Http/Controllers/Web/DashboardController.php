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

    /**
     * @return View
     */
    public function index()
    {
        $dashboardData = $this->dashboardService->generateAdminDashboardData();

        return view(
            'dashboard',
            [
                'dataFeed'           => $dashboardData['dataFeed'],
                'campaigns'          => $dashboardData['campaigns'],
                'accounts'           => $dashboardData['accounts'],
                'params'             => $dashboardData['params'],
                'has_campaign'       => $dashboardData['has_campaign'],
                'has_reception_list' => $dashboardData['has_reception_list'],
                'campaigns_graph'    => $dashboardData['campaigns_graph'],
                'send_graph'         => $dashboardData['send_graph'],
                'clicks_graph'       => $dashboardData['clicks_graph'],
                'ctr'                => $dashboardData['ctr'],
                'labels'             => $dashboardData['labels'],
                'cache_updated_at'   => $dashboardData['cache_updated_at'],
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
