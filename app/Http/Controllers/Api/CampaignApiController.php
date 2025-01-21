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
        if (!auth()->user()->hasRole('admin')) {
            return $this->responseError(message: 'You do not have permission to ignore campaigns.');
        }

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
        if (!auth()->user()->hasRole('admin')) {
            return $this->responseError(message: 'You do not have permission to ignore campaigns.');
        }

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
            'is_for_fifo'   => $request->get('is_for_fifo', null),
            'search'        => $request->get('search'),
            'status'        => $request->get('status'),
            'user_id'       => $request->get('user_id'),
            'sort_by'       => $request->get('sort_by', 'id'),
            'sort_order'    => $request->get('sort_order', 'desc'),
            'per_page'      => $request->get('per_page', 5),
            'page'          => $request->get('page', 1),
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
        // TODO:: VALIDATORS!
        $filters = [
            'is_blocked' => $request->input('is_blocked'),
            'status' => 'Sent' === $request->input('status') ? 'sent' : 'unsent',
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
        // TODO:: user can create campaign
        $recipientsList = RecipientsList::find(intval($request->recipients_list_id));

        if (!$recipientsList && !$request->is_template) {
            return $this->responseError(message: 'Recipient list not found.');
        }

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
        if (!auth()->user()->hasRole('admin') && $campaign->user_id !== auth()->id()) {
            return $this->responseError(message: 'You do not have permission to update this campaign.');
        }

        if ($campaign->status !== Campaign::STATUS_DRAFT && $campaign->status !== Campaign::STATUS_TEMPLATE) {
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
        if (!auth()->user()->hasRole('admin') && $campaign->user_id !== auth()->id()) {
            return $this->responseError(message: 'You do not have permission to update this campaign.');
        }

        if ($campaign->campaignShortUrls()->count() > 0) {
            return $this->responseError(message: 'Campaign has short urls and cannot be deleted.');
        }

        if ($campaign->status === Campaign::STATUS_DRAFT || $campaign->status === Campaign::STATUS_TEMPLATE) {
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
        $campaign = Campaign::with(['recipient_list.recipientsGroup'])
            ->when(!auth()->user()->hasRole('admin'), function ($query) {
                return $query->where('user_id', auth()->id());
            })
            ->find($id);

        if (!$campaign) {
            return $this->responseError(message: 'Campaign not found.');
        }

        $count_of_contacts = intval($campaign->recipient_list?->recipientsGroup->count);

        if ($count_of_contacts == 0) {
            return $this->responseError(message: 'Recipient list is empty.');
        }

        $user = auth()->user();

        if (!$user->hasEnoughTokens($count_of_contacts)) {
            return $this->responseError(message: 'You do not have enough tokens to send this campaign.');
        }

        $user->deductTokens($count_of_contacts);

        [$result, $message] = $this->campaignService->markAsProcessed(intval($id));

        if ($result) {
            return $this->responseSuccess(message: $message);
        }

        return $this->responseError(message: $message);
    }
}
