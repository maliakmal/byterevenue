<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
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
            'status' => $request->get('status'),
            'user_id' => $request->get('user_id'),
            'sortby' => $request->get('sortby', 'id_desc'),
            'count' => $request->get('count', 5),
            'search' => $request->get('search'),
        ];
        $campaigns = $this->campaignService->getCampaignsFiltered($filter);
        return $this->responseSuccess($campaigns);
    }

    public function show(int $id, Request $request)
    {
        $filters = [
            'per_page' => $request->get('per_page', 5),
            'page' => $request->get('page', 1),
            'sort' => $request->get('sort_by', 'id_desc'),
            'sort_order' => $request->get('sort_order', 'asc'),
        ];

        $campaignData = $this->campaignService->show($id, $filters);

        return $this->responseSuccess($campaignData);
    }
}
