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
    public function markAsIgnoreFromQueue(Request $request): JsonResponse
    {
        $campaign = $this->campaignRepository->find($request->campaign_id);
        $campaign->is_ignored_on_queue = true;
        $campaign->save();
        $result = $this->broadcastLogRepository->getQueueStats();
        $result['campaign'] = $campaign;

        return $this->responseSuccess(options: $result);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function markAsNotIgnoreFromQueue(Request $request): JsonResponse
    {
        $campaign = $this->campaignRepository->find($request->campaign_id);
        $campaign->is_ignored_on_queue = false;
        $campaign->save();
        $result = $this->broadcastLogRepository->getQueueStats();
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
        $updatedCampaign = $this->campaignService->update($campaign->id, $request->validated());

        if (!$updatedCampaign) {
            return $this->responseError('Failed to update the campaign.', 400);
        }

        return $this->responseSuccess($updatedCampaign, 'Campaign updated successfully.');
    }

    /**
     * @param Campaign $campaign
     * @return JsonResponse
     */
    public function destroy(Campaign $campaign): JsonResponse
    {
        $campaign->delete();

        return $this->responseSuccess([], 'Campaign deleted successfully.');
    }
}
