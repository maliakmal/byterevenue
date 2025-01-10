<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Services\Indicators\QueueIndicatorsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IndicatorsApiController extends ApiController
{
    /**
     * @param Request $request
     * @param QueueIndicatorsService $indicatorsService
     * @return JsonResponse
     */
    public function totalQueue(Request $request, QueueIndicatorsService $indicatorsService): JsonResponse
    {
        $result = $indicatorsService->getTotalQueueCount($request->get('user_ids', []), $request->get('campaign_ids', []));

        return $this->responseSuccess(data: $result);
    }

    /**
     * @param Request $request
     * @param QueueIndicatorsService $indicatorsService
     * @return JsonResponse
     */
    public function totalSentOnWeek(Request $request, QueueIndicatorsService $indicatorsService): JsonResponse
    {
        $result = $indicatorsService->getTotalSentOnWeekCount();

        return $this->responseSuccess(data: $result);
    }

    /**
     * @param Request $request
     * @param QueueIndicatorsService $indicatorsService
     * @return JsonResponse
     */
    public function topFiveCampaigns(Request $request, QueueIndicatorsService $indicatorsService): JsonResponse
    {
        $result = $indicatorsService->getTopFiveCampaigns();

        return $this->responseSuccess(data: $result);
    }

    /**
     * @param Request $request
     * @param QueueIndicatorsService $indicatorsService
     * @return JsonResponse
     */
    public function topFiveAccounts(Request $request, QueueIndicatorsService $indicatorsService): JsonResponse
    {
        $result = $indicatorsService->getTopFiveAccounts();

        return $this->responseSuccess(data: $result);
    }

    /**
     * @param Request $request
     * @param QueueIndicatorsService $indicatorsService
     * @return JsonResponse
     */
    public function topFiveDomains(Request $request, QueueIndicatorsService $indicatorsService): JsonResponse
    {
        $result = $indicatorsService->getTopFiveDomains();

        return $this->responseSuccess(data: $result);
    }
}
