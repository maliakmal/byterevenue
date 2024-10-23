<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\ApiController;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Services\Dashboard\DashboardService;

class DashboardController extends ApiController
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
        $dashboardData = $this->dashboardService->generateDashboardData();

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
            ]
        );
    }

    /**
     * @return JsonResponse
     */
    public function indexApi()
    {
        return $this->responseSuccess($this->dashboardService->generateDashboardData());
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
}
