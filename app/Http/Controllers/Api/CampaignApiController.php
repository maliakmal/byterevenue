<?php

namespace App\Http\Controllers\Api;

use App\Repositories\Contract\Campaign\CampaignRepositoryInterface;
use App\Repositories\Contract\BroadcastLog\BroadcastLogRepositoryInterface;
use Illuminate\Http\Request;

class CampaignApiController extends ApiController
{
    public function __construct(
        protected CampaignRepositoryInterface $campaignRepository,
        protected BroadcastLogRepositoryInterface $broadcastLogRepository
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

        // todo check on frontend
        // return response()->json($result);
        $this->responseSuccess($result);
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

        // todo check on frontend
        // return response()->json($result);
        $this->responseSuccess($result);
    }
}
