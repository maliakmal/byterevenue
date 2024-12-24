<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Models\User;
use App\Services\Dashboard\DashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardApiController extends ApiController
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
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->hasRole('admin')) {
            return $this->adminDashboard();
        }

        return $this->userDashboard();
    }

    private function adminDashboard()
    {
        $dashboardData = $this->dashboardService->generateAdminDashboardData();

        return $this->responseSuccess([
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
        ]);
    }

    private function userDashboard()
    {
        $dashboardData = $this->dashboardService->generateUserDashboardData();

        return $this->responseSuccess([
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
        ]);
    }
}
