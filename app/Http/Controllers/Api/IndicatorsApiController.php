<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Models\User;
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
        $validated = $request->validate([
            'user_ids' => ['sometimes', 'nullable', 'array'],
            'campaign_ids' => ['sometimes', 'nullable', 'array'],
        ]);

        $result = $this->indicatorsService->getTotalQueueCount($validated['user_ids'] ?? [], $validated['campaign_ids'] ?? []);

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
    public function statusUserListIndicator(): JsonResponse
    {
        $result = $this->indicatorsService->getStatusUserListIndicator();

        return $this->responseSuccess(data: $result);
    }

    public function createdDomainsIndicator(): JsonResponse
    {
        $result = $this->indicatorsService->getCreatedDomainsIndicator();

        return $this->responseSuccess(data: $result);
    }

    /**
     * @return JsonResponse
     */
    public function totalAccountsIndicator(): JsonResponse
    {
        $result = $this->indicatorsService->getTotalAccountsIndicator();

        return $this->responseSuccess(data: $result);
    }

    /**
     * @return JsonResponse
     */
    public function suspendedAccountsIndicator(): JsonResponse
    {
        $result = $this->indicatorsService->getSuspendedAccountsIndicator();

        return $this->responseSuccess(data: $result);
    }

    /**
     * @return JsonResponse
     */
    public function tokensGlobalSpentIndicator(): JsonResponse
    {
        $result = $this->indicatorsService->getTokensGlobalSpentIndicator();

        return $this->responseSuccess(data: $result);
    }

    /**
     * @return JsonResponse
     */
    public function topFiveAccountsBudget(): JsonResponse
    {
        if (!auth()->user()->hasRole('admin')) {
            return $this->responseSuccess(data: []);
        }

        $result = $this->indicatorsService->getTopFiveAccountsBudgetIndicator();

        return $this->responseSuccess(data: $result);
    }

    /**
     * @return JsonResponse
     */
    public function tokensPersonalBalance($id): JsonResponse
    {
        $id = auth()->user()->hasRole('admin') ?
            User::find($id)?->id :
            auth()->user()->id;

        if (!$id) {
            return $this->responseError(message: 'User not found', status: 404);
        }

        $result = $this->indicatorsService->getTokensPersonalBalanceIndicator($id);

        return $this->responseSuccess(data: $result);
    }

    /**
     * @return JsonResponse
     */
    public function tokensPersonalSpent($id): JsonResponse
    {
        $id = auth()->user()->hasRole('admin') ?
            User::find($id)?->id :
            auth()->user()->id;

        if (!$id) {
            return $this->responseError(message: 'User not found', status: 404);
        }

        $result = $this->indicatorsService->getTokensPersonalSpentIndicator($id);

        return $this->responseSuccess(data: $result);
    }
}
