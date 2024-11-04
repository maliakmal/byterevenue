<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\CampaignStoreRequest;
use App\Http\Requests\CampaignUpdateRequest;
use App\Models\RecipientsList;
use App\Repositories\Contract\Campaign\CampaignRepositoryInterface;
use App\Services\Campaign\CampaignService;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\Campaign;
use App\Models\Message;
use App\Models\User;
use App\Models\BroadcastLog;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class CampaignController extends ApiController
{
    private $campaignService;

    /**
     * @param CampaignService $campaignService
     */
    public function __construct(
        CampaignService $campaignService,
    ) {
        $this->campaignService = $campaignService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $filter = [
            'status' => request('status'),
            'user_id' => request('user_id'),
            'sortby' => request('sortby', 'id_desc'),
            'count' => request('count', 5),
        ];

        $campaigns = $this->campaignService->getCampaignsFiltered($filter);

        return view('campaigns.index', compact('campaigns', 'filter'));
    }

    /**
     * @return JsonResponse
     */
    public function indexApi(Request $request)
    {
        $filter = [
            'status' => $request->get('status'),
            'user_id' => $request->get('user_id'),
            'sortby' => $request->get('sortby', 'id_desc'),
            'count' => $request->get('count', 5),
        ];
        $campaigns = $this->campaignService->getCampaignsFiltered($filter);

        return $this->responseSuccess($campaigns);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $recipient_lists = auth()->user()->recipientLists()->get();
        return view('campaigns.create', compact('recipient_lists'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CampaignStoreRequest $request)
    {
        [$campaign, $errors] = $this->campaignService->store($request->validated());

        if (isset($errors['message'])) {
            redirect()->route('campaigns.index')->with('error', $errors['message']);
        }

        return redirect()->route('campaigns.show', $campaign)->with('success', 'Campaign created successfully.');
    }

    /**
     * @param CampaignStoreRequest $request
     *
     * @return JsonResponse
     */
    public function storeApi(CampaignStoreRequest $request)
    {
        [$campaign, $errors] = $this->campaignService->store($request->validated());

        if (isset($errors['message'])) {
            return $this->responseError([], $errors);
        }

        return $this->responseSuccess($campaign, 'Campaign created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Campaign $campaign)
    {
        $campaignData = $this->campaignService->show($campaign->id);

        if (request()->input('output') == 'json') {
            return response()->success(null, [
                'contacts' => $campaignData['contacts'],
                'logs' => $campaignData['logs'],
            ]);
        }

        return view('campaigns.show')->with($campaignData);
    }

    /**
     * @param int $id
     *
     * @return JsonResponse
     */
    public function showApi(int $id)
    {
        $campaignData = $this->campaignService->show($id);

        return $this->responseSuccess($campaignData);
    }

    /**
     * @param int $id
     *
     * @return RedirectResponse
     */
    public function markAsProcessed(int $id)
    {
        [$result, $message] = $this->campaignService->markAsProcessed($id);

        if ($result) {
            return redirect()->back()->with('success', $message);
        }

        return redirect()->back()->withErrors(['error' => $message]);
    }

    /**
     * @param int $id
     *
     * @return JsonResponse
     */
    public function markAsProcessedApi(int $id)
    {
        [$result, $message] = $this->campaignService->markAsProcessed($id);

        if ($result) {
            return $this->responseSuccess([], $message);
        }

        return $this->responseError([], $message);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Campaign $campaign)
    {
        $recipient_lists = RecipientsList::where('user_id', $campaign->user_id)->get();
        return view('campaigns.edit', compact('campaign', 'recipient_lists'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CampaignUpdateRequest $request, Campaign $campaign)
    {
        $campaign = $this->campaignService->update($campaign->id, $request->validated());
        return redirect()->route('campaigns.show', $campaign)->with('success', 'Campaign updated successfully.');
    }

    /**
     * @param int $id
     * @param CampaignUpdateRequest $request
     *
     * @return JsonResponse
     */
    public function updateApi(int $id, CampaignUpdateRequest $request)
    {
        $campaign = $this->campaignService->update($id, $request->validated());

        return $this->responseSuccess($campaign, 'Campaign updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Campaign $campaign)
    {
        $campaign->delete();

        return redirect()->route('campaigns.index')->with('success', 'Campaign deleted successfully.');
    }

    /**
     * @param int $id
     *
     * @return JsonResponse
     */
    public function destroyApi(int $id)
    {
        Campaign::find($id)->delete();

        return $this->responseSuccess([], 'Campaign deleted successfully.');
    }

    /**
     * @param Request $request
     *
     * @return mixed
     */
    public function getCampaignForUser(Request $request)
    {
        $request->validate(['user_id' => 'required|numeric']);
        $user_id = $request->user_id;
        $campaigns = $this->campaignService->getCampaignsForUser($user_id);

        return response()->success(null, $campaigns);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getCampaignForUserApi(Request $request)
    {
        $request->validate(['user_id' => 'required|numeric']);
        $user_id = $request->user_id;
        $campaigns = $this->campaignService->getCampaignsForUser($user_id);

        return $this->responseSuccess($campaigns);
    }
}
