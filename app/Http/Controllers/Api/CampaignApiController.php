<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Http\Requests\CampaignStoreRequest;
use App\Http\Requests\CampaignUpdateRequest;
use App\Models\Campaign;
use App\Repositories\Contract\BroadcastLog\BroadcastLogRepositoryInterface;
use App\Repositories\Contract\Campaign\CampaignRepositoryInterface;
use App\Services\Campaign\CampaignService;
use Illuminate\Http\Request;

class CampaignApiController extends ApiController
{
    public function __construct(
        protected CampaignRepositoryInterface $campaignRepository,
        protected BroadcastLogRepositoryInterface $broadcastLogRepository,
        protected CampaignService $campaignService
    ) {}

    /**
     * @param Request $request
     * @return mixed
     */
    public function markAsIgnoreFromQueue(Request $request)
    {
        $campaign = $this->campaignRepository->find($request->campaign_id);
        $campaign->is_ignored_on_queue = true;
        $campaign->save();
        $result = $this->broadcastLogRepository->getQueueStats();
        $result['campaign'] = $campaign;

        $this->responseSuccess(options: $result);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function markAsNotIgnoreFromQueue(Request $request)
    {
        $campaign = $this->campaignRepository->find($request->campaign_id);
        $campaign->is_ignored_on_queue = false;
        $campaign->save();
        $result = $this->broadcastLogRepository->getQueueStats();
        $result['campaign'] = $campaign;

        $this->responseSuccess(options: $result);
    }

    public function index(Request $request)
    {
        $filter = [
            'search' => $request->get('search'),
            'status' => $request->get('status'),
            'user_id' => $request->get('user_id'),
            'sort_by' => $request->get('sort_by', 'id'),
            'sort_order' => $request->get('sort_order', 'desc'),
            'per_page' => $request->get('per_page', 5),
            'page' => $request->get('page', 1),
        ];

        $campaigns = $this->campaignService->getCampaignsFiltered($filter);

        return $this->responseSuccess($campaigns);
    }

    public function show(int $id, Request $request)
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

    public function store(CampaignStoreRequest $request)
    {
        [$campaign, $errors] = $this->campaignService->store($request->validated());

        if (isset($errors['message'])) {
            return $this->responseError($errors['message']);
        }

        return $this->responseSuccess(['campaign' => $campaign], 'Campaign created successfully.');
    }

    public function update(CampaignUpdateRequest $request, Campaign $campaign)
    {
        $updatedCampaign = $this->campaignService->update($campaign->id, $request->validated());

        if (!$updatedCampaign) {
            return $this->responseError('Failed to update the campaign.', 400);
        }

        return $this->responseSuccess($updatedCampaign, 'Campaign updated successfully.');
    }

    public function destroy(Campaign $campaign)
    {
        $campaign->delete();
        return $this->responseSuccess([], 'Campaign deleted successfully.');
    }
}
