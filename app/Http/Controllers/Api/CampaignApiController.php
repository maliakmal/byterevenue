<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Http\Requests\CampaignStoreRequest;
use App\Http\Requests\CampaignUpdateRequest;
use App\Models\Campaign;
use App\Models\RecipientsList;
use App\Repositories\Contract\BroadcastLog\BroadcastLogRepositoryInterface;
use App\Repositories\Contract\Campaign\CampaignRepositoryInterface;
use App\Services\Campaign\CampaignService;
use App\Services\GlobalCachingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CampaignApiController extends ApiController
{
    /**
     * @param CampaignRepositoryInterface $campaignRepository
     * @param BroadcastLogRepositoryInterface $broadcastLogRepository
     * @param CampaignService $campaignService
     */
    public function __construct(
        protected CampaignRepositoryInterface $campaignRepository,
        protected BroadcastLogRepositoryInterface $broadcastLogRepository,
        protected CampaignService $campaignService
    ) {}

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function markAsIgnoreFromQueue(Request $request, GlobalCachingService $cachingService): JsonResponse
    {
        $campaign = $this->campaignRepository->find($request->campaign_id);
        $campaign->is_ignored_on_queue = true;
        $campaign->save();
        $result = [];
        $result['total_in_queue'] = $cachingService->getTotalInQueue();
        $result['total_not_downloaded_in_queue'] = $cachingService->getTotalNotDownloadedInQueue();
        $result['campaign'] = $campaign;

        return $this->responseSuccess(options: $result);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function markAsNotIgnoreFromQueue(Request $request, GlobalCachingService $cachingService): JsonResponse
    {
        $campaign = $this->campaignRepository->find($request->campaign_id);
        $campaign->is_ignored_on_queue = false;
        $campaign->save();
        $result = [];
        $result['total_in_queue'] = $cachingService->getTotalInQueue();
        $result['total_not_downloaded_in_queue'] = $cachingService->getTotalNotDownloadedInQueue();
        $result['campaign'] = $campaign;

        return $this->responseSuccess(options: $result);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $filter = [
            'search'     => $request->get('search'),
            'status'     => $request->get('status'),
            'user_id'    => $request->get('user_id'),
            'sort_by'    => $request->get('sort_by', 'id'),
            'sort_order' => $request->get('sort_order', 'desc'),
            'per_page'   => $request->get('per_page', 5),
            'page'       => $request->get('page', 1),
        ];

        $campaigns = $this->campaignService->getCampaignsFiltered($filter);

        return $this->responseSuccess($campaigns);
    }

    /**
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function show(int $id, Request $request): JsonResponse
    {
        $filters = [
            'is_blocked' => $request->input('is_blocked'),
            'status' => $request->input('status'),
            'is_clicked' => $request->input('is_clicked'),
            'per_page' => $request->get('per_page', 5),
            'page' => $request->get('page', 1),
            'search' => $request->get('search'),
        ];

        $campaignData = $this->campaignService->show($id, $filters);

        return $this->responseSuccess($campaignData);
    }

    /**
     * @param CampaignStoreRequest $request
     * @return JsonResponse
     */
    public function store(CampaignStoreRequest $request): JsonResponse
    {
        $recipientsList = RecipientsList::find(intval($request->recipients_list_id));

        if (!$recipientsList) {
            return $this->responseError(message: 'Recipient list not found.');
        }

        $count_of_contacts = $recipientsList->recipientsGroup->count;

        if ($count_of_contacts == 0) {
            return $this->responseError(message: 'Recipient list is empty.');
        }

        $user = auth()->user();

        if (!$user->hasEnoughTokens($count_of_contacts)) {
            return $this->responseError(message: 'You do not have enough tokens to send this campaign.');
        }

        $user->deductTokens($count_of_contacts);

        [$campaign, $errors] = $this->campaignService->store($request->validated());

        if (isset($errors['message'])) {
            return $this->responseError(message: 'Failed to create campaign.');
        }

        return $this->responseSuccess(['campaign' => $campaign], 'Campaign created successfully.');
    }

    /**
     * @param CampaignUpdateRequest $request
     * @param Campaign $campaign
     * @return JsonResponse
     */
    public function update(CampaignUpdateRequest $request, Campaign $campaign): JsonResponse
    {
        if ($campaign->status !== Campaign::STATUS_DRAFT) {
            return $this->responseError(message:'Campaign has been dispatched and cannot be updated.', status:400);
        }

        $updatedCampaign = $this->campaignService->update($campaign->id, $request->validated());

        if (!$updatedCampaign) {
            return $this->responseError(message:'Failed to update the campaign.', status:400);
        }

        return $this->responseSuccess($updatedCampaign, 'Campaign updated successfully.');
    }

    /**
     * @param Campaign $campaign
     * @return JsonResponse
     */
    public function destroy(Campaign $campaign): JsonResponse
    {
        $campaign = Campaign::findOrfail($campaign->id);

        if ($campaign->status === Campaign::STATUS_DRAFT) {
            $campaign->delete();

            return $this->responseSuccess(message: 'Campaign deleted successfully.');
        }

        return $this->responseError(message: 'Campaign has been dispatched and cannot be deleted.');

    }

    /**
     * @param int $id
     *
     * @return JsonResponse
     */
    public function markAsProcessed($id)
    {
        [$result, $message] = $this->campaignService->markAsProcessed(intval($id));

        if ($result) {
            return $this->responseSuccess(message: $message);
        }

        return $this->responseError(message: $message);
    }
}
