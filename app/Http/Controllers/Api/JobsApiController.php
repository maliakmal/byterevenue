<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Http\Requests\JobRegenerateRequest;
use App\Repositories\Contract\BroadcastLog\BroadcastLogRepositoryInterface;
use App\Repositories\Contract\CampaignShortUrl\CampaignShortUrlRepositoryInterface;
use App\Services\BatchFileDownloadService;
use App\Services\Campaign\CampaignService;
use App\Services\Accounts\AccountsService;
use App\Services\JobService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobsApiController extends ApiController
{
    /**
     * @param CampaignShortUrlRepositoryInterface $campaignShortUrlRepository
     * @param CampaignService $campaignService
     * @param BroadcastLogRepositoryInterface $broadcastLogRepository
     * @param JobService $jobService
     * @param BatchFileDownloadService $batchFileDownloadService
     */
    public function __construct(
        protected CampaignShortUrlRepositoryInterface $campaignShortUrlRepository,
        protected CampaignService $campaignService,
        protected BroadcastLogRepositoryInterface $broadcastLogRepository,
        protected JobService $jobService,
        protected BatchFileDownloadService $batchFileDownloadService,
    ) {}

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function fifo(Request $request): JsonResponse
    {
        $params = $this->jobService->index($request);

        return $this->responseSuccess($params);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function campaignsFiles(Request $request): JsonResponse
    {
        $params = $this->jobService->campaignsFiles($request);

        return $this->responseSuccess($params);
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
            'url_shortener' => ['required', 'string'],
            'type' => ['required', 'string', 'in:campaign'],
            'campaign_ids' => ['required', 'array'],
            'campaign_ids.*' => ['required', 'integer'],
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

    public function downloadFile($filename)
    {
        return $this->batchFileDownloadService->streamingNewBatchFile($filename);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getQueueStats(Request $request): JsonResponse
    {
        $response = $this->jobService->getQueueStats($request);

        if (isset($response['errors'])) {
            return $this->responseError($response['errors']);
        }

        return $this->responseSuccess($response);
    }

//    /**
//     * @param Request $request
//     * @return mixed
//     */
//    public function updateBlackListNumber(Request $request)
//    {
//        $request->validate([
//            'black_list_file' => "required|max:" . config('app.csv.upload_max_size_allowed'),
//        ], $request->all());
//
//        $file = $request->file('black_list_file');
//        $content = file_get_contents($file->getRealPath());
//        $csv = $this->csvToCollection($content);
//
//        if (!$csv || count($csv) == 0) {
//            response()->error('error parse csv');
//        }
//
//        if (isset($csv->first()['phone_number']) == false) {
//            return response()->error('column phone number not found');
//        }
//
//        $this->blackListNumberRepository->upsertPhoneNumber($csv->toArray());
//
//        // return response()->success();
//        $this->responseSuccess();
//    }
}
