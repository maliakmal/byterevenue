<?php

namespace App\Http\Controllers\Api;

use App\Enums\BroadcastLog\BroadcastLogStatus;
use App\Http\Controllers\ApiController;
use App\Http\Requests\JobRegenerateRequest;
use App\Repositories\Contract\BroadcastLog\BroadcastLogRepositoryInterface;
use App\Repositories\Contract\CampaignShortUrl\CampaignShortUrlRepositoryInterface;
use App\Services\Campaign\CampaignService;
use App\Services\JobService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobsApiController extends ApiController
{
    /**
     * @param CampaignShortUrlRepositoryInterface $campaignShortUrlRepository
     * @param CampaignService $campaignService
     * @param BroadcastLogRepositoryInterface $broadcastLogRepository
     * @param JobService $jobService
     */
    public function __construct(
        protected CampaignShortUrlRepositoryInterface $campaignShortUrlRepository,
        protected CampaignService $campaignService,
        protected BroadcastLogRepositoryInterface $broadcastLogRepository,
        protected JobService $jobService
    ) {}

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function fifo(Request $request): JsonResponse
    {
        $params = $this->jobService->index($request);

        return $this->responseSuccess(message: $params);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function generateCsv(Request $request): JsonResponse
    {
        $params = $request->validate([
            'number_messages' => ['required', 'integer', 'min:1', 'max:100000'],
            'url_shortener'   => ['required', 'string'],
            'type'            => ['required', 'string', 'in:fifo'],
        ]);

        $result = $this->jobService->processGenerate(params: $params);

        if ($result['error'] ?? null) {
            return $this->responseError(message: $result['error']);
        }

        return $this->responseSuccess(message: $result['success']);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function generateCsvByCampaigns(Request $request): JsonResponse
    {
        $params = $request->validate([
            'number_messages' => ['required', 'integer', 'min:1', 'max:100000'],
            'url_shortener'   => ['required', 'string'],
            'type'            => ['required', 'string', 'in:campaign'],
            'campaign_ids'    => ['required', 'array'],
            'campaign_ids.*'  => ['required', 'integer'],
        ]);

        $result = $this->jobService->processGenerateByCampaigns(params: $params);

        if ($result['error'] ?? null) {
            return $this->responseError(message: $result['error']);
        }

        return $this->responseSuccess(message: $result['success']);
    }

    /**
     * @param JobRegenerateRequest $request
     * @return JsonResponse
     */
    public function regenerateUnsent(JobRegenerateRequest $request): JsonResponse
    {
        $result = $this->jobService->regenerateUnsent($request->validated());

        if ($result['error'] ?? null) {
            return $this->responseError(message: $result['error']);
        }

        return $this->responseSuccess(message: $result['success']);
    }

    /**
     * Method for webhooks data
     * @param Request $request
     * @return JsonResponse
     */
    public function updateSentMessage(Request $request): JsonResponse
    {
        $request->validate([
            'u' => ['required', 'string', 'size:8'],
        ]);

        $uid = $request->u;
        $model = $this->broadcastLogRepository->findBy('slug', $uid);

        if (!$model) {
            // answer for outside services
            return response()->error('not found');
        }

        if (
            !$this->broadcastLogRepository->updateByModel([
                'sent_at' => Carbon::now(),
                'is_sent' => true,
                'status' => BroadcastLogStatus::SENT,
            ], $model)
        ) {
            // answer for outside services
            return response()->error('update failed');
        }
        // answer for outside services
        return response()->success();
    }

    /**
     * Method for webhooks data
     * @param Request $request
     * @return JsonResponse
     */
    public function updateClickMessage(Request $request): JsonResponse
    {
        $request->validate([
            'u' => ['required', 'string', 'size:8'],
        ]);

        $uid = $request->u;
        $model = $this->broadcastLogRepository->findBy('slug', $uid);

        if (!$model) {
            // answer for outside services
            return response()->error('not found');
        }

        if (
            !$this->broadcastLogRepository->updateByModel([
                'is_click' => true,
                'clicked_at' => Carbon::now(),
            ], $model)
        ) {
            // answer for outside services
            return response()->error('update failed');
        }
        // answer for outside services
        return response()->success();
    }

    public function downloadFile($id)
    {
        return $this->jobService->downloadFile($id);
    }

}
