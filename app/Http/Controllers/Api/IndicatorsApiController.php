<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Services\Indicators\QueueIndicatorsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IndicatorsApiController extends ApiController
{
    protected $indicatorsService;

    /**
     * IndicatorsApiController constructor.
     * @param QueueIndicatorsService $indicatorsService
     */
    public function __construct(QueueIndicatorsService $indicatorsService)
    {
        $this->indicatorsService = $indicatorsService;
    }

    /**
     * @return JsonResponse
     */
    public function totalQueue(Request $request): JsonResponse
    {
        $result = $this->indicatorsService->getTotalQueueCount($request->get('user_ids', []), $request->get('campaign_ids', []));

        return $this->responseSuccess(data: $result);
    }

    /**
     * @return JsonResponse
     */
    public function totalSentOnWeek(): JsonResponse
    {
        $result = $this->indicatorsService->getTotalSentOnWeekCount();

        return $this->responseSuccess(data: $result);
    }

    /**
     * @return JsonResponse
     */
    public function topFiveCampaigns(): JsonResponse
    {
        $result = $this->indicatorsService->getTopFiveCampaigns();

        return $this->responseSuccess(data: $result);
    }

    /**
     * @return JsonResponse
     */
    public function topFiveAccounts(): JsonResponse
    {
        $result = $this->indicatorsService->getTopFiveAccounts();

        return $this->responseSuccess(data: $result);
    }

    /**
     * @return JsonResponse
     */
    public function topFiveDomains(): JsonResponse
    {
        $result = $this->indicatorsService->getTopFiveDomains();

        return $this->responseSuccess(data: $result);
    }

    /**
     * @return JsonResponse
     */
    public function importStatusRecipientLists(): JsonResponse
    {
        $result = $this->indicatorsService->getImportStatusRecipientLists();

        return $this->responseSuccess(data: $result);
    }

    /**
     * @return JsonResponse
     */
    public function createdCampaignsChartData(): JsonResponse
    {
        $result = $this->indicatorsService->getCreatedCampaignsChartData();

        return $this->responseSuccess(data: $result);
    }

    /**
     * @return JsonResponse
     */
    public function totalContactsIndicator(): JsonResponse
    {
        $result = $this->indicatorsService->getTotalContactsIndicator();

        return $this->responseSuccess(data: $result);
    }

    /**
     * @return JsonResponse
     */
    public function statusUserList(): JsonResponse
    {
        $result = $this->indicatorsService->getStatusUserList();

        return $this->responseSuccess(data: $result);
    }

    public function createdDomains(): JsonResponse
    {
        $result = $this->indicatorsService->getCreatedDomains();

        return $this->responseSuccess(data: $result);
    }

    public function totalAccountsIndicator(): JsonResponse
    {
        $result = $this->indicatorsService->getTotalAccountsIndicator();

        return $this->responseSuccess(data: $result);
    }

    public function suspendedAccountsIndicator(): JsonResponse
    {
        $result = $this->indicatorsService->getSuspendedAccountsIndicator();

        return $this->responseSuccess(data: $result);
    }
}
